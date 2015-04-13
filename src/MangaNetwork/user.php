<?php
/**
 * User class
 * @package MangaNetwork
 */

/**
* User class
*/
class MnUser {

	public $id;
	public $login;
	public $pass;
	public $credentials;

	/**
	 * Manga network user constructor
	 */
	function __construct($id, $login, $pass, $credentials) {
		$this->id = $id;
		$this->login = $login;
		$this->pass = $pass;
		$this->credentials = $credentials;
	}

	static function initFrom($data) {
		return new MnUser($data['id'], $data['login'], $data['password'], $data['credentials']);
	}
}

?>