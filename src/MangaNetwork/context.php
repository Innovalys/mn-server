<?php
/**
 * Context of the request
 * @package MangaNetwork
 */

/**
 * Context of the request
 */
class MnContext {
	
	/**
	 * @var string[] The list of GET and POST parameters
	 */
	public $params;

	/**
	 * @var string The HTTP method used
	 */
	public $method;

	/**
	 * @var string The URI requested
	 */
	public $uri;

	/**
	 * @var \User|null The user from the session
	 */
	public $user;

	/**
	 * Constructor for the context of the request
	 */
	function __construct() {
		$this->params = array_merge($_GET, $_POST);
		$this->params["request_content"] = json_decode(file_get_contents("php://input"), true);
		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->uri = $_SERVER['REQUEST_URI'];
		$this->user = isset($_SESSION['USER']) ? $_SESSION['USER'] : null;
	}
}

?>
