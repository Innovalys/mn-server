<?php 
	include_once 'MangaNetwork/utils.php';
	include_once 'MangaNetwork/validator.php';
	include_once 'MangaNetwork/manga.php';
	
/**
 *  Get manga list of the user personnal library
 * @param \MnContext $context The request context
 */
function SearchOnePersonnalManga($context) {
	
	$manga_info = ['manga_name' => $context->params['manga_name'], 'id' => $context->params['id']];
	

	$db = GetDBConnection();
		// recupere les id des mangas
		$query = $db->prepare("SELECT * FROM manga 
							   WHERE manga.title LIKE ? ");
		//$query->bindParam(1, $manga_info['manga_name'], PDO::PARAM_INT);
		$query->execute(["%" . $manga_info['manga_name'] . "%"]);
		$data = $query->fetch(PDO::FETCH_ASSOC);
		
		
		if(!$data)
			return [];

		// recupere les données des mangas
		$query = $db->prepare("SELECT user_has_manga.user_id, user_has_manga.manga_id 
		                       FROM user_has_manga  
		                       WHERE user_has_manga.user_id = ?
		                       AND user_has_manga.manga_id = ?");
		$query->bindParam(1, $manga_info['id'], PDO::PARAM_INT);
		$query->bindParam(2, $data['id'], PDO::PARAM_INT);
		$query->execute();
		$data2 = $query->fetch(PDO::FETCH_ASSOC);
		
		if(!$data2)
			return [];
			
		// recupere genre
		$query = $db->prepare("SELECT genre.name FROM genre JOIN genre_has_manga
							   WHERE genre_has_manga.manga_id = ? AND genre_has_manga.genre_id = genre.id");
		$query->execute([$data['id']]);
		$data['genres'] = $query->fetchAll(PDO::FETCH_COLUMN, 0);
		// recupere auteurs
		$query = $db->prepare("SELECT author.name FROM author JOIN author_has_manga
							   WHERE author_has_manga.manga_id = ? AND author_has_manga.author_id = author.id");
		$query->execute([$data['id']]);
		$data['authors'] = $query->fetchAll(PDO::FETCH_COLUMN, 0);
		
		$query = $db->prepare("SELECT manga_chapter.id, manga_chapter.title  FROM manga_chapter 
							   WHERE manga_chapter.manga_id = ?");
		$query->execute([$data['id']]);
		$data['chapters'] = $query->fetchAll(PDO::FETCH_COLUMN, 0);
		
		
		$myArray[] = MnManga::initFrom($data);
		
		
		return $myArray ;
}
?>