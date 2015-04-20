<?php 

include_once 'MangaNetwork/manga.php';
include_once 'MangaNetwork/utils.php';

function DeleteManga($context) {

		$result = $context->params["request_content"];
		$id=$result['id'];
		
	if(ExistManga($id)!=false)
	{
		$db = GetDBConnection();
		$query = $db->prepare("DELETE *
							FROM manga
							WHERE id = ?");

		$response = $query->execute([$id]);
		$response = $query->fetch(PDO::FETCH_ASSOC);
	}
	else
	{
	throw new MnException("Error : no manga with ID : ".$id, 404);
	}
	return("Manga deleted with ID : ".$id); ;	
}

function ExistManga($id)
{
	$query = $db->prepare("SELECT *
							FROM manga
							WHERE id = ?");

	$response = $query->execute([$id]);
	$response = $query->fetch(PDO::FETCH_ASSOC);
	if($response==NULL){return false;}
	return true;
}

?>