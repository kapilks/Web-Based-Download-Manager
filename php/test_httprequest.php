<?php
	include 'httpRequest.php';

	//$url = "http://www.myandroidbest.com/wp-content/uploads/2014/05/Awesome-Backgrounds.jpg";
	$url = "http://r3---sn-o3o-qxae.googlevideo.com/videoplayback?expire=1405998000&key=yt5&sparams=id%2Cip%2Cipbits%2Citag%2Cratebypass%2Csource%2Cupn%2Cexpire&id=o-AFQlviwbP11YvV5lzud_1ci2G2hIjde2tYcuvd9FxRhg&sver=3&ratebypass=yes&source=youtube&ms=au&mv=m&ip=180.149.49.234&upn=EiuWhfcSwJk&itag=18&ipbits=0&mws=yes&fexp=902408%2C924222%2C927622%2C930008%2C930816%2C934024%2C934030%2C941413%2C945306%2C946008%2C946505&mt=1405973600&signature=0F295B43ECC75433CE263ED7AFFA94EA1514D994.62F660A55249730728740BE7082A517D9BD07438&title=How%20BIG%20Is%20Google%3F";
	//$url = "http://hqscreen.com/wallpapers/l/1366x768/60/binary_geek_1366x768_59266.jpg";
	$req = new HTTPRequest();
	$req->open("HEAD",$url);
	//$req->setRequestHeaders(["Referer" => "http://fs-stg-7.filestream.me" ]);
	//$req->setRequestHeaders(["Range"=>"0-1024"]);
	if( $req->send())
	{
		var_dump($req->getError());
	}
	var_dump($req->getResponseHeaders());
	var_dump($req->getRequestHeaders());
	var_dump($req->getFileExtension());
	var_dump($req->getFileName());


?>