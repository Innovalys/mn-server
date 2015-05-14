<?php

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/validator.php';
include_once 'MangaNetwork/utils.php';

include_once 'MangaNetwork/action/manga/utils.php';

/**
 * Add a manga to the connected user
 * @param \MnContext $context The request context
 */
function GetMangaAPI($context) {

	$manga_info = ['source' => $context->params['source'], 'id' => $context->params['id']];

	// Get manga
	return getManga($manga_info);
}


function GetManga($context) {
	$idManga = $context->params["id"];
	$idUser=$context->user->id;
	
	$db = GetDBConnection();
	//liaison entre user has manga et manga
	//retourner le contenu de la table manga liée a la biliotheque de l'utilisateur.
	
	$query = $db->prepare("SELECT manga.id, manga.title, manga.page_nb,
	manga.source_API, manga.source_URL, manga.source_ID,
	manga.update_date, manga.release_date, manga.completed,
	manga.description
							FROM manga, user_has_manga
								
							WHERE user_has_manga.manga_id=manga.id
							and user_has_manga.manga_id = ?
							and user_has_manga.user_id= ?");
	$response = $query->execute([$idManga, $idUser]);
	$response = $query->fetch(PDO::FETCH_ASSOC);
	if($response == NULL) {
		throw new MnException("Error : no manga with ID : ".$idManga, 404);
	}
	return $response;
}

function GetMangaGenre($idManga) {

	$db = GetDBConnection();
	//retourner le genre du manga
	
	$query = $db->prepare("select genre.name from genre, genre_has_manga, manga where
	genre_has_manga.id=genre.id and genre_has_manga.id=manga.id and manga.id=?");
	$response = $query->execute([$idManga]);
	$response = $query->fetch(PDO::FETCH_ASSOC);
	if($response == NULL) {
		throw new MnException("Error : no genre with MangaID : ".$idManga, 404);
	}
	return $response;
}

function GetMangaAuteur($idManga) {

	$db = GetDBConnection();

	//retourner le(s) auteur du manga
	
	$query = $db->prepare("select author.name from author, author_has_manga, manga where
	author_has_manga.id=author.id and author_has_manga.id=manga.id and manga.id=?");

	$response = $query->execute([$idUser]);
	$response = $query->fetch(PDO::FETCH_ASSOC);

	if($response == NULL) {
		throw new MnException("Error : no author with ID : ".$idManga, 404);
	}

	return $response;
}
?>