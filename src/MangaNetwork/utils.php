<?php

include_once 'exception.php';

// Create a new connection to the database
function GetDBConnection() {
	return new PDO('mysql:host=localhost;dbname=manga-network', 'root', '', [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
}

function GetMashapeKey() {
	return "3x4BIHw2TAmshhKqDFHbdB0oZIqqp1NKbLOjsnBCSOWKpapHEp";
}

?>