<?php

/**
 * Get a manga chapter. Note that only chapter of loaded manga can be retreived
 * @param  MnManga $manga      The manga
 * @param  string $chapter_id  The chapter ID
 * @return MnMangaChapter      The chapter found
 */
function getMangaChapter($manga, $chapter_id) {

	$chapter = getMangaChapterFromDatabase($manga, $chapter_id);

	if(!$chapter->isLoaded()) {
		// Need to cache data from the API
		switch (guessAPIFromSource($manga->source_URL)) {
			case 'mangascrapper':
				return getMangaChapterFromMangaScrapper($manga, $chapter);

			case 'mangaeden':
				return getMangaChapterFromMangaEden($manga, $chapter);
		}

	} else
		return $chapter;
}

/**
 * Get a manga chapter from the database. If the chapter is not loaded, no pages will be present
 * @param  string $chapter_id  The chapter id
 * @return MnChapter           The chapter
 */
function getMangaChapterFromDatabase($manga, $nb) {
	$db = GetDBConnection();

	// Get id
	if($nb > sizeof($manga->getChapters()) OR $nb < 1)
		throw new MnException("Error : no chapter #".$nb." in manga '" . $manga->title . "'", 404);
	$chapter_id = $manga->getChapters()[$nb - 1];

	// Get chapters
	$query = $db->prepare("SELECT * FROM manga_chapter WHERE id = ?");
	$chapter = $query->execute([$chapter_id['id']]);
	$chapter = $query->fetch(PDO::FETCH_ASSOC);

	if($chapter == NULL) {
		throw new MnException("Error : no chapter #".$nb." in manga '" . $manga->title . "'", 404);
	}

	$chapter = MnMangaChapter::initFrom($chapter);

	if($chapter->isLoaded() == 1) {
		$query = $db->prepare("SELECT * FROM manga_page WHERE manga_page.manga_chapter_id = ?");
		$pages = $query->execute([$chapter->id]);

		foreach ($query->fetchall() as $data) {
			$chapter->pages[] = MnMangaPage::initFrom($data);
		}

		return $chapter;
	} 

	return $chapter;
}

/**
 * Get a manga chapter pages from manga scrapper API
 * @param  MnManga $manga   The manga
 * @param  MnMangaChapter $chapter The manga chapter
 * @return MnMangaChapter          The completed manga chapter
 */
function getMangaChapterFromMangaScrapper($manga, $chapter) {
	
	// Get chapter data from MangaScrapper
	$curl = curl_init();
	curl_setopt_array($curl, [
	    CURLOPT_RETURNTRANSFER => 1,
	    CURLOPT_SSL_VERIFYPEER => false,
	    CURLOPT_HTTPHEADER => [
	    	'X-Mashape-Key: ' . GetMashapeKey(),
	    	'Accept: text/plain'
	    ],
	    CURLOPT_URL => 'https://doodle-manga-scraper.p.mashape.com/' . $manga->source_URL . '/manga/' .  $manga->source_ID . '/' . $chapter->source_ID
	]);
	var_dump(curl_exec($curl));
	var_dump(curl_getinfo($curl, CURLINFO_HTTP_CODE));
	$rawResponse = json_decode(curl_exec($curl), true);

	try {
		// Test for error
		if(curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200)
			throw new MnException("Error : error with '" . $manga->source_URL . "' : " . curl_getinfo($curl, CURLINFO_HTTP_CODE), 400);

		// Test for error
		if(isset($rawResponse['error']))
			throw new MnException("Error : error with '" . $manga->source_URL . "' : " . $rawResponse['error'], 400);

	} catch (Exception $e) {
		throw $e;
	} finally {
		curl_close($curl);
	}

	// Validate data
	$validator = new MnValidator();
	$validator->addRule("name",        MnValidatorRule::optionalString());
	$validator->addRule("lastUpdate",  MnValidatorRule::optionalString());
	$validator->addRule("pages",       MnValidatorRule::optionalArray());
	$validator->validate($rawResponse);
	$chapter_data = $validator->getValidatedValues();

	if(!empty($chapter_data['lastUpdate']))
		throw new MnException("Error : no manga chapter could be retrieved with ID '" . $chapter->source_ID . "'", 404);

	return updateChapter($chapter, $chapter_data);
}

/**
 * Get a manga chapter pages from manga scrapper API
 * @param  MnManga $manga   The manga
 * @param  MnMangaChapter $chapter The manga chapter
 * @return MnMangaChapter          The completed manga chapter
 */
function getMangaChapterFromMangaEden($manga, $chapter) {

	// Get chapter data from MangaScrapper
	$curl = curl_init();
	curl_setopt_array($curl, [
	    CURLOPT_RETURNTRANSFER => 1,
	    CURLOPT_SSL_VERIFYPEER => false,
	    CURLOPT_URL => 'https://www.mangaeden.com/api/chapter/' . $chapter->source_ID . '/'
	]);
	$rawResponse = json_decode(curl_exec($curl), true);

	try {
		// Test for error
		if(curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200)
			throw new MnException("Error : error with '" . $manga->source_URL . "' : " . curl_getinfo($curl, CURLINFO_HTTP_CODE), 400);

		// Test for error
		if(isset($rawResponse['error']))
			throw new MnException("Error : error with '" . $manga->source_URL . "' : " . $rawResponse['error'], 400);

	} catch (Exception $e) {
		throw $e;
	} finally {
		curl_close($curl);
	}

	// Validate data
	$validator = new MnValidator();
	$validator->addRule("images",       MnValidatorRule::optionalArray());
	$validator->validate($rawResponse);
	$chapter_data = $validator->getValidatedValues();

	$chapter_data['name'] = $chapter->title;
	$chapter_data['pages'] = [];

	foreach ($chapter_data['images'] as $url) {
		$chapter_data['pages'][] = ['url' => "https://cdn.mangaeden.com/mangasimg/" . $url[1]];
	}

	return updateChapter($chapter, $chapter_data);
}

/**
 * Update the chapter to insert the given pages
 * @param  MnMangaChapter $chapter   The chapter
 * @param  mixed[] $chapter_data     Data to use
 * @return MnMangaChapter            The updated manga
 */
function updateChapter($chapter, $chapter_data) {

	$db = GetDBConnection();
	$pages = [];
	$i = 1;

	foreach ($chapter_data['pages'] as $page) {

		$query = $db->prepare("INSERT INTO manga_page (link, manga_chapter_id, page_nb)
			                   VALUES (?, ?, ?)");
		$query->execute([$page['url'], $chapter->id, $i]);

		$pages[] = ["page_nb" => $i++, "link" => $page['url']];
	}

	$query = $db->prepare("UPDATE manga_chapter SET manga_chapter.loaded = 1, manga_chapter.page_nb = ? WHERE manga_chapter.id = ?");
	$query->execute([$i - 1, $chapter->id]);

	$chapter->page_nb = $i - 1;
	$chapter->pages = $pages;

	return $chapter;
}

?>