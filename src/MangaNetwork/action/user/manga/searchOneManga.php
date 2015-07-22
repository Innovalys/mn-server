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
		// Get genres
		$query = $db->prepare("SELECT genre.name FROM genre JOIN genre_has_manga
							   WHERE genre_has_manga.manga_id = ? AND genre_has_manga.genre_id = genre.id");
		$query->execute([$manga['id']]);
		$manga['genres'] = $query->fetchAll(PDO::FETCH_COLUMN, 0);

		// Get authors
		$query = $db->prepare("SELECT author.name FROM author JOIN author_has_manga
							   WHERE author_has_manga.manga_id = ? AND author_has_manga.author_id = author.id");
		$query->execute([$manga['id']]);
		$manga['authors'] = $query->fetchAll(PDO::FETCH_COLUMN, 0);
		
		// Get chapters
		$query = $db->prepare("SELECT manga_chapter.id, manga_chapter.title  FROM manga_chapter 
							   WHERE manga_chapter.manga_id = ?");
		$query->execute([$manga['id']]);
		$manga['chapters'] = $query->fetchAll(PDO::FETCH_ASSOC);
		
		// Init a new manga
		$myArray[] = MnManga::initFrom($manga);
	}
	
	return $myArray ;
}
?>