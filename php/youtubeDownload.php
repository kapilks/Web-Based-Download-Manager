<?php
	include 'httpRequest.php';

	if( isset( $_GET['url'] ) )
	{
		$url = $_GET['url'];
		$id = '';
		$separator = 'v=';
		
		if(strpos($url, 'youtube'))
		{
			$id = substr($url, strpos($url, $separator) + strlen($separator));
		}

		$url = "http://youtube.com/get_video_info?video_id=$id";		

		$request = new HTTPRequest();
		$request->open('GET',$url);
		//$request->setRequestHeaders(['Referer' => 'youtube.com']);
		$request->send();

		$output = $request->getResponseBody();
		//var_dump($request->getRequestHeaders());
		parse_str($output, $parseOutput);

		if($parseOutput['status'] != 'ok')
		{
			header('Content-Type: application/json');
			echo "{";
			echo "\"error\":\"Rstricted Access To The Video Specified\"";
			echo "}";
			exit;
		}
		
		$author = $parseOutput['author'];
		
		$duration = $parseOutput['length_seconds'];
		$duration = timeTo($duration);

		$thumbnail = '';
		if(isset($parseOutput['iurlmaxres']))
		{
			$thumbnail = $parseOutput['iurlmaxres'];
		}
		elseif(isset($parseOutput['iurl']))
		{
			$thumbnail = $parseOutput['iurl'];
		}
		elseif(isset($parseOutput['iurlhq']))
		{
			$thumbnail = $parseOutput['iurlhq'];
		}
		elseif(isset($parseOutput['iurlsd']))
		{
			$thumbnail = $parseOutput['iurlsd'];
		}
		elseif(isset($parseOutput['iurlmq']))
		{
			$thumbnail = $parseOutput['iurlmq'];
		}

		$resTags = $parseOutput['fmt_list'];
		$resTags = explode(',', $resTags);
		
		$assocTag = [];
		foreach ($resTags as $key => $value)
		{
			$parts = explode('/', $value);
			$assocTag[$parts[0]] = substr($parts[1],strpos($parts[1], 'x') + 1).'p';
		}
		
		$title = $parseOutput['title'];

		$url = $parseOutput['url_encoded_fmt_stream_map'];
		$url = explode(',', $url);
		
		foreach ($url as $key => $value) 
		{
			$array = [];
			preg_match('#itag=([^&]*)#', $value, $matches);
			$itag = $matches[1];
			
			$array['quality'] = $assocTag[$itag];
			
			preg_match('#type=([^&]*)#', $value, $matches);
			$type = $matches[1];
			$type = explode(';', urldecode($type));
			$array['type'] = getFileType($type[0]);

			preg_match('#url=([^&]*)#', $value, $matches);
			$location = $matches[1];
			$array['location'] = urldecode($location);

			$array['location'] .= "&title=$title";

			$url[$key] = $array;
		}
		
		$output =	"<div class=\"head afterClear\">
						<div class=\"thumb left\"><img class=\"left\" src=\"$thumbnail\"></div>
						<div class=\"detail left\">
							<h3 class=\"vTitle\">$title</h3>
							<h6 class=\"author\">$author</h6>
							<h6 class=\"duration\">$duration</h6>
						</div>
					</div>";

		$output .= "<div class=\"yFiles afterClear\">";
		//$var_dump[$_SERVER];
		//include 'httpRequest.php';

		foreach ($url as $key => $value) 
		{
			$type = $value['type'];
			$quality = $value['quality'];
			$location = $value['location'];
			$request = new HTTPRequest();
			$currentPath = $_SERVER['REQUEST_URI'];
			$currentPath = parse_url($currentPath)['path'];
			$requestUri =  substr($currentPath, 0,strrpos($currentPath, '/'));
			//var_dump($requestUri);
			$requestUri = "http://".$_SERVER['HTTP_HOST']."$requestUri/download.php?type=head&url=$location";
			//var_dump($requestUri);
			//echo "$requestUri";
			$request->open('GET',$requestUri);
			$request->send();
			$response = $request->getResponseBody();
			$size = 'Unknown';
			if(strpos($response, 'size') >= 0)
			{
				//echo "$response";
				$size = json_decode($response)->size;
			}
			

			$html =	"<div class=\"eachYFiles left\" data-url=\"$location\">
						<div class=\"ext left\"><img class=\"left\" src=\"image/fileicon/$type.png\"></div>
						<div class=\"meta left\">
							<p class=\"format\">$type Format</p>
							<p class=\"quality\">$quality</p>
							<p class=\"size\">$size</p>
							<button class=\"yFilesDownload\">Download</button>
						</div>
					</div>";
			$output .= $html;
		}

		$output .= "</div>";

		echo "$output";
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

	function getFileType( $mime )
	{
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

?>