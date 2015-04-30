<?php 

include_once 'MangaNetwork/manga.php';
include_once 'MangaNetwork/utils.php';

function UpdateManga($context) {

		//Recupere ce que l'utilisateur veut changer (favoris ou non et/ou la page en cour et/ou la note) 
		//verifier si la note est bien entre 0 et 5
		$db = GetDBConnection();

		$result = $context->params["request_content"];
		$idManga = $context->params["id"];
		$idUser=$context->user->id;

		
		$favoris=$result['favoris'];
		$page=$result['pages'];
		$note=$result['note'];
		
		
		
		if($favoris!=null)
		{
			if($favoris==0 || $favoris==1)
			{
			$query = $db->prepare("UPDATE user_has_manga
			SET favoris = ?
			WHERE user_id=?
			and manga_id=?");
			$response = $query->execute([$favoris, $idUser, $idManga]);
			$response = $query->fetch(PDO::FETCH_ASSOC);

			if($response == NULL) {
			throw new MnException("Error : sql error update favoris : ".$idManga, 404);
			}

			return $response;
			}
		}
		
		if($page!=null)
		{
		$query = $db->prepare("UPDATE user_has_manga
			SET page_cur = ?
			WHERE user_id=?
			and manga_id=?");
			$response = $query->execute([$page, $idUser, $idManga]);
			$response = $query->fetch(PDO::FETCH_ASSOC);

			if($response == NULL) {
				throw new MnException("Error : sql error update page_cur : ".$idManga, 404);
			}

			return $response;
		}
		
		if($note!=null)
		{
			if($note>0&& $note<6)
			{
			$query = $db->prepare("UPDATE user_has_manga
			SET note = ?
			WHERE user_id=?
			and manga_id=?");
			}
			$response = $query->execute([$note, $idUser, $idManga]);
			$response = $query->fetch(PDO::FETCH_ASSOC);

			if($response == NULL) {
				throw new MnException("Error : sql error update note: ".$idManga, 404);
			}

			return $response;
		}
	
}

?>