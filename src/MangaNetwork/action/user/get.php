<?php 

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/utils.php';

function GetUser($context) {

	$data = $context->params["id"];

	$db = GetDBConnection();
	
	$query = $db->prepare("SELECT id, login, mail, name, credentials
							FROM user
							WHERE id = ?");

	$query->execute([$data]);
	$response = $query->fetch(PDO::FETCH_ASSOC);

	// Users followed
	$query = $db->prepare("SELECT id, login, mail, name, credentials
							FROM user
							INNER JOIN user_has_user
							ON user.id = user_has_user.user_id_followed AND user_has_user.user_id_following = ?");

	$query->execute([$data]);

	$response["followed"] = [];
	while($followed = $query->fetch(PDO::FETCH_ASSOC)) {
		$response["followed"][] = $followed;
	}

	// Users following
	$query = $db->prepare("SELECT id, login, mail, name, credentials
							FROM user
							INNER JOIN user_has_user
							ON user.id = user_has_user.user_id_following AND user_has_user.user_id_followed = ?");

	$query->execute([$data]);

	$response["following"] = [];
	while($following = $query->fetch(PDO::FETCH_ASSOC)) {
		$response["following"][] = $following;
	}

	return $response;
}

function GetConnectedUser($context) {
	return $context->user;
}
?>
