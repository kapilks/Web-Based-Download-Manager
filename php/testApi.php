<?php
	
	var_dump($_SERVER);
	$uri = $_SERVER['REQUEST_URI'];
	$uri = substr($uri, 0,strrpos($uri, '/'));
	$uri = "http://localhost".$uri;
	var_dump($uri);
?>