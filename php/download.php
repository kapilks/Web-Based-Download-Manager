<?php
	
	/*
	 * To get details use
	 * download.php?type=head&url=......
	 * then the output is in JSON format
	 *
	 * To download file use
	 * download.php?type=get&url=.......&filename=xyz.zbc
	 * then output is in stream JSON format
	 */


	/*
	 * mainerror 	event for error reporting
	 * connecting 	event for connecting feedback
	 * receiving 	event for receiving feedback
	 * copying 		event for copying feedback
	 * complete 	event for download complete
	 */

	$maxTime = 1 * 60 * 60;// 1hr
	ini_set ( 'max_execution_time', $maxTime );
	date_default_timezone_set( 'Asia/Kolkata' );
	
	@ apache_setenv('no-gzip', 1);
	@ ini_set('zlib.output_compression', 0);

	$oldTime 		=	 microtime(true);
	$startAt 		=	 microtime(true);

	// at this interval the data will be sent to browser
	$interval 		=	 1;// after 1s the progress feedback is sent to user
	$dataDownload 	=	 0;
	$speed 			=	 0;
	$timeLeft 		=	 0;
	$avgSpeed 		=	 0;
	$downloadCompleted = false;
	$sizeWritten = false;
	$offset 		=	 0;
	$preAvgSpeed 	=	 0;
	$resumeSupport 	=	 false;
	$resuming = false;
	$type = '';
	$file = '';

	if( isset( $_GET['url'] ) && isset( $_GET['type'] ) )
	{
		include_once 'httpRequest.php';
		// ad control for reinstalling

		
		if( $_GET['type'] === 'head' )
		{
			header('Content-Type: application/json');

			$url = '';
		
			$allParameter = array_slice( $_GET, 1 );
			
			foreach ( $allParameter as $key => $value ) 
			{
				if( $key == 'url' )
				{
					$url .= $value;
				}
				else
				{
					$url .= "&$key=$value"; 
				}
				
			}
			
			$url = str_replace( ' ', '%20', $url );

			$normalRequest = new HTTPRequest();
			if( $normalRequest->open( 'HEAD', $url ) )
			{
				echo "{ \"error\":\"".$normalRequest->getError()."\" }";
				exit;
			}
	
			if( $normalRequest->send() )
			{
				echo "{ \"error\":\"".$normalRequest->getError()."\" }";
				exit;
			}
			
			$headers = $normalRequest->getResponseHeaders();
	
			if( $headers['Response-Code'] == 404 )
			{
				$error = "Server Sent 404 Code File Not Found";
				echo "{ \"error\":\"$error\" }";
				exit;
			}
			elseif( $headers['Response-Code'] == 403 || $headers['Response-Code'] == 401 )
			{
				$error = "Server Sent ".$headers['Response-Code']." Code Request Forbidden";
				echo "{ \"error\":\"$error\" }";
				exit;
			}
			elseif( $headers['Response-Code'] >= 400 )
			{
				// sending only the error code not the description

				$error = "Server Sent ".$headers['Response-Code']." Code";
				echo "{ \"error\":\"$error\" }";
				exit;
			}
			
			if( isset( $headers['Content-Length'] ) )
			{
				$sizeUnit = byteTo( $headers['Content-Length'], 3 );
			}
			else
			{
				$sizeUnit = "Unknown";
			}

			$notAllowed = ['\\','/',':','*','?','"','<','>','|'];
			
			$filterFileName = $normalRequest->getFileName();
			$filterFileName = str_replace($notAllowed, '-', $filterFileName);
			
			$output  = "{";
			$output .= "\"code\":".$headers['Response-Code'];
			$output .= ",";
			$output .= "\"size\":\"".$sizeUnit;
			$output .= "\",";
			$output .= "\"name\":\"".$filterFileName;
			$output .= "\",";
			$output .= "\"ext\":\"".$normalRequest->getFileExtension();
			$output .= "\",";			

			if( isset( $headers['Accept-Ranges'] ) )
			{
				$output .= "\"resume\":\"Yes\"";
				$acceptRange = $headers['Accept-Ranges'];
				$output .= ",";
				$output .= "\"range\":\"$acceptRange\"";
			}
			else
			{
				$output .= "\"resume\":\"No\"";
			}

			$type = getFileType( $normalRequest->getFileExtension()) ;
			$output .= ",";
			$output .= "\"type\":\"$type\"";
			
			$output .= "}";

			echo $output;
			exit;
		}
		elseif( $_GET['type'] === 'get' )
		{
			header( 'Content-Type: text/event-stream' );
			header( 'Cache-Control: no-cache' );
			//echo "<pre>";
			// filename containg extension of file also
			global $type;
			
			$filename 	=	 $_GET['filename'];
			$url 		=	 '';
			$ext 		=	 ".".pathinfo( $filename )['extension'];
			$type 		=	 getFileType( $ext );
			$date 		=	 getCurrentDate();
			$support 	= 	 $_GET['support'];
			$allParameter = array_slice( $_GET, 4 );
			
			//var_dump($_GET);

			foreach ( $allParameter as $key => $value ) 
			{
				if( $key == 'url' )
				{
					$url .= $value;
				}
				else
				{
					$url .= "&$key=$value"; 
				}
				
			}

			$url 		=	 str_replace( ' ', '%20', $url );
			//var_dump($url);
			//echo "data:$url";
			// write to database about new download added
			include 'connection.php';
			if( isset($_GET['resume']) && $_GET['resume'] == 'false' )
			{
				$query = "INSERT INTO files VALUES( NULL, \"$filename\", \"$url\", 0, \"$type\", \"$ext\", \"n\",\"$support\", 0, \"$date\", \"$date\" )";
				
				if( !$db->query( $query ) )
				{
					echo "event: mainerror\n";
					echo "data: Error Connecting To Server\n";
					echo "\n";

					ob_flush();
					flush();
					$db->close();
					exit;
				}
				$db->close();
			}
			else
			{
				$query = "SELECT url,avg_speed, resume FROM files WHERE filename = \"$filename\"";
				//var_dump($query);
				$result = $db->query( $query );
				//var_dump($result);
				$result = $result->fetch_assoc();

				$url = $result['url'];
				$preAvgSpeed = $result['avg_speed'];
				$resumeSupport = ( $result['resume'] == 'y' )?true: false;

				$db->close();

				$resuming = true;

			}
			// saving to file like "kapil.txt.doc.kink"
			// first saving in the tmp folder
			

			$request = new HTTPRequest();
			if( $request->open( 'GET', $url ) )
			{
				echo "event: mainerror\n";
				echo "data: ".$request->getError();// no EOL at last
				echo "\n\n";

				//fclose( $file );

				exit;
			}

			$file = '';
			$fullFileName = "../tmp/".$filename.".$type.kink";
			if( $_GET['resume'] == 'true' && $resumeSupport )
			{
				
				$file = fopen( $fullFileName, 'ab' );

				$offset = filesize($fullFileName);
				$done = $offset + 1;
				$byte = (float)toByte($preAvgSpeed);
				if( !($byte == 0.0) )
				{
					$startAt -= $offset / $byte;
				}
				else
				{
					$done = $offset;
				}

				$request->setRequestHeaders(['Range' => "$done-"]);
			}
			else
			{
				$offset = 0;
				$file = fopen( $fullFileName, 'wb' );
			}

			if( $request->saveTo( $file ) )
			{
				echo "event: mainerror\n";
				echo "data: ".$request->getError();// no EOL at last
				echo "\n\n";

				fclose( $file );

				exit;
			}

			$request->attachProgressFunction( 'progress' );

			if ( $request->send() )
			{
				echo "event: mainerror\n";
				echo "data: ".$request->getError();// no EOL at last
				echo "\n\n";

				fclose( $file );

				exit;
			}

			// moving temp file to destination folder
			moveToParticularDirectory( $filename );

		}
	}


	

	function progress( $channel, $downloadSize, $downloaded, $uploadSize, $uploaded )
	{

		global 	$oldTime,
				$startAt, 
				$interval, 
				$dataDownload, 
				$filename, 
				$speed, 
				$avgSpeed,
				$timeLeft,
				$downloadCompleted,
				$resuming,
				$sizeWritten,
				$offset,
				$file;


		if( curl_errno( $channel ) )
		{
			// some error occured
			// send error event

			$date = getCurrentDate();
			include 'connection.php';
			$downloadSize += $offset;
			$query = "UPDATE files SET filesize = $downloadSize, avg_speed = \"$avgSpeed\", last_try = \"$date\" WHERE filename=\"$filename\"";
			if( !$db->query( $query ) )
			{
				echo "event: mainerror\n";
				echo "data: Error Connecting To Server\n";
				echo "\n";

				ob_flush();
				flush();
				$db->close();
				exit;
			}
			$db->close();

			echo "event: mainerror\n";
			echo "data: ".curl_error( $channel );// no EOL at last
			echo "\n\n";

			fclose( $file );

			exit;
		}

		if( $downloaded <= 0 )
		{
			// still connecting
			// send connecting event
			echo "event: connecting\n";
			echo "data: Connecting To Server...\n";
			echo "\n";
			
			ob_flush();
			flush();
		}
		elseif( $downloadSize > 0 && $downloaded > 0 && !$downloadCompleted )
		{
			// this will not executed when download is completed

			// some bytes received
			// send receiving event
			if( !$sizeWritten && !$resuming )
			{
				include 'connection.php';

				$query = "UPDATE files SET filesize = $downloadSize WHERE filename = \"$filename\"";
				echo "data:$query";
				if(!$db->query($query))
				{
					echo "event: mainerror\n";
					echo "data: Error Connecting To Server\n";
					echo "\n";

					ob_flush();
					flush();
					$db->close();
					exit;
				}
				$db->close();
				$sizeWritten = true;
			}

			$newTime = microtime(true);

			if( $newTime - $oldTime >= $interval || ( $downloadSize !== 0 && ( $downloaded === $downloadSize ) ) )
			{
				if( !$downloadCompleted )
				{
					$speed 			=	 ( $downloaded - $dataDownload ) / ( $newTime - $oldTime );
				}
				
				
				if( $speed == 0.0 )
				{
					if( $downloadSize !== 0 && ( $downloaded === $downloadSize ) )
					{
						$timeLeft = timeTo( 0 );
					}
					else
					{
						$timeLeft = "Infinity";
					}
				}
				else
				{
					$timeLeft 		=	 ( $downloadSize - $downloaded ) / $speed;
					$timeLeft 		=	 timeTo( $timeLeft );
				}
				
				
				$speed 			=	 byteTo( $speed, 3 ).'ps';
				
				
				$avgSpeed 		=	 ($downloaded + $offset) / ( $newTime - $startAt );
				$avgSpeed 		=	 byteTo( $avgSpeed, 3 ).'ps';
				
				$dataDownload 	=	 $downloaded;
				$oldTime 		=	 $newTime;

				$percent		= 	 sprintf( '%.4f', ($downloaded + $offset)/ ($downloadSize+$offset) );
				
				$downloadedWithUnit 	= 	 byteTo( $downloaded+$offset, 2 );
				$downloadSizeWithUnit 	=	 byteTo( $downloadSize+$offset, 2 );
				
				echo "event: receiving\n";
				echo "data: {\n";
				echo "data: \"cSpeed\":\"$speed\",\n";
				echo "data: \"aSpeed\":\"$avgSpeed\",\n";
				echo "data: \"left\":\"$timeLeft\",\n";
				echo "data: \"per\":$percent,\n";
				echo "data: \"done\":\"$downloadedWithUnit\",\n";
				echo "data: \"size\":\"$downloadSizeWithUnit\"\n";
				echo "data: }\n";
				echo "\n";
				
				ob_flush();
				flush();
			}

		}
		if( $downloadSize !== 0 && ( $downloaded === $downloadSize ) )
		{
			if( $downloadCompleted )
			{
				return;
			}
			
			// download complete
			// send complete event
			$date = getCurrentDate();

			include 'connection.php';
			$downloadSize += $offset;
			$query = "UPDATE files SET filesize = $downloadSize, avg_speed = \"$avgSpeed\", complete = \"y\", last_try = \"$date\" WHERE filename = \"$filename\"";
			if( !$db->query( $query ) )
			{
				echo "event: mainerror\n";
				echo "data: Error Connecting To Server\n";
				echo "\n";

				ob_flush();
				flush();
				$db->close();
				exit;
			}
			$db->close();

			$downloadCompleted = true;
		}
	}


	function moveToParticularDirectory( $filename )
	{
		global $type;

		$tmpFile = "../tmp/$filename.$type.kink";
		$newFile = '';
		
		if( $type === 'doc' )
		{
			$newFile = "../Downloads/Document/$filename";
		}
		elseif( $type === 'aud' )
		{
			$newFile = "../Downloads/Music/$filename";
		}
		elseif( $type === 'com' )
		{
			$newFile = "../Downloads/Compressed/$filename";
		}
		elseif( $type === 'vid' )
		{
			$newFile = "../Downloads/Video/$filename";
		}
		elseif( $type === 'app' )
		{
			$newFile = "../Downloads/Application/$filename";
		}


		// copying file
		echo "event: copying\n";
		echo "data: Copying File To Destination Folder...\n\n";
		ob_flush();
		flush();

		if( !( copy( $tmpFile, $newFile ) && unlink( $tmpFile ) ) )
		{
			echo "event: mainerror\n";
			echo "data: Cannot Move Downloaded File To Destination Folder\n\n";

			ob_flush();
			flush();
		}

		// finished everything
		echo "event: complete\n";
		echo "data: Download Complete For $filename !!\n";
		echo "\n";
		exit;
	}


	function getCurrentDate()
	{
		return date('Y-m-d G:i:s');
	}


	function getFileType( $ext )
	{
		$ext = strtolower( $ext );

		$doc = ['.doc', '.docx', '.txt', '.js', '.css', '.pdf', '.ppt', '.jpg', '.gif', '.png','.ico','.jpeg'];
		$com = ['.7z', '.rar', '.iso', '.zip', '.gz', '.zgip', '.z'];
		$aud = ['.mp3', '.aac', '.wma', '.flac', '.amr', '.m4a', '.ogg', '.wav', '.mp2'];
		$vid = ['.3gp', '.mp4','.webm', '.avi', '.wmv', '.mkv', '.mpg', '.mov', '.vob', '.flv'];
		$app = ['.bin', '.exe'];

		if( !( array_search( $ext, $doc, true ) === false ) )
		{
			return 'doc';
		}
		if( !( array_search( $ext, $com, true ) === false ) )
		{
			return 'com';
		}
		if( !( array_search( $ext, $aud, true ) === false ) )
		{
			return 'aud';
		}
		if( !( array_search( $ext, $vid, true ) === false ) )
		{
			return 'vid';
		}
		if( !( array_search( $ext, $app, true ) === false ) )
		{
			return 'app';
		}
	}


	function timeTo( $seconds )
	{
		$seconds = ( int )$seconds;
		$min = 0;
		$hr = 0;
		$output = '';

		if( $seconds / 3600 >= 1 )
		{
			$hr = (int)( $seconds / 3600 );
			$seconds = $seconds - $hr * 3600;
		}
		if( $seconds / 60 >= 1 )
		{
			$min = (int)( $seconds / 60 );
			$seconds = $seconds - $min * 60;
		}
		
		if( $hr > 0 )
		{
			$output .= "$hr hr ";
		}
		if( $min > 0 )
		{
			$output .= "$min min ";
			if( $hr > 0 )
			{
				return rtrim( $output );
			}
		}
		if( $seconds >= 0 )
		{
			$output .= "$seconds sec";
			return $output;
		}
	}


	function byteTo( $bytes, $decimalPlaces )
	{
		if( $bytes < 0 )
		{
			return "Unknown";
		}

		$unit = 'bytes';
		
		if( $bytes >= 1000 )
		{
			$bytes /= 1024;
			$unit = 'KB';
		}

		if( $bytes >= 1000 )
		{
			$bytes /= 1024;
			$unit = 'MB';
		}

		if( $bytes >= 1000 )
		{
			$bytes /= 1024;
			$unit = 'GB';
		}

		return sprintf('%.'.$decimalPlaces.'f', $bytes ).' '.$unit;
	}

	function toByte( $data )
	{
		$value = (float)$data;
		$unit = substr($data, strpos($data, ' ') + 1);
		$unit = strtolower($unit);
		$units = ['bytesps' => 0, 'kbps' => 1, 'mbps' => 2];
		return $value * pow(1024,$units[$unit]);
	}
?>