<?php
	
	/*
	 *	HTTPRequest class
	 *	Any request can be made by this class
	 *	
	 *	First create HTTPRequest object
	 *	
	 *	Then call its open() method with 'method' and 'url' as parameters
	 *	
	 *	Call setRequestHeaders() method to set 'Range' or 'Referer' or 'User-Agent' header
	 *	
	 *	To get Response Headers call getResponseHeaders() method
	 *	
	 *	To save output to file call saveTo() method with a file resource opened to 'write'
	 *	mode else default file is used
	 *	
	 *	To get the most recent error call anytime to getError() method
	 *	
	 *	To get file extension call getFileExtension() method after open() method had been called
	 *	
	 *	Call attachProgressFunction() with a function handler to monitor progress of download
	 *	
	 *	Call send() method to send the request
	 *	
	 *	To get Response Body call getResponseBody() method , it will be empty string if saveTo() method
	 *	had been called already
     *
	 *	To get Response Code call getResponseCode() method
     *
	 */

	class HTTPRequest
	{
		private $requestUrl_ ;
		private $requestMethod_;
		private $requestHeaders_;
		private $channel_;
		private $allowedRequestMethod = [ 'get' => 1, 'post' => 1, 'head' => 1 ];
		private $responseBody_;
		private $error_;
		private $responseHeaders_;
		private $fileExtension_;
		private $outputToFile_;
		private $defaultOutputFile_;		
		private $timeout_;
		private $maxRedirect_;
		private $fileHandle_;
		private $info_;

		function __construct()
		{
			$this->channel_ 		= 	curl_init();
			$this->requestUrl_ 		=
			$this->requestMethod_ 	=
			$this->responseBody_ 	=
			$this->error_ 			=
			$this->responseHeaders_ =
			$this->requestHeaders_ 	=
			$this->defaultOutputFile_ =
			$this->fileExtension_	=	'';
			$this->outputToFile_	= 	false;
			$this->timeout_ 		=	18000;
			$this->maxRedirect_ 	=	5;
			$this->info_ 			=	0;
			
			/* set default action */

			// to send the Referer header on Redirection
			curl_setopt( $this->channel_, CURLOPT_AUTOREFERER, true );
			
			// not to display the output
			curl_setopt( $this->channel_, CURLOPT_RETURNTRANSFER, true );

			// does not verify the SSL certificate of server
			curl_setopt( $this->channel_, CURLOPT_SSL_VERIFYPEER, false );

			// to get the Last Modified time
			curl_setopt( $this->channel_, CURLOPT_FILETIME, true );

			// to follow any redirection
			curl_setopt( $this->channel_, CURLOPT_FOLLOWLOCATION, true );

			// max number of redirects
			curl_setopt( $this->channel_, CURLOPT_MAXREDIRS, $this->maxRedirect_ );
			
			// set connection timeout
			curl_setopt( $this->channel_, CURLOPT_CONNECTTIMEOUT, $this->timeout_ );

			// not to display progress in command line
			curl_setopt( $this->channel_, CURLOPT_NOPROGRESS, false );

			// to track the request headers
			curl_setopt( $this->channel_, CURLINFO_HEADER_OUT, true );

			// Accept - Encoding header ( accepting all encoding type )
			curl_setopt( $this->channel_, CURLOPT_ENCODING, "" );

			// User - Agent header ( as received by the client browser )
			curl_setopt( $this->channel_, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
		}


		private function setError_()
 		{
 			// if a new error occured
 			if( !( curl_error( $this->channel_ ) === '' ) )
 			{
 				return $this->error_ = curl_error( $this->channel_ );
 			}
 		}


 		private function populateRequestHeaders_()
 		{
 			$this->info_ = curl_getinfo( $this->channel_ );

 			$headers = curl_getinfo( $this->channel_ )['request_header'];
 			
 			$headersDelimiters = "\r\n";
 			$headers = explode( $headersDelimiters, $headers );
 			
 			$headersCount = count( $headers );
 			$firstPropertyName = 'main'; 
 			
 			for( $i = 0; $i < $headersCount - 2 /* last two are empty line*/; $i++ )
 			{
 				$parts = explode( ": ", $headers[$i] );
 				if( $i === 0 )
 				{
 					$parts[1] = $parts[0];
 					$parts[0] = $firstPropertyName;
 				}
 				$this->requestHeaders_[$parts[0]] = $parts[1];
 			}
 		}


 		private function setResponseHeader_()
 		{
 			if( $this->requestMethod_ != "head" )
 			{
 				$infoOfTransfer = curl_getinfo( $this->channel_ );
			
				$this->responseHeaders_['Content-Type'] 	= 	$infoOfTransfer['content_type'];
				$this->responseHeaders_['Content-Length']	= 	$infoOfTransfer['download_content_length'];
				$this->responseHeaders_['Response-Code']	=	$infoOfTransfer['http_code'];
				$this->responseHeaders_['Last-Modified']	=	$infoOfTransfer['filetime'];
 			}
 			else
 			{
 				$headers = explode("\r\n", $this->responseBody_ );
 				$totalHeaders = count( $headers );
 				$this->responseHeaders_['Response-Code']	=	curl_getinfo( $this->channel_ )['http_code'];
 				for( $i = 0; $i < $totalHeaders - 2 /* last two empty lines*/; $i++ )
 				{
 					$parts = explode( ": ", $headers[$i] );
 					if( $i === 0 )
 					{
 						$parts[1] = $parts[0];
 						$parts[0] = "main";
 					}
 					$this->responseHeaders_[$parts[0]] = $parts[1];
 				}
 			}
 			
 		}


		public function open( $method, $url )
		{
			$method = strtolower( $method );
			
			if( !isset( $this->allowedRequestMethod[$method] ) )
			{
				// invalid method
				$this->error_ =  "The requested method is not supported";
				return $this->error_;
			}
			
			//if( preg_match( '#^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$#', $url ) === 0 )
			//{
				// invalid url ( does not support localhost url )
			//	$this->error_ = 'The requested url is not valid';
			//	return;
			//}

			$this->requestMethod_ 	= $method;
			$this->requestUrl_ 		= $url;

			// set the request url
			curl_setopt( $this->channel_, CURLOPT_URL, $this->requestUrl_ );
						
			// setting up the request method
			switch ( $this->requestMethod_ ) 
			{
				case 'get' :	curl_setopt( $this->channel_, CURLOPT_HTTPGET, true );
								break;

				case 'post':	curl_setopt( $this->channel_, CURLOPT_POST, true );
								break;

				case 'head':	curl_setopt( $this->channel_, CURLOPT_NOBODY, true );
								// to get all response headers
								curl_setopt( $this->channel_, CURLOPT_HEADER, true );
								break;	
			}

			// only when url contains the extension
			//$this->setFileExtension_();
			
			return $this->setError_();
			
		}
		

		public function getFileExtension()
		{
			return $this->fileExtension_;
		}


		public function getRequestHeaders()
		{
			return $this->requestHeaders_;
		}


		public function getInfo()
		{
			return $this->info_;
		}


		public function setRequestHeaders( $headersArray )
		{
			// for setting Range, Referer, Accept-Encoding headers

			if( isset( $headersArray['Range'] ) )
			{
				curl_setopt( $this->channel_, CURLOPT_RANGE, $headersArray['Range'] );
			}
			
			if( isset( $headersArray['Referer'] ) )
			{
				curl_setopt($this->channel_, CURLOPT_REFERER, $headersArray['Referer'] );
			}
			
			if( isset( $headersArray['Accept-Encoding'] ) )
			{
				curl_setopt( $this->channel_, CURLOPT_ENCODING, $headersArray['Accept-Encoding'] );
			}

			// seeting error_ property
			return $this->setError_();

		}

		
		public function setFileExtension( $filename = "" )
		{
			// query string is not present
			if( $filename != "" )
			{
				$this->defaultOutputFile_ = $filename;

				// taking string after the last . in name
				$this->fileExtension_ = substr( $filename, strrpos($filename, '.') );
			}
			elseif( strpos( basename( $this->requestUrl_ ), '?') === false )
			{
				$lastPathPart = basename( $this->requestUrl_ );
				// no query string
				// this filename will be used if saveTo method is called with no resource handle
				$this->defaultOutputFile_ = rawurldecode( $lastPathPart );

				// taking string after the last . in name
				$this->fileExtension_ = substr( $lastPathPart, strrpos($lastPathPart, '.') );
			}// from Content-Disposition header
			elseif( $this->requestMethod_ === "head" && isset( $this->getResponseHeaders()['Content-Disposition']) )
			{
				$header = $this->getResponseHeaders()['Content-Disposition'];
				$regex = '#([\'"])([^\'"]*)\1#';
				
				preg_match( $regex, $header , $matches);
				
				$this->defaultOutputFile_ = $matches[2];
				$this->fileExtension_ = substr( $matches[2], strrpos($matches[2], '.') );
			}// from mime type list
			else
			{
				// only setup extension no filename

				$lines = file( "mimeTypeExtension.txt", FILE_IGNORE_NEW_LINES );
				$totalLines = count( $lines );
				$contentType = $this->getContentType();
				for( $i = 0; $i < $totalLines; $i++ )
				{
					$parts = explode( "\t", $lines[$i] );
					// parts[0] - Description
					// parts[1] - Content-Type
					// parts[2] - Extension
					// parts[3] - Description
					if( $parts[1] === $contentType )
					{
						$this->fileExtension_ = $parts[2];
						break;
					}
				}
			}
		}


		public function saveTo( $file = ''/* resource handle */ )
		{
			$this->outputToFile_ = true;

			if( $file === '' )
			{
				// file name not given
				// open handle for the new file
				// close it when done
				$file = fopen( $this->defaultOutputFile_, 'w' );
			}

			if( !is_resource( $file ) )
			{
				$this->error_ = 'Not a valid file handle';
				return $this->error_;
			}

			// for closing the resource when done
			$this->fileHandle_ = &$file;

			curl_setopt( $this->channel_, CURLOPT_FILE, $file );

			return $this->setError_();
		}


		public function send( $body = '' )
		{
			// message body of the request in POST method
			if( $this->requestMethod_ === 'post' )
			{
				curl_setopt( $this->channel_, CURLOPT_POSTFIELDS, $body );
			}

			if( !$this->outputToFile_ )
			{
				$this->responseBody_ = curl_exec( $this->channel_ );
			}
			else
			{
				curl_exec( $this->channel_ );
			}

			// seeting error_ property
			if( $this->setError_() )
			{
				return $this->setError_();
			}
			
			// updating requestHeaders_ property
			$this->populateRequestHeaders_();

			$this->setResponseHeader_();
			if( $this->getFileName() === "" )
			{
				$this->setFileExtension();
			}
			
			/*
				Freeing up the resources
			*/
			curl_close( $this->channel_ );
			if( $this->outputToFile_ )
			{
				fclose( $this->fileHandle_ );
			}

 		}
 		
 		
 		public function getFileName()
 		{
 			return $this->defaultOutputFile_;
 		}


 		public function getResponseBody()
 		{
 			return $this->responseBody_;
 		}

 		
 		public function getResponseCode()
 		{
 			return $this->responseHeaders_['Response-Code'];
 		}


 		public function getResponseHeaders()
 		{
 			return $this->responseHeaders_;
 		}


 		public function getContentLength()
 		{
 			return $this->responseHeaders_['Content-Length'];
 		}


 		public function getContentType()
 		{
 			return $this->responseHeaders_['Content-Type'];
 		}


 		public function attachProgressFunction( $handler )
 		{
 			curl_setopt( $this->channel_, CURLOPT_PROGRESSFUNCTION, $handler );
 		}

 		public function getError()
 		{
 			return $this->error_;
 		}
	}
?>