<?php

	/*
	 *	This script will create the directory and set up database when the
	 *  manager is first launched
	 */
	
	// making the necessary directories for downloading the files
	$allDirectory = [
						'Downloads',
						'Downloads/Document',
						'Downloads/Compressed',
						'Downloads/Music',
						'Downloads/Video',
						'Downloads/Application',
						'tmp'
					];
	
	$totalDirectory = count( $allDirectory );

	for( $i = 0; $i < $totalDirectory; $i++ )
	{
		if( !is_dir( $allDirectory[$i] ) )
		{
			if( !mkdir( $allDirectory[$i] ) )
			{
				echo "Cannot Make Directory Restricted Access";
			}
		}
	}


	// setting up the database
	@ $db = new mysqli('localhost', 'root', '', '');
		
	if( mysqli_connect_errno() )
	{
		echo "Failed to connect to Server";
		$db->close();
		exit;// stop the whole script
	}

	// file path changes when script is included in other script
	$content 	= file('data/database.sql');
	$totalQuery = count( $content );

	for( $i = 0; $i < $totalQuery; $i++ )
	{
		if( !$db->query( $content[$i] ) )
		{
			echo "Error Connecting To Server";
			$db->close();
			exit;
		}
	}

	$db->close();

?>