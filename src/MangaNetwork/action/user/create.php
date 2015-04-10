<?php 

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/utils.php';

function CreateUser($context) {

	$data = $context->params["json"];
	var_dump($data);

	$db = GetDBConnection();
	
	$query = $db->prepare("INSERT INTO user (login, password, mail, name, admin) 
							VALUES (?, ?, ?, ?, ?)");

	$query->execute([$data['login'], $data["password"], $data["mail"], $data["name"], $data["admin"]]);

	return ["data" =>  $data];

}

?>
