<?php 

include_once 'MangaNetwork/manga.php';
include_once 'MangaNetwork/utils.php';

include_once 'MangaNetwork/action/manga/chapter/utils.php';

function GetUserMangaChapterID($context) {
	 
	// Get manga
	$manga = getMangaFromDatabaseByID($context->params['id'], $context->user, true);
	// Get chapter
	$chapter = getMangaChapter($manga, $context->params['chapter_id']);

	if(!$chapter) {
		
	}

	// Get chapter
	return $chapter;
}

?>