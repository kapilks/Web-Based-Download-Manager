<?php
	header('Content-Type: text/event-stream');
	header('Cache-Control: no-cache');

	$i = 0;

	while( true )
	{
		echo "event: rec\n";
		echo "data: {\n";
		echo "data: \"x\":1,\n";
		echo "data: \"y\":2\n";
		echo "data: }\n\n";

		ob_flush();
		flush();

		sleep(2);
		$i++;
		if( $i == 10 )
			break;
	}
	

	echo "event:done\n";
	echo "data:Complete\n\n";

	ob_flush();
	flush();

	echo "event: end\n";
	echo "data: finish\n\n";

	ob_flush();
	flush();

	$file = fopen('abc.txt','w');
	fclose($file);
?>