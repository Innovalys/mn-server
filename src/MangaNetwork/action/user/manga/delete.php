<?php 

include_once 'MangaNetwork/manga.php';
include_once 'MangaNetwork/utils.php';

function DeleteManga($context) {

	$idManga = $context->params['id'];
	$user = $context->user;
		
	if(ExistManga($user,$idManga)) {
		$db = GetDBConnection();
		
		$query = $db->prepare("DELETE FROM user_has_manga
							   WHERE user_id = ? AND manga_id= ?");

		$response = $query->execute([$user->id, $idManga]);

		if(!$response) {
			throw new MnException("Error : unable to remove manga with ID : " . $idManga . " from user '" . $user->login . "' collection", 400);
		}
	} else {
		throw new MnException("Error : no manga with ID : " . $idManga . " for user '" . $user->login . "'", 404);
	}

	return("Manga with ID : " . $idManga . " deleted from user '" . $user->login . "' collection");
}

function ExistManga($user, $idManga) {
	$db = GetDBConnection();
	$query = $db->prepare("SELECT *
						   FROM user_has_manga
						   WHERE user_id = ? AND manga_id= ?");

	$response = $query->execute([$user->id, $idManga]);
	$response = $query->fetch(PDO::FETCH_ASSOC);

	if($response == NULL)
		return false;

	return true;
}

?>