<?php 
	include_once 'MangaNetwork/utils.php';
	include_once 'MangaNetwork/validator.php';
	include_once 'MangaNetwork/manga.php';
	
/**
 *  Get manga list of the user personnal library
 * @param \MnContext $context The request context
 */
function SearchOnePersonnalManga($context) {
	
	$manga_info = ['manga_name' => "%" . $context->params['manga_name'] . "%", 'id' => $context->params['id']];
	
	$db = GetDBConnection();
	// Get the manga's IDs
	$query = $db->prepare("SELECT * FROM manga
		                   INNER JOIN user_has_manga
		                        ON user_has_manga.user_id = ? AND user_has_manga.manga_id = manga.id
		                   WHERE manga.title LIKE ?");

	$query->bindParam(1, $context->user->id, PDO::PARAM_STR);
	$query->bindParam(2, $manga_info['manga_name'], PDO::PARAM_STR);
	$query->execute();

	$data = $query->fetchAll(PDO::FETCH_ASSOC);

	if(!$data)
		return [];

	$myArray = []; // List of mangas
	
	foreach ($data as $manga) {
		// Init a new manga
		$myArray[] = MnManga::initFrom(setMangaRelativeInfo($db, $manga, $context->user));
	}
	
	return $myArray ;
}
?>