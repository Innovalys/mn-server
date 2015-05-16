<?php

/**
 * Main PHP endpoint
 */

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/context.php';
include_once 'MangaNetwork/renderer.php';
include_once 'MangaNetwork/exception.php';
include_once 'MangaNetwork/action_router.php';

include_once 'MangaNetwork/action/user/get.php';
include_once 'MangaNetwork/action/user/create.php';
include_once 'MangaNetwork/action/user/manga/get.php';
include_once 'MangaNetwork/action/user/manga/add.php';
include_once 'MangaNetwork/action/user/manga/add_id.php';
include_once 'MangaNetwork/action/user/manga/update.php';
include_once 'MangaNetwork/action/user/manga/delete.php';
include_once 'MangaNetwork/action/user/manga/searchAllManga.php';
include_once 'MangaNetwork/action/user/manga/searchOneManga.php';
include_once 'MangaNetwork/action/user/manga/chapter/get.php';
include_once 'MangaNetwork/action/user/manga/chapter/get_id.php';
include_once 'MangaNetwork/action/manga/get.php';
include_once 'MangaNetwork/action/manga/get_id.php';
include_once 'MangaNetwork/action/manga/search.php';
include_once 'MangaNetwork/action/manga/chapter/get.php';
include_once 'MangaNetwork/action/manga/chapter/get_id.php';



try {
	// Request context
	$context = new MnContext();

	// Router definition
	
	
	$router = new MnActionRouter();

	// == User endpoints ==
	// User creation
	$router->addRule(new MnActionRule("/mn-server\/user\/?$/", "PUT", MnUser::NONE, [], function($context) {
		render(CreateUser($context));
	}));
	// == User informations ==
	$router->addRule(new MnActionRule("/mn-server\/user\/([^\/]+)\/?$/", "GET", MnUser::NONE, ['id'], function($context) {
		render(GetUser($context));
	}));

	// == Manga actions ==
	// Search
	$router->addRule(new MnActionRule("/mn-server\/manga\/search\/([^\/]+)\/([^\/]+)\/?$/", "GET", MnUser::NONE, ["source","query"], function($context) {
		render(SearchManga($context));
	}));
	// Get chapter
	$router->addRule(new MnActionRule("/mn-server\/manga\/((?!id).+)\/([^\/]+)\/([^\/]+)\/?$/", "GET", MnUser::USER, ['source', 'id', 'chapter_id'], function($context) {
		render(GetMangaChapterAPI($context));
	}));
	// Get (id) chapter
	$router->addRule(new MnActionRule("/mn-server\/manga\/id\/([^\/]+)\/([^\/]+)\/?$/", "GET", MnUser::USER, ['id', 'chapter_id'], function($context) {
		render(GetMangaChapterID($context));
	}));
	// Get
	$router->addRule(new MnActionRule("/mn-server\/manga\/((?!id).+)\/([^\/]+)\/?$/", "GET", MnUser::USER, ['source', 'id'], function($context) {
		render(GetMangaAPI($context));
	}));
	// Get (id)
	$router->addRule(new MnActionRule("/mn-server\/manga\/id\/([^\/]+)\/?$/", "GET", MnUser::USER, ['id'], function($context) {
		render(GetMangaID($context));
	}));
	
	// == User's manga actions ==
	// User manga add
	$router->addRule(new MnActionRule("/mn-server\/user\/manga\/((?!id).+)\/([^\/]+)\/?$/", "PUT", MnUser::USER, ['source', 'id'], function($context) {
		render(AddMangaToUserAPI($context));
	}));
	// User manga chapter get
	$router->addRule(new MnActionRule("/mn-server\/user\/manga\/((?!id).+)\/([^\/]+)\/([^\/]+)\/?$/", "GET", MnUser::USER, ['source', 'id', 'chapter_id'], function($context) {
		render(GetUserMangaChapterAPI($context));
	}));
	// User manga add (id)
	$router->addRule(new MnActionRule("/mn-server\/user\/manga\/id\/([^\/]+)\/?$/", "PUT", MnUser::USER, ['id'], function($context) {
		render(AddMangaToUserID($context));
	}));
	// User manga chapter get (id)
	$router->addRule(new MnActionRule("/mn-server\/user\/manga\/id\/([^\/]+)\/([^\/]+)\/?$/", "GET", MnUser::USER, ['id', 'chapter_id'], function($context) {
		render(GetUserMangaChapterID($context));
	}));
	// User manga get
	$router->addRule(new MnActionRule("/mn-server\/user\/manga\/([^\/]+)\/?$/", "GET", MnUser::USER, ['id'], function($context) {
		render(GetUserMangaFromDb($context));
	}));
	// User manga delete
	$router->addRule(new MnActionRule("/mn-server\/user\/manga\/([^\/]+)\/?$/", "DELETE", MnUser::USER, ['id'], function($context) {
		render(DeleteManga($context));
	}));
	// User manga update
	$router->addRule(new MnActionRule("/mn-server\/user\/manga\/([^\/]+)\/?$/", "POST", MnUser::USER, ["id"], function($context) {
		render(UpdateManga($context));
	}));
	// User manga collection (all)
	$router->addRule(new MnActionRule("/mn-server\/user\/mangas\/([^\/]+)\/?$/", "GET", MnUser::USER, ["id"], function($context) {
 		render(SearchallPersonnalManga($context));
 	}));
	// User manga search
	$router->addRule(new MnActionRule("/mn-server\/user\/search\/([^\/]+)\/([^\/]+)\/?$/", "GET", MnUser::USER,['id','manga_name'], function($context) {
 		render(SearchOnePersonnalManga($context));
 	}));

	// Dispatch
	$router->route($context);

} catch(MnException $e) {
	render($e, true, $e->getCode()); // Handled error
} catch(Exception $e) {
	render($e, true, 500); // Server error
}

?>
