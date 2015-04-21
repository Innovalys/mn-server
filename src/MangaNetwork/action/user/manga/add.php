<?php

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/validator.php';
include_once 'MangaNetwork/utils.php';

include_once 'MangaNetwork/action/manga/utils.php';

/**
 * Add a manga to the connected user
 * @param \MnContext $context The request context
 */
function AddMangaToUser($context) {

	$validator = new MnValidator();
	$validator->addRule("api",    MnValidatorRule::requiredString());
	$validator->addRule("source", MnValidatorRule::requiredString());
	$validator->addRule("id",     MnValidatorRule::requiredString());
	$validator->validate($context->params["request_content"]);
	$manga_info = $validator->getValidatedValues();

	// Get manga
	$manga = getManga($manga_info);

	// Add manga
	$db = GetDBConnection();
	$query = $db->prepare("SELECT * FROM user_has_manga WHERE user_id = ? AND manga_id = ?");
	$query->execute([$context->user->id, $manga->id]);
	$data = $query->fetch(PDO::FETCH_ASSOC);

	if($data)
		throw new MnException("Error : user '" . $context->user->login . "' already have the manga '" . $manga->title . "' in its personnal library", 400);
	
	$query = $db->prepare("INSERT INTO user_has_manga (manga_id, user_id, update_date) VALUES (?, ?, ?)");
	$query->execute([$manga->id, $context->user->id, (new DateTime())->format('Y-m-d H:i:s')]);

	return $manga;
}

?>