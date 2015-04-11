<?php 

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/utils.php';

function CreateUser($context) {

	$user = $context->params["json"];

	$db = GetDBConnection();
	
	$query = $db->prepare("INSERT INTO user (login, password, mail, name, admin) 
							VALUES (:login, :password, :mail, :name, :admin)");

	$query->execute($user);
    $user['id'] = $db->lastInsertId(); 
    
	return $user;

}

?>
