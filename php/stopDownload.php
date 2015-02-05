<?php
	if( isset($_GET['id']) )
	{
		$id = $_GET['id'];

		include_once 'connection.php';

		$query = "SELECT filename, complete,type FROM files WHERE id = $id";
		$result = $db->query($query);

		$result = $result->fetch_assoc();

		if( $result['complete'] == 'n' )
		{
			// writing '1' to file like abc.mp3.aud.pause.kink
			$filename = $result['filename'].".$type.pause.kink";
			$file = fopen($filename,'w');
			fwrite($file,'1');
			fclose($file);
		}

		$db->close();

	}


?>