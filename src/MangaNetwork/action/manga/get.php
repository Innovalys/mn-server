<?php

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/validator.php';
include_once 'MangaNetwork/utils.php';

include_once 'MangaNetwork/action/manga/utils.php';

/**
 * Add a manga to the connected user
 * @param \MnContext $context The request context
 */
function GetMangaAPI($context) {

	$manga_info = ['source' => $context->params['source'], 'id' => $context->params['id']];

	// Get manga
	return getManga($manga_info);
}

?>