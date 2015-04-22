<?php

include_once 'MangaNetwork/exception.php';
include_once 'MangaNetwork/manga.php';

/**
 * Get a manga. If the manga is not in the database, the function will try to get the manga
 * from the provided API/source couples
 * @param  string $manga_info     The manga informations from the API
 * @return \MnManga       The retreived manga
 */
function getManga($manga_info) {

	$manga_info['api'] = guessAPIFromSource($manga_info['source']);

	// Get the manga from the database
	$manga = getMangaFromDatabase($manga_info['api'], $manga_info['source'], $manga_info['id']);

	if(!$manga) {
		switch ($manga_info['api']) {
			case 'mangascrapper':
				return getMangaFromMangaScrapper($manga_info);

			case 'mangaeden':
				return getMangaFromMangaEden($manga_info);
			
			default:
				// Should not happen
				throw new MnException("Error : unknow API '" . $manga_info['api'] . "", 404);
		}
	} else
		return $manga;
}

/**
 * Guess the API to use from the given source. This method can also be used as a failguard
 * because it will thrown an exception if the source link don't refer to any known
 * source
 * @param  string $source The source
 * @return string         The found API
 */
function guessAPIFromSource($source) {
	switch (strtolower($source)) {
		case 'mangafox.me' :
		case 'mangareader.net' :
			return "mangascrapper";
		case 'www.mangaeden.com' :
		case 'mangaeden.com' :
			return 'mangaeden';
		default :
			throw new MnException("Error : unknow source '" . $source . "'", 404);
	}
}

/**
 * Get a manga from tha database. If the manga is not in the database, false will be returned. Otherwise,
 * the manga will be return
 * @param  string $api    The API to use
 * @param  string $source The API source to use
 * @param  string $id     The manga ID from the API
 * @return \MnManga             The found manga, or false
 */
