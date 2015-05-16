<?php 

include_once 'MangaNetwork/manga.php';
include_once 'MangaNetwork/utils.php';

function UpdateManga($context) {

	$validator = new MnValidator();
	$validator->addRule("favoris", MnValidatorRule::optinalBoolean());
	$validator->addRule("page", MnValidatorRule::optinalNumber(0));
	$validator->addRule("note", MnValidatorRule::optinalNumber(0, 5));
	$validator->validate($context->params);
	$user_update = $validator->getValidatedValues();
	
	$db = GetDBConnection();

		
	if($favoris != null) {
		if($favoris==0 || $favoris==1) {
			$query = $db->prepare("UPDATE user_has_manga
			                       SET favoris = ?
			                       WHERE user_id = ? AND manga_id = ?");
			$response = $query->execute([$favoris, $idUser, $idManga]);
			$response = $query->fetch(PDO::FETCH_ASSOC);

			if($response == NULL) {
				throw new MnException("Error : sql error update favoris : ".$idManga, 404);
			}

			return $response;
		}
	}
	
	if($page != null) {
		$query = $db->prepare("UPDATE user_has_manga
			                   SET page_cur = ?
			                   WHERE user_id = ? AND manga_id = ?");
		$response = $query->execute([$page, $idUser, $idManga]);
		$response = $query->fetch(PDO::FETCH_ASSOC);

		if($response == NULL) {
			throw new MnException("Error : sql error update page_cur : ".$idManga, 404);
		}

		return $response;
	}
	
	if($note!=null) {
		$query = $db->prepare("UPDATE user_has_manga
							   SET note = ?
							   WHERE user_id = ? AND manga_id = ?");
		$response = $query->execute([$note, $idUser, $idManga]);
		$response = $query->fetch(PDO::FETCH_ASSOC);

		if($response == NULL) {
			throw new MnException("Error : sql error update note: ".$idManga, 404);
		}

		return $response;
	}
	
}

?>