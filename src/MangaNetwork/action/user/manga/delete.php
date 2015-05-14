<?php 

include_once 'MangaNetwork/manga.php';
include_once 'MangaNetwork/utils.php';

function DeleteManga($context) {

		//NO NEED $result = $context->params["request_content"];
		
		$idManga=$result['id'];
		$idUser=$context->user->id;
		
		//1ere verification :  voir si le manga appartient a l'utilisateur
		//2ement : supprimer le "user has manga" du manga liée au user connecte
	if(ExistManga($idUser,$idManga)!=false)
	{
		$db = GetDBConnection();
		
		$query = $db->prepare("DELETE *
							FROM manga
							WHERE user_id = ? and manga_id= ?");

		$response = $query->execute([$idUser, $idManga ]);
		$response = $query->fetch(PDO::FETCH_ASSOC);
	}
	else
	{
	throw new MnException("Error : no manga with ID : ".$id, 404);
	}
	return("Manga deleted with ID : ".$id); ;	
}

function ExistManga($idUser, $idManga)
{
	$query = $db->prepare("SELECT *
							FROM user_has_manga
							WHERE user_id = ? and manga_id= ?");

	$response = $query->execute([$idUser, $idManga]);
	$response = $query->fetch(PDO::FETCH_ASSOC);
	if($response==NULL){return false;}
	return true;
}

?>