function getMangaFromDatabase($api, $source, $id, $throw_on_null=false) {

	$db = GetDBConnection();

	// Get manga
	$query = $db->prepare("SELECT * FROM manga 
		                   WHERE source_API = :api AND source_URL = :source AND source_ID = :id");
	$query->execute(['api' => $api, 'source' => $source, 'id' => $id]);

	$data = $query->fetch(PDO::FETCH_ASSOC);

	if(!$data) {
		if($throw_on_null)
			throw new MnException("Error : no manga in the database with the source_ID '" + $id + "' for API '" + $api + "'", 404);
		else
			return false;
	}

	return MnManga::initFrom(setMangaRelativeInfo($db, $data));
}

/**
 * Get a manga from tha database. If the manga is not in the database, false will be returned. Otherwise,
 * the manga will be return
 * @param  string   $id   The manga unical ID
 * @return \MnManga       The found manga, or false
 */
function getMangaFromDatabaseByID($id, $throw_on_null=false) {

	$db = GetDBConnection();

	// Get manga
	$query = $db->prepare("SELECT * FROM manga WHERE id = :id");
	$query->execute(['id' => $id]);

	$data = $query->fetch(PDO::FETCH_ASSOC);

	if(!$data) {
		if($throw_on_null)
			throw new MnException("Error : no manga in the database with the ID '" + $id + "'", 404);
		else
			return false;
	}

	return MnManga::initFrom(setMangaRelativeInfo($db, $data));
}

/**
 * Set the manga relative info from the database
 */
function setMangaRelativeInfo($db, $data) {

	// Get genre
	$query = $db->prepare("SELECT genre.name FROM genre JOIN genre_has_manga
		                   WHERE genre_has_manga.manga_id = ? AND genre_has_manga.genre_id = genre.id");
	$query->execute([$data['id']]);
	$data['genres'] = $query->fetchAll(PDO::FETCH_COLUMN, 0);

	// Get authors
	$query = $db->prepare("SELECT author.name FROM author JOIN author_has_manga
		                   WHERE author_has_manga.manga_id = ? AND author_has_manga.author_id = author.id");
	$query->execute([$data['id']]);
	$data['authors'] = $query->fetchAll(PDO::FETCH_COLUMN, 0);

	// Get chapters
	$query = $db->prepare("SELECT manga_chapter.id, manga_chapter.title FROM manga_chapter
		                   WHERE manga_chapter.manga_id = ?");
	$query->execute([$data['id']]);
	$data['chapters'] = $query->fetchAll(PDO::FETCH_COLUMN, 0);

	return $data;
}


/**
 * Get a manga from MangaScrapper. Work with both sources
 * @param  mixed[] $manga_info The manga to download
 * @return \MnManga             The loaded manga
 */
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

	try {
		// Test for error
		if(curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200)
			throw new MnException("Error : error with '" . $manga_info['source'] . "' : " . curl_getinfo($curl, CURLINFO_HTTP_CODE), 400);

		// Test for error
		if(isset($rawResponse['error']))
			throw new MnException("Error : error with '" . $manga_info['source'] . "' : " . $rawResponse['error'], 400);

	} catch (Exception $e) {
		throw $e;
	} finally {
		curl_close($curl);
	}

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
	$validator->addRule("genres",        MnValidatorRule::optionalArray());
	$validator->validate($rawResponse);
	$manga_data = $validator->getValidatedValues();

	if(!$manga_data['name'])
		throw new MnException("Error : no manga could be retrieved with ID '" . $manga_info['id'] . "' on source '" . $manga_info['source'] . "'", 404);
	
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
	$manga['update_date']  = $manga['update_date']->format('Y-m-d H:i:s');

	$manga_data['authors'] = array_unique(array_merge($manga_data['author'], $manga_data['artist']));

	return createManga($manga, $manga_data);
}


/**
 * Get a manga from MangaEden
 * @param  mixed[] $manga_info The manga to download
 * @return \MnManga             The loaded manga
 */
function getMangaFromMangaEden($manga_info) {

	// Get manga data from MangaScrapper
	$curl = curl_init();
	curl_setopt_array($curl, [
	    CURLOPT_RETURNTRANSFER => 1,
	    CURLOPT_SSL_VERIFYPEER => false,
	    CURLOPT_HTTPHEADER => [
	    	'X-Mashape-Key: ' . GetMashapeKey(),
	    	'Accept: text/plain'
	    ],
	    CURLOPT_URL => 'http://www.mangaeden.com/api/manga/' .  $manga_info['id'] . '/'
	]);
	$rawResponse = curl_exec($curl);

	try {
		// Test for error
		if(curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200)
			throw new MnException("Error : error with '" . $manga_info['source'] . "' : " . curl_getinfo($curl, CURLINFO_HTTP_CODE), 400);

		// Test for error
		if(isset($rawResponse['error']))
			throw new MnException("Error : error with '" . $manga_info['source'] . "' : " . $rawResponse['error'], 400);

	} catch (Exception $e) {
		throw $e;
	} finally {
		curl_close($curl);
	}

	$rawResponse = json_decode($rawResponse, true);

	// Validate data
	$validator = new MnValidator();
	$validator->addRule("title",         MnValidatorRule::optionalString());
	$validator->addRule("created",       MnValidatorRule::optionalNumber());
	$validator->addRule("status",        MnValidatorRule::optionalString());
	$validator->addRule("description",   MnValidatorRule::optionalString());
	$validator->addRule("image",         MnValidatorRule::optionalString());
	$validator->addRule("chapters",      MnValidatorRule::optionalArray());
	$validator->addRule("author_kw",     MnValidatorRule::optionalArray());
	$validator->addRule("artist_kw",     MnValidatorRule::optionalArray());
	$validator->addRule("categories",    MnValidatorRule::optionalArray());
	$validator->validate($rawResponse);
	$manga_data = $validator->getValidatedValues();

	if(!$manga_data['title'])
		throw new MnException("Error : no manga could be retrieved with ID '" . $manga_info['id'] . "' on source '" . $manga_info['source'] . "'", 404);
	
	// Create the manga
	$manga = [ 'title'        => $manga_data['title'],                                   // Title of the manga
	           'chapter_nb'   => sizeof($manga_data['chapters']),                        // Number of chapters available
	           'source_API'   => "MangaEden",                                            // Name of the API
	           'source_URL'   => "www.mangaeden.com",                                    // Source URL
	           'source_ID'    => $manga_info['id'],                                      // Source ID
	           'release_date' => (new DateTime())->setTimestamp($manga_data['created']), // Date of release, but MangaScrappe only provides the year
	           'update_date'  => new DateTime(),                                         // Last edit : now
	           'completed'    => $manga_data['status'] == 1,                             // True if completed, false otherwise
	           'description'  => $manga_data['description'],                             // The manga description
	           'cover'        => $manga_data['image']                                    // URL to the manga's cover
	         ];

	$manga['release_date'] = $manga['release_date']->format('Y-m-d H:i:s');
	$manga['update_date']  = $manga['update_date']->format('Y-m-d H:i:s');

	$manga_data['authors'] = array_unique(array_merge($manga_data['artist_kw'], $manga_data['author_kw']));
	$manga_data['genres']  = $manga_data["categories"];

	for ($i=0; $i < sizeof($manga_data['chapters']); $i++) { 
		$manga_data['chapters'][$i]["name"] = $manga_data['chapters'][$i][2];
		$manga_data['chapters'][$i]["chapterId"] = $manga_data['chapters'][$i][3];
	}

	return createManga($manga, $manga_data);
}


/**
 * Create a manga in the database using the provided values
 * @param  mixed[] $manga      The manga informations (id, name, etc..)
 * @param  mixed[] $manga_data The manga relative informations (author, chapters, ...)
 * @return MnManga             The created manga
 */
function createManga($manga, $manga_data) {

	// Add the manga
	$db = GetDBConnection();
	$query = $db->prepare("INSERT INTO manga (title,   chapter_nb,  source_API,  source_URL,  source_ID,  update_date,  release_date,  completed,  description, cover)
		                              VALUES (:title, :chapter_nb, :source_API, :source_URL, :source_ID, :update_date, :release_date, :completed, :description, :cover)");
	$query->execute($manga);

	$manga['id'] = $db->lastInsertId();
	$manga['authors'] = $manga_data['authors'];
	$manga['genres'] = $manga_data['genres'];
	$manga['chapters'] = $manga_data['chapters'];
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
	foreach ($manga->genres as $genre) {

		// Check if the genre is in the database
		$query = GetDBConnection()->prepare("SELECT * FROM genre WHERE name = ?");
		$query->execute([$genre]);

		$data = $query->fetch(PDO::FETCH_ASSOC);
		$id = 0;

		if(!$data) {
			// Genre needs to be added
			$query = $db->prepare("INSERT INTO genre (name) VALUES (?)");
			$query->execute([$genre]);

    		$id = $db->lastInsertId();
		} else {
			$id = $data['id']; // Get ID from database
		}

		// Add this manga to the genre's mangas
		$query = $db->prepare("INSERT INTO genre_has_manga (genre_id, manga_id) VALUES (?, ?)");
		$query->execute([$id, $manga->id]);
	}

	// Add all the chapters to the database
	$i = 1;
	foreach ($manga_data['chapters'] as $chapter) {
		$query = $db->prepare("INSERT INTO manga_chapter (manga_id, source_ID, title) VALUES (?, ?, ?)");
		$query->execute([$manga->id, $chapter['chapterId'], (isset($chapter['name']) ? $chapter['name'] : "Chapter " . $i)]);
		$i++;
	}

	return $manga;
}

?>