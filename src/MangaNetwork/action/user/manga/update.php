<?php 

include_once 'MangaNetwork/manga.php';
include_once 'MangaNetwork/utils.php';

function UpdateManga($context) {

	$validator = new MnValidator();

	if(isset($context->params["request_content"]["favoris"]))
		$validator->addRule("favoris", MnValidatorRule::optionalBoolean());

	if(isset($context->params["request_content"]["page"]))
		$validator->addRule("page", MnValidatorRule::optionalNumber(0));

	if(isset($context->params["request_content"]["chapter"]))
		$validator->addRule("chapter", MnValidatorRule::optionalNumber(0));

	if(isset($context->params["request_content"]["note"]))
		$validator->addRule("note", MnValidatorRule::optionalNumber(0, 5));

	$validator->validate($context->params["request_content"]);
	$user_update = $validator->getValidatedValues();
	
	$db = GetDBConnection();

	// Get the manga
	$ret = false;
	$manga = getUserMangaFromDatabaseById($context->params["id"], $context->user, true);

	if(isset($user_update['favoris'])) {
		$query = $db->prepare("UPDATE user_has_manga
		                       SET favoris = ?
		                       WHERE user_id = ? AND manga_id = ?");
		$response = $query->execute([$user_update['favoris'] ? 1 : 0, $context->user->id, $manga->id]);

		if(!$response)
			throw new MnException("Error : sql error update favoris : ".$manga->id, 500);
		
		$ret = true;
	}
	
	if(isset($user_update['page'])) {
		$query = $db->prepare("UPDATE user_has_manga
			                   SET page_cur = ?
			                   WHERE user_id = ? AND manga_id = ?");
		$response = $query->execute([$user_update['page'], $context->user->id, $manga->id]);

		if(!$response)
			throw new MnException("Error : sql error update page_cur : ".$idManga, 500);
	
		$ret = true;
	}
	
	if(isset($user_update['chapter'])) {
		$query = $db->prepare("UPDATE user_has_manga
			                   SET chapter_cur = ?
			                   WHERE user_id = ? AND manga_id = ?");
		$response = $query->execute([$user_update['chapter'], $context->user->id, $manga->id]);

		if(!$response)
			throw new MnException("Error : sql error update chapter_cur : ".$idManga, 500);
	
		$ret = true;
	}
	
	if(isset($user_update['note'])) {
		$query = $db->prepare("UPDATE user_has_manga
							   SET note = ?
							   WHERE user_id = ? AND manga_id = ?");
		$response = $query->execute([$user_update['note'], $context->user->id, $manga->id]);

		if(!$response)
			throw new MnException("Error : sql error update note: ".$idManga, 500);
	
		$ret = true;
	}

	return $ret;
}

?>