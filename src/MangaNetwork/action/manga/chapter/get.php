<?php 

include_once 'MangaNetwork/manga.php';
include_once 'MangaNetwork/utils.php';

function GetMangaChapterAPI($context) {

	$manga_info = ['source' => $context->params['source'], 'id' => $context->params['id']];

	// Get manga
	$manga = getManga($manga_info);

	// Get chapter
	return getMangaChapter($manga, $context->params['chapter_id']);
}

?>