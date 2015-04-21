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

	$validator = new MnValidator();
	$validator->addRule("api",    MnValidatorRule::requiredString());
	$validator->addRule("source", MnValidatorRule::requiredString());
	$validator->addRule("id",     MnValidatorRule::requiredString());
	$validator->validate($context->params["request_content"]);
	$manga_info = $validator->getValidatedValues();

	// Get manga
	return getManga($manga_info);
}

?>