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

	const NONE = 0b00;
	const USER = 0b01;
	const ADMIN = 0b10;
	
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
