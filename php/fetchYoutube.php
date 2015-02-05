<?php
	

	/*
	 *
	 *  This script fetches the link for youtube video files to download
	 *	Pass link to youtube video with 'url' as parameter
	 *
	 *	fetchYoutube.php?url=https://www.youtube.com/watch?v=2gLq4Ze0Jq4
	 *  Also have playlist support to link from video in playlist
	 *  
	 *  It returns the link in json format
	 *  Also gives the quality of the video and size of video
	 *	 
	 *	{1 : { link:..., quality:..., size:...},
	 *	 2 : { link:..., quality:..., size:...},... }
	 *
	 *	It does not fetches link link to copyright videos
	 *
	 */

	include 'httpRequest.php';
	
	if( isset( $_GET['url'] ) )
	{
		/*
		 *	When any url is passed
		 */

		header( "Content-Type: application/json" );
		
		$maxTime = 5 * 60;// 5 min
		ini_set ( 'max_execution_time', $maxTime );
		
		$url = $_GET['url'];
		$url = urldecode( $url );
		
		if( strpos( $url, 'http' ) === false )
		{
			$url = "http://$url";
		}

		if( isset($_GET['list'] ) )
		{
			$url = "$url&list=".$_GET['list'];
		}
		
		$youtubeFormat = '#^https?://www.youtube.com/#';
		preg_match( $youtubeFormat, $url,$matches);
		
		if( preg_match( $youtubeFormat, $url ) !== 1 )
		{
			errorMessage( "Not a valid youtube link" );
		}


		if( strpos( $url, 'watch?v=') !== false )
		{
			if( strpos( $url, 'list=') !== false && isset( $_GET['all'] ) && $_GET['all'] === 'true' )
			{
				/*
				 *	All link from playlist
				 */

				echo getPlaylistLinks( $url );
			}
			else
			{
				/*
				 *	Get only single video specified by id
				 */

				echo getIdLinks( $url );
			}

		}
		elseif( strpos( $url, 'list=') !== false )
		{
			/*
			 *	All link from playlist
			 */

			echo getPlaylistLinks( $url );
		}
		else
		{
			/*
			 *	Not valid url
			 */

			errorMessage( "This is not a valid youtube link" );
		}
	}


	


	function getPlaylistLinks( $url )
	{
		/*
		 *	Start fetching from playlist
		 */

		$id = getPlaylistId( $url );

		$allVideoIds = fetchIdFromPlaylist( $id );

		$output = '';
		foreach ( $allVideoIds as $key => $value ) 
		{
			$youtubeUrl = "http://www.youtube.com/watch?v=$value";
		
			$details = getIdLinks( $youtubeUrl );

			$output .= ", \"$key\":$details";
		}

		$output = substr( $output, 2 );
		$output = "{".$output."}";

		return $output;
	}

	function getIdLinks( $url )
	{
		/*
		 *	Start fetching grom id
		 */

		$id = getId( $url );
		$jsonString = fetchLinksFromId( $id );

		return $jsonString;
	}

	function getId( $url )
	{
		preg_match( '#v=([^&]*)#', $url, $matches );
		
		return $matches[1];
	}

	function getPlaylistId( $url )
	{
		preg_match( '#list=([^&]*)#', $url, $matches );

		return $matches[1];
	}

	function fetchLinksFromId( $videoId )
	{
		/*
		 *	Return links to specified video id in JSON string format
		 */

		$requestUrl = "http://youtube.com/get_video_info?video_id=$videoId";

		$request = new HTTPRequest();
		
		if( $request->open( 'GET', $requestUrl ) || $request->send() )
		{
			errorMessage( "Something went wrong try again later" );
		}


		$response = $request->getResponseBody();

		parse_str( $response, $parseResponse );

		if( $parseResponse['status'] !== 'ok' )
		{
			errorMessage( $parseResponse['reason'] );
		}

		$assocItags = getItags( $parseResponse['fmt_list'] );
		
		$thumbnail = '';
		if( isset( $parseResponse['iurlmaxres'] ) )
		{
			$thumbnail = $parseResponse['iurlmaxres'];
		}
		elseif( isset( $parseResponse['iurl'] ) )
		{
			$thumbnail = $parseResponse['iurl'];
		}
		elseif( isset( $parseResponse['iurlhq'] ) )
		{
			$thumbnail = $parseResponse['iurlhq'];
		}
		elseif( isset( $parseResponse['iurlsd'] ) )
		{
			$thumbnail = $parseResponse['iurlsd'];
		}
		elseif( isset( $parseResponse['iurlmq'] ) )
		{
			$thumbnail = $parseResponse['iurlmq'];
		}
	

		$author = '';
		if( isset( $parseResponse['author'] ) )
		{
			$author = $parseResponse['author'];
		}

		$title = '';
		if( isset( $parseResponse['title'] ) )
		{
			$title = $parseResponse['title'];
		}

		$duration = 0;
		if( isset( $parseResponse['length_seconds'] ) )
		{
			$duration = $parseResponse['length_seconds'];
			$duration = timeTo( $duration );
		}
		
		/*
		 *  preparing output
		 */

		$output = "{";

		$title !== '' && $output = "$output\"title\":\"$title\"";
		$author !== '' && $output = "$output, \"author\":\"$author\"";
		$thumbnail !== '' && $output = "$output, \"thumbnail\":\"$thumbnail\"";
		$output .= ", \"duration\":\"$duration\"";

		if( isset( $parseResponse['url_encoded_fmt_stream_map'] ) )
		{
			$jsonLink = parseVideoUrl( $parseResponse['url_encoded_fmt_stream_map'], $assocItags, $title );
			$output .= ", \"links\":{$jsonLink}";
		}

		$output .= "}";

		return $output;
		 

	}

	function fetchIdFromPlaylist( $playlistId )
	{
		/*
	 	 *	Return all video id from the playlist in an ARRAY
		 */

		$start = 1;
		$maxResults = 25;
		$allIds = [];

		while( true )
		{
			$matches = [];

			$playlistUrl = "http://gdata.youtube.com/feeds/api/playlists/$playlistId?alt=json&start-index=$start&max-results=$maxResults";//&max-results=$maxResults&start-index=$start";
			//var_dump($playlistUrl);
			$request = new HTTPRequest();
			if( $request->open( 'GET', $playlistUrl ) )
			{
				errorMessage( "Something went wrong" );
			}
			if( $request->send() )
			{
				errorMessage( "Something went wrong" );
			}

			$response = $request->getResponseBody();
			
			preg_match_all( '#watch\?v=([^&]*)&feature=youtube_gdata_player#', $response, $matches );
			
			$allIds = array_merge( $allIds, $matches[1] );
			$start += $maxResults;
			//echo count($matches[1]);
			//echo "</br>";
			//echo count($maxResults);
			//echo "</br>";
			if( count( $matches[1] ) < $maxResults )
			{
				break;
			}
		}
		//echo count($allIds);
		return $allIds;
		
	}

	function getItags( $allTags )
	{
		$delimiter = ',';
		$allTags = explode( $delimiter, $allTags );

		$assocItags = [];
		
		foreach ( $allTags as $key => $value )
		{
			$parts = explode( '/', $value );
			$assocItags[$parts[0]] = substr( $parts[1], strpos( $parts[1], 'x' ) + 1 ).'p';
		}

		return $assocItags;
	}

	function parseVideoUrl( $link, $assocItags, $title )
	{
		$url = explode( ',', $link );
		$allLinks = '';

		foreach ( $url as $key => $value ) 
		{

			// itags
			preg_match( '#itag=([^&]*)#', $value, $matches );
			$itag = $matches[1];
			$quality = $assocItags[$itag];
			
			// type
			preg_match( '#type=([^&]*)#', $value, $matches );
			$type = $matches[1];
			$type = explode( ';', urldecode( $type ) );
			$type = getFileType( $type[0] );

			// location
			preg_match( '#url=([^&]*)#', $value, $matches );
			$location = $matches[1];
			$location = urldecode( $location );

			// size
			$request = new HTTPRequest();
			if( $request->open( 'HEAD', $location ) )
			{
				errorMessage( "Something went wrong" );
			}

			if( $request->send() )
			{
				errorMessage( "Something went wrong" );
			}

			$size = $request->getContentLength();
			$size = byteTo( $size, 3 );

			// making JSON
			$location .= "&title=$title";

			$allLinks .= ", \"$key\":{ \"url\":\"$location\", \"type\":\"$type\", \"quality\":\"$quality\", \"size\":\"$size\" }";
		}
		$allLinks = substr( $allLinks, 2 /* Removing first comma */);
		$allLinks = "{".$allLinks."}";
		
		return $allLinks;
	}

	function timeTo( $seconds )
	{
		/*
		 *	Convert x second into 'a hr b min' or 'a min b sec' or 'x sec' format
		 */
		
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


	function getFileType( $mime )
	{
		/*
		 * Get file extension from video mime type
		 */

		$mime = strtolower($mime);
		$type = '';

		switch( $mime )
		{
			case 'video/mp4'	:	$type = 'mp4';
									break;

			case 'video/webm'	:	$type = 'webm';
									break;

			case 'video/3gpp'	:	$type = '3gp';
									break;

			case 'video/x-flv'	:	$type = 'flv';
									break;
		}

		return $type;
	}

	function errorMessage( $error )
	{
		echo "{";
		echo "\"error\":\"$error\"";
		echo "}";

		exit;
	}


?>