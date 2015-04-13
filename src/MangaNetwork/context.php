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

		if ($this->params["request_content"] === null && json_last_error() !== JSON_ERROR_NONE) {
		    throw new MnException("Error : malformed JSON content", 400);
		}

		$this->method = $_SERVER['REQUEST_METHOD'];
		$this->uri = $_SERVER['REQUEST_URI'];

		if (empty($_SERVER['HTTP_AUTHORIZATION']) == false)
			$this->user = authenticateUser($_SERVER['HTTP_AUTHORIZATION']);
		else
			$this->user = null;
	}
}

function authenticateUser($auth) {

	$auth = sscanf($auth, "Basic %s");

	if ($auth == null)
		throw new MnException("Error: malformed authorization header", 400);
		
	$decoded = base64_decode($auth[0]);
	$exploded = explode(":", $decoded);

	if ($exploded == null || empty($exploded[0]) || empty($exploded[1]))
		throw new MnException("Error: couldn't parse login:password ", 400);

	$db = GetDBConnection();
	
	$query = $db->prepare("SELECT *
							FROM user
							WHERE login = ?
							AND password = ?");

	$response = $query->execute($exploded);
	$user = $query->fetch(PDO::FETCH_ASSOC);

	if ($user == false)
		throw new MnException("Error: unknown login/password", 400);

	return MnUser::initFrom($user);
}

?>
