<?php
	include_once 'php/firstRun.php';
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>KINK Download Manager</title>
		<link rel="shortcut icon" type="image/png" href="image/favicon.png">
		<link rel="stylesheet" type="text/css" href="css/removeDefault.css">
		<link rel="stylesheet" type="text/css" href="css/style.css">
	</head>

	<body>
		<div id="container">
			<div id="middleBody">
				<div id="top"></div>
				<div id="header">
					<div class="left logo"><img class="left" src="image/logo.png"></div>
					<div class="left logoName">kink</div>
					<ul class="right menuOptions">
						<li><span class="menu bigMenu" data-menu="add">Add</span></li>
						<li><span class="menu deactive resume">Resume</span></li>
						<li><span class="menu deactive stop">Stop</span></li>
						<li><span class="menu deactive delete">Delete</span></li>						
						<li><span class="menu bigMenu" data-menu="youtube">Youtube</span></li>
						
					</ul>
					<div class="clear"></div>
					<div class="allMenu">
						<div class="menuContainer zeroHeight">
							<div class="add subMenu">
							<form class="addForm" action="download.php" method="get">
								<div class="addInputArea afterClear">
									<input type="text" class="left urlInput textInput" name="newUrl" placeholder="Add New Url"
									 autocomplete="off" spellcheck="false" />
									<input class="left button" type="submit" value="Add" name="addurl" />
								</div>
							</form>
							<div class="loading hidden"><img src="image/loading.gif" width="35" height="35"></div>
							<div class="detail hidden afterClear">
								<div class="fileType fOpt">File Type | <span class="xDetail">mp3</span></div>
								<div class="fileSize fOpt">File Size | <span class="xDetail">5 MB</span></div>
								<div class="saveAs fOpt">Save As | <input class="xDetail" type="text" autocomplete="off" spellcheck="false" name="fileName" /></div>
								<div class="resumeCapability fOpt">Resume Support | <span class="xDetail yes">Yes</span></div>
								<button class="download button left">Download</button>
							</div>
							</div>
						</div>

						<!--<div class="menuContainer zeroHeight">
							<div class="grab subMenu">
							<form action="" method="get">
								<div class="addInputArea afterClear">
									<input type="text" class="left urlInput textInput" name="newUrl" placeholder="Add New Url"
									 autocomplete="off" spellcheck="false" />
									<input class="left button" type="submit" value="Grab" name="addurl" />
								</div>
							</form>
							<div class="grabResult">
								<table>
									<thead>
										<tr>
											<th class="c1"><input type="checkbox"></th>
											<th class="c2">Url</th>
											<th class="c3">Type</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td><input type="checkbox"></td>
											<td>cdkcsd</td>
											<td>cddcsjkd</td>
										</tr>
										<tr>
											<td><input type="checkbox"></td>
											<td>cdkcsd</td>
											<td>cddcsjkd</td>
										</tr>
										<tr>
											<td><input type="checkbox"></td>
											<td>cdkcsd</td>
											<td>cddcsjkd</td>
										</tr>
										<tr>
											<td><input type="checkbox"></td>
											<td>cdkcsd</td>
											<td>cddcsjkd</td>
										</tr>
									</tbody>
								</table>
								<button class="button">Download</button>
							</div>
							</div>
						</div>

						<div class="menuContainer zeroHeight">
							<div class="range subMenu">
							<form>
								<div class="addInputArea afterClear">
									<input class="textInput" type="text" name="range1" placeholder="Type First Url" autocomplete="off" spellcheck="false">
								</div>
								<div class="to">To</div>
								<div class="addInputArea afterClear">
									<input class="textInput" type="text" name="range2" placeholder="Type Last Url" autocomplete="off" spellcheck="false">
								</div>
								<input class="button" type="submit" value="Download">
							</form>
							</div>
						</div>-->

						<div class="menuContainer zeroHeight">
							<div class="youtube subMenu">
								<form class="youtubeForm" action="php/youtube.php" method="get">
									<div class="addInputArea afterClear">
										<input type="text" class="left urlInput textInput" name="newUrl" placeholder="Add Youtube Url"
										 autocomplete="off" spellcheck="false" />
										<input class="left button" type="submit" value="Add Video" name="addurl" />
									</div>
								</form>
								<div class="loading hidden"><img src="image/loading.gif" width="35" height="35"></div>
								<div class="videoDetail">
									
								</div>
							</div>
						</div>

					</div>
				</div>
				<div id="speedGraph" class="zeroHeight">
					<div class="graphBackground afterClear">
						<div class="left axis">
							<span class="unit">KBps</span>
							<ul>
								<li>25<span class="marker">-</span></li>
								<li>20<span class="marker">-</span></li>
								<li>15<span class="marker">-</span></li>
								<li>10<span class="marker">-</span></li>
								<li>5<span class="marker">-</span></li>
								<li>0<span class="marker">-</span></li>
							</ul>
						</div>
						<div class="left grid"><canvas id="graph" width="1120" height="211"></canvas></div>
						<div class="left xAxis">Time ( 1 second grid )</div>
					</div>
					
					<div class="stats">
						<ul class="afterClear">
							<li>Current Speed | <span class="xDetail">0 KBps</span></li>
							<li>Average Speed | <span class="xDetail">0 KBps</span></li>
							<li>Time Left | <span class="xDetail">0 sec</span></li>
							<li>Downloaded | <span class="xDetail">0 MB</span></li>
							<li>File Size | <span class="xDetail">0 MB</span></li>
						</ul>
						<div class="progress">
							<span class="info"></span>
							<div class="highlight error"></div>
						</div>
					</div>
				</div>

				<div id="mainBody" class="afterClear">
					<div id="category" class="left">
						<div class="eachCategory all active"><span class="expander">-</span>All</div>
						<div class="allExpand expand">
							<ul>
								<li class="com">Compressed</li>
								<li class="doc">Document</li>
								<li class="aud">Music</li>
								<li class="vid">Video</li>
								<li class="app">Application</li>
							</ul>
						</div>
						<div class="eachCategory completed"><span class="expander">-</span>Completed</div>
						<div class="completedExpand expand">
							<ul>
								<li class="com">Compressed</li>
								<li class="doc">Document</li>
								<li class="aud">Music</li>
								<li class="vid">Video</li>
								<li class="app">Application</li>
							</ul>
						</div>
						<div class="eachCategory notCompleted"><span class="expander">-</span>Not Completed</div>
						<div class="notCompletedExpand expand">
							<ul>
								<li class="com">Compressed</li>
								<li class="doc">Document</li>
								<li class="aud">Music</li>
								<li class="vid">Video</li>
								<li class="app">Application</li>
							</ul>
						</div>
					</div>
					<div id="files" class="left">
						<div class="tHead">
							<table cellspacing="0" cellpadding="0">
								<thead>
									<tr>
										<th class="c1">File Name<span></span></th>
										<th class="c2">File Size<span></span></th>
										<th class="c3">Status<span></span></th>
										<th class="c4">Avg Speed<span></span></th>
										<th class="c5">Added On<span></span></th>
									</tr>
								</thead>
							</table>
						</div>
						<table class="allFiles" cellpadding="0" cellspacing="0">
							<thead>
								<tr>
									<th class="c1">File Name</th>
									<th class="c2">File Size</th>
									<th class="c3">Status</th>
									<th class="c4">Avg Speed</th>
									<th class="c5">Added On</th>
								</tr>
							</thead>
							<tbody>
								
								<?php
									include_once 'php/loadFiles.php';
								?>
				
							</tbody>
						</table>
					</div>
				</div>
				<div class="contextMenu hidden">
					<ul>
						<li>Remove From List</li>
						<li>Properties</li>
					</ul>
				</div>
				<div id="footer">
					<ul><!-- for terms and condition -->
						<li><a target="_blank" href="https://github.com/Kapilks/Kink-Download-Manager/blob/master/README.md">Help</a></li>
						<li><a target="_blank" href="https://github.com/Kapilks/Kink-Download-Manager">Improve</a></li>
					</ul>
					<p class="right me">Developed By <span class="arrow">|</span> 
						<a class="web" title="www.kapilkumarsingh.in" href="https://github.com/Kapilks" target="_blank">Kapil Kumar Singh</a>
					</p>
				</div>
			</div>
		</div>

		<div class="lightbox hidden">
			<div class="error hidden">
				<span class="close"></span>
				<div class="head afterClear">
					<div class="icon left"></div>
					<div class="name left"><p>Error</p></div>
				</div>
				<p class="text">Server Sent 404 Code Request Forbidden</p>
			</div>
			<div class="complete hidden">
				<span class="close"></span>
				<div class="head afterClear">
					<div class="icon left"></div>
					<div class="name left"><p>Download Complete</p></div>
				</div>
				<p class="text">Download Complete For Song.mp3</p>
			</div>
			<div class="properties hidden">
			</div>
		</div>

		<script type="text/javascript" src="js/drawGraph.js"></script>
		<script type="text/javascript" src="js/interective.js"></script>
		<script type="text/javascript" src="js/main.js"></script>
		<script type="text/javascript" src="js/youtubeVid.js"></script>
	</body>
</html>