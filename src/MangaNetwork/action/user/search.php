<?php 

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/validator.php';
include_once 'MangaNetwork/utils.php';

function SearchUser($context) {

	$data = "%" . $context->params["login"] . "%";

	$db = GetDBConnection();
	
	$query = $db->prepare("SELECT id, login, mail, name, credentials
							FROM user
							WHERE login LIKE ?");

	$query->execute([$data]);
	$response = $query->fetchAll(PDO::FETCH_ASSOC);

	if(!$response)
		return [];

	$users = [];

	foreach ($response as $user) {
		// Users followed
		$query = $db->prepare("SELECT id, login, mail, name, credentials
								FROM user
								INNER JOIN user_has_user
								ON user.id = user_has_user.user_id_followed AND user_has_user.user_id_following = ?");

		$query->execute([$user['id']]);

		$user["followed"] = [];
		while($followed = $query->fetch(PDO::FETCH_ASSOC)) {
			$user["followed"][] = $followed;
		}

		// Users following
		$query = $db->prepare("SELECT id, login, mail, name, credentials
								FROM user
								INNER JOIN user_has_user
								ON user.id = user_has_user.user_id_following AND user_has_user.user_id_followed = ?");

		$query->execute([$user['id']]);

		$user["following"] = [];
		while($following = $query->fetch(PDO::FETCH_ASSOC)) {
			$user["following"][] = $following;
		}

		$users[] = $user;
	}

	return $users;

}

?>
