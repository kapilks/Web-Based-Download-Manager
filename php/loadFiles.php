<?php
	include 'connection.php';
	date_default_timezone_set( 'Asia/Kolkata' );

	$tableName = "files";

	// change query
	$query = "SELECT * FROM $tableName";
	
	$result = $db->query( $query );

	$output = '';

	while( $content = $result->fetch_assoc() )
	{
		$filename 	= $content['filename'];
		$id 		= $content['file_id'];
		$type 		= $content['type'];
		$complete 	= $content['complete'];
		$avgSpeed 	= $content['avg_speed'];
		$addedOn 	= $content['added_on'];
		$size 		= $content['filesize'];
		$size = (int)$size;
		$status 	= $complete == 'y' ? 'done' : 'pause';
		$date = date( "D M j Y G:i:s", strtotime( $addedOn ) );

		if( $complete == 'y' )
		{
			$percent = "100";
		}
		elseif( $complete == 'n' )
		{
			// changes as it is included in different fiels
			// when used individually use '../tmp..' else 'tmp....'
			$downloaded = 0;
			$fullFileName = "tmp/$filename.$type.kink";

			if( is_file( $fullFileName ) )
			{	
				$downloaded = filesize( $fullFileName );
			}
			if( $size > 0 )
			{
		
				$percent = sprintf( "%.2f", $downloaded * 100 / $size );
				$percent = (int)$percent;

			}
			else
			{
				$percent = "0";
			}
			
		}
		$size = byteTo( $size, 2 );

		$output .= "<tr class=\"$type\" data-status=\"$status\" data-id=\"$id\"title=\"$filename\">
						<td>$filename</td>
						<td>$size</td>
						<td>$percent%</td>
						<td>$avgSpeed</td>
						<td>$date</td>
					</tr>";
	}

	echo "$output";

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
?>