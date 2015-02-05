<?php
	if( isset($_GET['name']) )
	{
		header('Content-Type: application/json');

		include_once 'connection.php';
		$filename = $_GET['name'];
		$filename = str_replace('%20', ' ', $filename);

		$ext = substr($filename,strrpos($filename, '.'));
		$name = strstr($filename, $ext,true);

		$query = "SELECT COUNT(*) FROM files WHERE filename LIKE \"$name%\"";
		$result = $db->query( $query );
		$result = $result->fetch_assoc();
		$totalMatch = $result['COUNT(*)'];
		
		if( $totalMatch > 0 )
		{
			$filename = str_replace($ext, "($totalMatch)".$ext, $filename);
			$error = '';
			if($filename == '')
			{
				$filename = 'file';
				$error = "File Dont Hav Any Name. Save It As $filename Or Change It Yourself";
			}
			else
			{
				$error = "File With Same Name Exits. Save It As $filename Or Change It Yourself";
			}

			echo "{";
			echo "\"error\":\"$error\"";
			echo ",";
			echo "\"name\":\"$filename\"";
			echo "}";
			
		}
	}

?>