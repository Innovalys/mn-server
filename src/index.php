<?php

/**
 * Main PHP endpoint
 */

include_once 'MangaNetwork/user.php';
include_once 'MangaNetwork/renderer.php';
include_once 'MangaNetwork/exception.php';
include_once 'MangaNetwork/action_router.php';
include_once 'MangaNetwork/context.php';

include_once 'MangaNetwork/action/user/create.php';
include_once 'MangaNetwork/action/user/get.php';
include_once 'MangaNetwork/action/user/manga/add.php';
include_once 'MangaNetwork/action/user/manga/add_id.php';

include_once 'MangaNetwork/action/manga/get.php';
include_once 'MangaNetwork/action/manga/get_id.php';


session_start();

try {
	// Request context
	$context = new MnContext();

	// Router definition
	$router = new MnActionRouter();

	// == User endpoints ==
	// User creation
	$router->addRule(new MnActionRule("/\/user\/?$/", "PUT", MnUser::NONE, [], function($context) {
		render(CreateUser($context));
	}));
	// == User informations ==
	$router->addRule(new MnActionRule("/\/user\/([^\/]+)\/?$/", "GET", MnUser::NONE, ["id"], function($context) {
		render(GetUser($context));
	}));

	// == Manga actions ==
	// Search TODO
	// Get
	$router->addRule(new MnActionRule("/\/manga\/?$/", "GET", MnUser::USER, [], function($context) {
		render(GetMangaAPI($context));
	}));
	// Get (id)
	$router->addRule(new MnActionRule("/\/manga\/([^\/]+)\/?$/", "GET", MnUser::USER, ['id'], function($context) {
		render(GetMangaID($context));
	}));
	
	// == User's manga actions==
	// User manga add
	$router->addRule(new MnActionRule("/\/user\/manga\/?$/", "PUT", MnUser::USER, [], function($context) {
		render(AddMangaToUser($context));
	}));
	// User manga add (id)
	$router->addRule(new MnActionRule("/\/user\/manga\/([^\/]+)\/?$/", "PUT", MnUser::USER, ['id'], function($context) {
		render(AddMangaByIdToUser($context));
	}));
	// User manga get
	$router->addRule(new MnActionRule("/\/user\/manga\/([^\/]+)\/?$/", "GET", MnUser::NONE, ["id"], function($context) {
		render(GetUserManga($context));
	}));
	// User manga chapter get TODO
	// User manga delete TODO
	// User manga updata TODO

	// Dispatch
	$router->route($context);

} catch(MnException $e) {
	render($e, true, $e->getCode()); // Handled error
} catch(Exception $e) {
	render($e, true, 500); // Server error
}

?>
