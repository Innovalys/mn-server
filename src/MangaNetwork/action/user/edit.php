<?php 

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/validator.php';
include_once 'MangaNetwork/utils.php';

function UpdateUser($context) {

	$validator = new MnValidator();

	if(isset($context->params["request_content"]["follow"]))
		$validator->addRule("follow", MnValidatorRule::optionalNumber());

	if(isset($context->params["request_content"]["unfollow"]))
		$validator->addRule("unfollow", MnValidatorRule::optionalNumber());

	$validator->validate($context->params["request_content"]);
	$user_update = $validator->getValidatedValues();
	
	$db = GetDBConnection();

	// Get the manga
	$ret = false;

	if(isset($user_update['unfollow'])) {
		$query = $db->prepare("DELETE FROM user_has_user
		                       WHERE user_id_following = ? AND user_id_followed = ?");
		$response = $query->execute([$user_update['unfollow'], $context->user->id]);

		if(!$response)
			throw new MnException("Error : sql error update follower", 500);
		
		$ret = true;
	}

	if(isset($user_update['follow'])) {
		$query = $db->prepare("INSERT INTO user_has_user VALUES (?, ?)");
		$response = $query->execute([$user_update['follow'], $context->user->id]);

		if(!$response)
			throw new MnException("Error : sql error update follower", 500);
		
		$ret = true;
	}

	return $ret;
}

?>