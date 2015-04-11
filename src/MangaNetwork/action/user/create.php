<?php 

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/utils.php';

function CreateUser($context) {

	$user = $context->params["request_content"];
	
	checkUser($user);

	$db = GetDBConnection();
	
	$query = $db->prepare("INSERT INTO user (login, password, mail, name) 
							VALUES (:login, :password, :mail, :name)");

	$query->execute($user);
    $user['id'] = $db->lastInsertId(); 

	return $user;

}

function checkUser($user) {
	
	$mail_regex = "/^([a-zA-Z0-9_\\.-]+\\@[\\da-z\\.-]+\\.[a-z\\.]{2,6})$/m"; 
	$login_regex = "/^[\w_\-]+$/m";

	// Check missings fields
	if (isset($user['mail']) == false)
		throw new MnException("Missing user field: [mail]", 400);
		
	if (isset($user['login']) == false)
		throw new MnException("Missing user field: [login]", 400);
		
	if (isset($user['name']) == false)
		throw new MnException("Missing user field: [name]", 400);
		
	if (isset($user['password']) == false)
		throw new MnException("Missing user field: [password]", 400);
	
	// Check fields with regex
	if (preg_match_all($mail_regex, $user['mail']) == false)
		throw new MnException('Rejected user field: [mail] => ['. $user['mail'] .']', 400);

	if (preg_match_all($login_regex, $user['login']) == false)
		throw new MnException('Rejected user field: [login] => ['. $user['login'] .']', 400);

	if (preg_match_all($login_regex, $user['name']) == false)
		throw new MnException('Rejected user field: [name] => ['. $user['name'] .']', 400);

	// TODO: Hashed password
}

?>
