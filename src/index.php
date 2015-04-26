<?php

/**
 * Main PHP endpoint
 */

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/action/search/search.php';
include_once 'MangaNetwork/action/user/create.php';
include_once 'MangaNetwork/action/user/get.php';
include_once 'MangaNetwork/action/user/add_manga.php';
include_once 'MangaNetwork/renderer.php';
include_once 'MangaNetwork/exception.php';
include_once 'MangaNetwork/action_router.php';
include_once 'MangaNetwork/context.php';
include_once 'MangaNetwork/action/manga/get.php';
include_once 'MangaNetwork/action/search/search_personnal_manga.php';


session_start();

try {
	// Request context
	$context = new MnContext();

	// Router definition
	
	
	$router = new MnActionRouter();
	
	$router->addRule(new MnActionRule("/\/test_rest\/user\/?$/", "PUT", MnUser::NONE, [], function($context) {
		render(CreateUser($context));
	}));
	$router->addRule(new MnActionRule("/\/test_rest\/user\/([^\/]+)/", "GET", MnUser::NONE, ["id"], function($context) {
		render(GetUser($context));
	}));
	$router->addRule(new MnActionRule("/\/test_rest\/user\/manga\/?$/", "PUT", MnUser::USER, [], function($context) {
		render(AddMangaToUser($context));
	}));
	$router->addRule(new MnActionRule("/\/test_rest\/manga\/([^\/]+)/", "GET", MnUser::NONE, ["id"], function($context) {
		render(GetManga($context));
	}));
	$router->addRule(new MnActionRule("/\/search\/([^\/]+)\/([^\/]+)\/?$/", "GET", MnUser::NONE, ["source","query"], function($context) {
		render(SearchManga($context));
	}));

	$router->addRule(new MnActionRule("/\/test_rest\/?$/", "GET", MnUser::NONE, [], function($context) {
		render(searchPersonnalManga($context));
	}));
	// Dispatch
	$router->route($context);

} catch(MnException $e) {
	render($e, true, $e->getCode()); // Handled error
} catch(Exception $e) {
	render($e, true, 500); // Server error
}

?>
