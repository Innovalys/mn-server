<?php 
	include_once 'MangaNetwork/utils.php';
	include_once 'MangaNetwork/validator.php';
	include_once 'MangaNetwork/manga.php';
	
/**
 *  Get manga list of the user personnal library
 * @param \MnContext $context The request context
 */
function SearchallPersonnalManga($context) {
	$validator = new MnValidator();
	$validator->addRule("id", MnValidatorRule::requiredString());
	$validator->validate($context->params);
	$user_info = $validator->getValidatedValues();
	
	$values = [];
	
	$db = GetDBConnection();
		// recupere les id des mangas
		$main_query = $db->prepare("SELECT * FROM user_has_manga WHERE user_id = :id");
		$main_query->execute($user_info);

		// recupere les données des mangas
		while($data_user = $main_query->fetch(PDO::FETCH_ASSOC)){

			$query = $db->prepare("SELECT * FROM manga WHERE id = ?");
			$query->bindParam(1, $data_user["manga_id"], PDO::PARAM_INT);
			$query->execute();
			$data = $query->fetch(PDO::FETCH_ASSOC);

			if(!$data)
				return [];

			$data['user_info'] = $data_user;
			
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
			$data['chapters'] = $query->fetchAll(PDO::FETCH_ASSOC);
			
			
			$values[] = MnManga::initFrom($data);
		}
		
		return $values ;
}
?>