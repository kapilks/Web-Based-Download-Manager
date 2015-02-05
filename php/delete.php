<?php
	if( isset($_GET['filename']))
	{
		$filename = $_GET['filename'];

		include 'connection.php';
		
		$query = "DELETE FROM files WHERE filename = \"$filename\"";
		$db->query( $query );
	}

?>