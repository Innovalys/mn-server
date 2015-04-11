<?php
/**
 * User class
 * @package MangaNetwork
 */

/**
* User class
*/
class MnUser {

	private $id;
	private $login;
	private $pass;
	private $credentials;

	/**
	 * Manga network user constructor
	 */
	function __construct($id, $login, $pass, $credentials) {
		$this->id = $id;
		$this->login = $login;
		$this->pass = $pass;
		$this->credentials = $credentials;
	}

	/**
	 * Return the users's credentials
	 *
	 * @return string[] The user's credentials
	 */
	function getCredentials() {
		return $this->credentials;
	}
}

?>
