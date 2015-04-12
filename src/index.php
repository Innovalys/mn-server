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


session_start();

try {

	// Request context
	$context = new MnContext();

	// Router definition
	$router = new MnActionRouter();
	$router->addRule(new MnActionRule("/\/test_rest\/user\/?$/", "PUT", [], [], function($context) {
		render(CreateUser($context));
	}));
	$router->addRule(new MnActionRule("/\/test_rest\/user\/([^\/]+)/", "GET", [], ["id"], function($context) {
		render(GetUser($context));
	}));
	$router->addRule(new MnActionRule("/\/test_rest\/user\/([^\/]+)\/manga\/?$/", "PUT", [], ["id"], function($context) {
		render(AddMangaToUser($context));
	}));
	$router->addRule(new MnActionRule("/\/test_rest\/test\/?$/", "GET", [], [], function($context) {
		render([ "hello" => "world" ]);
	}));
	$router->addRule(new MnActionRule("/\/test_rest\/test\/([^\/]+)\/?$/", "GET", [], ["id"], function($context) {
		render([ "hello" => "world", "from" => $context->uri, "id" => $context->params["id"] ]);
	}));
	$router->addRule(new MnActionRule("/\/test_rest\/manga\/([^\/]+)/", "GET", [], ["id"], function($context) {
		render(GetManga($context));
	}));

	// Dispatch
	$router->route($context);

} catch(MnException $e) {
	render($e, true, $e->getCode()); // Handled error
} catch(Exception $e) {
	render($e, true, 500); // Server error
}

?>