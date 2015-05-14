<?php 
	include_once 'MangaNetwork/utils.php';
	include_once 'MangaNetwork/validator.php';
	include_once 'MangaNetwork/manga.php';
	
/**
 *  Get manga list of the user personnal library
 * @param \MnContext $context The request context
 */
	function SearchPersonnalManga($context) {
	$validator = new MnValidator();
	$validator->addRule("id", MnValidatorRule::requiredString());
	$validator->validate($context->params);
	$user_info = $validator->getValidatedValues();
	
	
	$db = GetDBConnection();
		// recupere les id des mangas
		$query = $db->prepare("SELECT manga_id FROM user_has_manga 
							   WHERE user_id = :id  ");
		$query->execute($user_info);
		$data = $query->fetch(PDO::FETCH_ASSOC);
		if(!$data)
			return false;
		// recupere les données des mangas
		foreach($data['manga_id'] as $manga){
		
			
			$query = $db->prepare("SELECT * FROM manga 
							   WHERE id = :id");
			$query->execute($manga);
			$data = $query->fetch(PDO::FETCH_ASSOC);
			if(!$data){return false;}
				
			
			// recupere genre
			$query = $db->prepare("SELECT genre.name FROM genre JOIN genre_has_manga
								   WHERE genre_has_manga.manga_id = ? AND genre_has_manga.genre_id = genre.id");
			$query->execute([$data2['id']]);
			$data['genres'] = $query->fetchAll(PDO::FETCH_COLUMN, 0);
			// recupere auteurs
			$query = $db->prepare("SELECT author.name FROM author JOIN author_has_manga
								   WHERE author_has_manga.manga_id = ? AND author_has_manga.author_id = author.id");
			$query->execute([$data['id']]);
			$data['authors'] = $query->fetchAll(PDO::FETCH_COLUMN, 0);
			
			$myArray[] = MnManga::initFrom($data);
		}
		
		return $myArray ;
}
?>