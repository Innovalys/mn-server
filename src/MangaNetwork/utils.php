<?php

// Create a new connection to the database
function GetDBConnection() {
	return new PDO('mysql:host=localhost;dbname=manga-network', 'root', '', [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
}

?>	
