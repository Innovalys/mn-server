<?php

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/validator.php';
include_once 'MangaNetwork/utils.php';

include_once 'MangaNetwork/action/manga/utils.php';

/**
 * Add a manga to the connected user
 * @param \MnContext $context The request context
 */
function GetMangaID($context) {
	// Get manga
	return getMangaFromDatabaseByID($context->params['id'], $context->user, true);
}

?>