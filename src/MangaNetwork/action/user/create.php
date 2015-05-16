<?php 

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/validator.php';
include_once 'MangaNetwork/utils.php';

function CreateUser($context) {

	$user = validateUser($context->params["request_content"]);

	$db = GetDBConnection();
	
	$query = $db->prepare("INSERT INTO user (login, password, mail, name) 
							VALUES (:login, :password, :mail, :name)");

	$query->execute($user);
	
	// Check if the insert failed because of an already existing login
	$user['id'] = $db->lastInsertId();
	if ($user['id'] == 0)
		throw new MnException("Error : the login '".$user['login']."' is already taken", 400);

    $user['credentials'] = MnUser::USER;

	return $user;

}

function validateUser($user) {
	
	$validator = new MnValidator();
	$validator->addRule("login",    MnValidatorRule::requiredString("/^[\w_\-]+$/m", 0, 25));
	$validator->addRule("mail",     MnValidatorRule::requiredString("/^([a-zA-Z0-9_\\.-]+\\@[\\da-z\\.-]+\\.[a-z\\.]{2,6})$/m", 0, 25));
	$validator->addRule("password", MnValidatorRule::requiredString(NULL, 0, 25)); // TODO: Hashed password
	$validator->addRule("name",     MnValidatorRule::optionalString(NULL, 0, 25));
	$validator->validate($user);

	return $validator->getValidatedValues();
}

?>
