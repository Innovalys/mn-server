<?php 

include_once 'MangaNetwork/manga.php';
include_once 'MangaNetwork/utils.php';
include_once 'MangaNetwork/action/manga/utils.php';

function GetUserMangaFromDb($context) {
	return getUserMangaFromDatabaseById($context->params["id"], $context->user, true);
}

?>