<?php 

include_once 'MangaNetwork/manga.php';
include_once 'MangaNetwork/utils.php';

include_once 'MangaNetwork/action/manga/chapter/utils.php';

function GetUserMangaChapterID($context) {
	 
	// Get manga
	$manga = getMangaFromDatabaseByID($context->params['id'], true);

	// TODO : check if mange is un user collection

	// Get chapter
	return getMangaChapter($manga, $context->params['chapter_id']);
}

?>