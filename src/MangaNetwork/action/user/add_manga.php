<?php

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/validator.php';
include_once 'MangaNetwork/utils.php';

function AddMangaToUser($context) {

	$validator = new MnValidator();
	$validator->addRule("api",    MnValidatorRule::requiredString());
	$validator->addRule("source", MnValidatorRule::requiredString());
	$validator->addRule("id",     MnValidatorRule::requiredString());
	$validator->validate($context->params["request_content"]);

	$manga_info = $validator->getValidatedValues();
	$manga = getMangaFromDatabase($manga_info);

	if(!$manga) {
		switch (strtolower($manga_info["api"])) {
			case 'mangascrapper':
				if(strtolower($manga_info["source"]) == "mangafox.me" OR
				   strtolower($manga_info["source"]) == "mangareader.net") {
					$manga = getMangaFromMangaScrapper($manga_info);
				} else {
					throw new MnException("Error : unknow source '" . $manga_info["source"] . "' to use", 400);
				}
				break;

			case 'mangaeden':
				if(strtolower($manga_info["source"]) == "www.mangaeden.com") {
					$manga = getMangaFromMangaScrapper($manga_info);
				} else {
					throw new MnException("Error : unknow source '" . $manga_info["source"] . "' to use", 400);
				}
				break;
			
			default:
				throw new MnException("Error : unknow API '" . $manga_info["api"] . "' to use", 400);
		}
	}
	
	return $manga;
}

function getMangaFromDatabase($manga_info) {

	// Get manga
	$query = GetDBConnection()->prepare("SELECT * FROM manga 
		                                 WHERE source_API = :api AND source_URL = :source AND source_ID = :id");
	$query->execute($manga_info);

	$data = $query->fetch(PDO::FETCH_ASSOC);
	if(!$data)
		return false;

	// Get genre
	// TODO

	// Get authors
	$query = GetDBConnection()->prepare("SELECT * FROM author JOIN author_has_manga
		                                 WHERE author_has_manga.manga_id = ? AND author_has_manga.author_id = author.id");
	$query->execute([$data['id']]);
	$data['authors'] = $query->fetch(PDO::FETCH_ASSOC);

	return MnManga::initFrom($data);
}

function getMangaFromMangaScrapper($manga_info) {

	// Get manga data from MangaScrapper
	$curl = curl_init();
	curl_setopt_array($curl, [
	    CURLOPT_RETURNTRANSFER => 1,
	    CURLOPT_SSL_VERIFYPEER => false,
	    CURLOPT_HTTPHEADER => [
	    	'X-Mashape-Key: ' . GetMashapeKey(),
	    	'Accept: text/plain'
	    ],
	    CURLOPT_URL => 'https://doodle-manga-scraper.p.mashape.com/' . $manga_info['source'] . '/manga/' .  $manga_info['id'] . '/'
	]);
	$rawResponse = json_decode(curl_exec($curl), true);
	curl_close($curl);

	// Test for error
	if(isset($rawResponse['error']))
		throw new MnException("Error : error with MangaScrapper API '" . $rawResponse['error'] . "'", 400);

	// Validate data
	$validator = new MnValidator();
	$validator->addRule("name",          MnValidatorRule::optionalString());
	$validator->addRule("yearOfRelease", MnValidatorRule::optionalNumber());
	$validator->addRule("status",        MnValidatorRule::optionalString());
	$validator->addRule("info",          MnValidatorRule::optionalString());
	$validator->addRule("cover",         MnValidatorRule::optionalString());
	$validator->addRule("chapters",      MnValidatorRule::optionalArray());
	$validator->addRule("author",        MnValidatorRule::optionalArray());
	$validator->addRule("artist",        MnValidatorRule::optionalArray());
	$validator->validate($rawResponse);
	$manga_data = $validator->getValidatedValues();

	if(!$manga_data['name'])
		throw new MnException("Error : no manga could be retrieved with ID '" . $manga_info['id'] . "' on '" . $manga_info['source'] . "'", 404);
	
	// Create the manga
	$manga = [ 'title'        => $manga_data['name'],                                           // Title of the manga
	           'chapter_nb'   => sizeof($manga_data['chapters']),                               // Number of chapters available
	           'source_API'   => "MangaScrapper",                                               // Name of the API
	           'source_URL'   => $manga_info['source'],                                         // Source URL
	           'source_ID'    => $manga_info['id'],                                             // Source ID
	           'release_date' => (new DateTime())->setDate($manga_data['yearOfRelease'], 0, 0), // Date of release, but MangaScrappe only provides the year
	           'update_date'  => new DateTime(),                                                // Last edit : now
	           'completed'    => ($manga_data['status'] == 'Complete'),                         // True if completed, false otherwise
	           'description'  => $manga_data['info'],                                           // The manga description
	           'cover'        => $manga_data['cover']                                           // URL to the manga's cover
	         ];

	$manga['release_date'] = $manga['release_date']->format('Y-m-d H:i:s');
	$manga['update_date'] = $manga['update_date']->format('Y-m-d H:i:s');

	$db = GetDBConnection();
	$query = $db->prepare("INSERT INTO manga (title,   chapter_nb,  source_API,  source_URL,  source_ID,  update_date,  release_date,  completed,  description, cover)
		                              VALUES (:title, :chapter_nb, :source_API, :source_URL, :source_ID, :update_date, :release_date, :completed, :description, :cover)");
	$query->execute($manga);

	$manga['id'] = $db->lastInsertId();
	$manga['authors'] = array_unique(array_merge($manga_data['author'], $manga_data['artist']));
	$manga = MnManga::initFrom($manga);

	// For each author/artist, check if they appear in the database, if not add them
	foreach ($manga->authors as $artist) {

		// Check if the author is in the database
		$query = GetDBConnection()->prepare("SELECT * FROM author WHERE name = ?");
		$query->execute([$artist]);

		$data = $query->fetch(PDO::FETCH_ASSOC);
		$id = 0;

		if(!$data) {
			// Author needs to be added
			$query = $db->prepare("INSERT INTO author (name) VALUES (?)");
			$query->execute([$artist]);

    		$id = $db->lastInsertId();
		} else {
			$id = $data['id']; // Get ID from database
		}

		// Add this manga to the author's mangas
		$query = $db->prepare("INSERT INTO author_has_manga (author_id, manga_id) VALUES (?, ?)");
		$query->execute([$id, $manga->id]);
	}

	// Add all the genre to the manga
	// TODO

	// Add all the chapters to the database
	$i = 1;
	foreach ($manga_data['chapters'] as $chapter) {
		$query = $db->prepare("INSERT INTO manga_chapter (manga_id, source_ID, title) VALUES (?, ?, ?)");
		$query->execute([$manga->id, $chapter['chapterId'], (isset($chapter['name']) ? $chapter['name'] : "Chapter " . $i)]);
		$i++;
	}

	return $manga;
}

function getMangaFromMangaEden($context) {
	
}


?>