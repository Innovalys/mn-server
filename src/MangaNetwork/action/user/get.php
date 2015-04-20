<?php 

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/utils.php';

function GetUser($context) {

	$data = $context->params["id"];

	$db = GetDBConnection();
	
	$query = $db->prepare("SELECT id, mail, name, credentials
							FROM user
							WHERE id = ?");

	$response = $query->execute([$data]);
	$response = $query->fetch(PDO::FETCH_ASSOC);

	return $response;
}

?>
