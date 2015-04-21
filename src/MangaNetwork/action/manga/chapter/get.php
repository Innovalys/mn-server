<?php 

include_once 'MangaNetwork/manga.php';
include_once 'MangaNetwork/utils.php';
include_once 'MangaNetwork/action/user/add_manga.php';

function GetMangaChapter($context) {

	$validator = new MnValidator();
	$validator->addRule("manga_id",    MnValidatorRule::requiredString());
	$validator->addRule("chapter_id",     MnValidatorRule::requiredString());
	$validator->validate($context->params["request_content"]);
	$chapter_info = $validator->getValidatedValues();


	$db = GetDBConnection();

	$query = $db->prepare("SELECT * FROM manga JOIN user_has_manga
		                   WHERE manga.id = ? AND user.id = ? 
		                   AND user_has_manga.user_id = user.id AND user_has_manga.manga_id = manga.id");
	$response = $query->execute([$chapter_info['manga_id'], etc...]);

	$response = $query->fetch(PDO::FETCH_ASSOC);

	if($response == NULL) {
		throw new MnException("Error : no manga with ID : ".$data, 404);
	}

	return $response;
}

?>