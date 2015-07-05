<?php 

include_once 'MangaNetwork/manga.php';
include_once 'MangaNetwork/utils.php';

include_once 'MangaNetwork/action/manga/chapter/utils.php';

function GetUserMangaChapterAPI($context) {

	$manga_info = ['source' => $context->params['source'], 'id' => $context->params['id']];

	// Get manga
	$manga = getManga($manga_info);

	// TODO : check if manga is un user collection

	// Get chapter
	return getMangaChapter($manga, $context->params['chapter_id']);
}

?>