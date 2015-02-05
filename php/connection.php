<?php
	@ $db = new mysqli('localhost', 'root', '', 'kink');
		
	if(mysqli_connect_errno())
	{
		echo "Failed to connect to Server";
		exit;
	}		
?>