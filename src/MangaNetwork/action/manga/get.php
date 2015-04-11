<?php 

include_once 'MangaNetwork/manga.php';
include_once 'MangaNetwork/utils.php';

function GetManga($context) {

	$data = $context->params["id"];

	$db = GetDBConnection();
	
	$query = $db->prepare("SELECT *
							FROM manga
							WHERE id = ?");

	$response = $query->execute([$data]);
	$response = $query->fetch(PDO::FETCH_ASSOC);

	if($response == NULL) {
		throw new MnException("Error : no manga with ID : ".$data, 404);
	}

	return $response;
}

?>
