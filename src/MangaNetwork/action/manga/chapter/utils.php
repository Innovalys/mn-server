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
function getMangaChapterFromDatabase($manga, $chapter_id) {
	$db = GetDBConnection();

	// Get id
	if($chapter_id > sizeof($manga->getChapters()))
		throw new MnException("Error : no chapter #".$chapter_id." in manga '" . $manga->title . "'", 404);
	$chapter_id = $manga->getChapters()[$chapter_id];

	// Get chapters
	$query = $db->prepare("SELECT * FROM manga_chapter WHERE id = ?");
	$chapter = $query->execute([$chapter_id]);
	$chapter = $query->fetch(PDO::FETCH_ASSOC);

	if($chapter == NULL) {
		throw new MnException("Error : no chapter #".$chapter_id." in manga '" . $manga->title . "'", 404);
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

	//$chapter_data['lastUpdate'] = 

	if(!$chapter_data['name'])
		throw new MnException("Error : no manga chapter could be retrieved with ID '" . $chapter['source_ID'] . "'", 404);

	return updateChapter($chapter, $chapter_data);
}

/**
 * Get a manga chapter pages from manga scrapper API
 * @param  MnManga $manga   The manga
 * @param  MnMangaChapter $chapter The manga chapter
 * @return MnMangaChapter          The completed manga chapter
 */
function getMangaChapterFromMangaEden($manga, $chapter) {
	// TODO
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

	$query = $db->prepare("UPDATE manga_chapter SET manga_chapter.loaded = 1 WHERE manga_chapter.id = ?");
	$query->execute([$chapter->id]);


	$chapter->pages = $pages;

	return $chapter;
}

?>