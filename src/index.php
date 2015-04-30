<?php

/**
 * Main PHP endpoint
 */

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/action/user/create.php';
include_once 'MangaNetwork/action/user/get.php';
include_once 'MangaNetwork/action/user/add_manga.php';
include_once 'MangaNetwork/renderer.php';
include_once 'MangaNetwork/exception.php';
include_once 'MangaNetwork/action_router.php';
include_once 'MangaNetwork/context.php';
include_once 'MangaNetwork/action/manga/get.php';
include_once 'MangaNetwork/action/manga/delete.php';
include_once 'MangaNetwork/action/manga/update.php';



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
	$router->addRule(new MnActionRule("/\/test_rest\/user\/manga\/([^\/]+)/", "GET", MnUser::USER, ["id"], function($context) {
		render(GetManga($context));
	}));

	$router->addRule(new MnActionRule("/\/test_rest\/manga\/([^\/]+)/", "DELETE", MnUser::USER, ["id"], function($context) {
		render(DeleteManga($context));
	}));
	/*
	UPDATE
	*/
	$router->addRule(new MnActionRule("/\/test_rest\/manga\/([^\/]+)/", "POST", MnUser::USER, ["id"], function($context) {
		render(UpdateManga($context));
		}));
	
	$router->addRule(new MnActionRule("/\/search\/manga\//", "GET", [], [], function($context) {
		render(searchManga($context));

	}));

	// Dispatch
	$router->route($context);

} catch(MnException $e) {
	render($e, true, $e->getCode()); // Handled error
} catch(Exception $e) {
	render($e, true, 500); // Server error
}

?>
