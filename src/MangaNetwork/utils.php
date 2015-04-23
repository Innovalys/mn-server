<?php

include_once 'exception.php';

// Create a new connection to the database
function GetDBConnection() {
	return new PDO('mysql:host=localhost;dbname=manga-network', 'root', '', [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
}

function GetMashapeKey() {
	return "HhfIVRaNifmshprSUa6pmLwUo0fvp14H42rjsn8WSeSTUrRW9W";
}

?>