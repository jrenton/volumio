<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Volumio - Music Player</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0 user-scalable=no">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/flat-ui.css" rel="stylesheet">
    <link href="css/bootstrap-select.css" rel="stylesheet">
	<link href="css/bootstrap-fileupload.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
	<!--[if lte IE 7]>
		<link href="css/font-awesome-ie7.min.css" rel="stylesheet">
	<![endif]-->
	<link href="css/jquery.countdown.css" rel="stylesheet">
	<!--<link rel="stylesheet" href="css/jquery.mobile.custom.structure.min.css">-->
	<link href="css/jquery.pnotify.default.css" rel="stylesheet">
	<link rel="stylesheet" href="css/panels.css">
    <link rel="shortcut icon" href="images/favicon.png" type="image/png">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
    <![endif]-->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="apple-touch-icon" href="/images/apple-touch-icon.png">
	<style type="text/css" id="dynamicCss">
	</style>
</head>

<body id="app">

<div id="menu-top" class="ui-header ui-bar-f ui-header-fixed slidedown" data-position="fixed" data-role="header" role="banner">
	<div class="dropdown">
		<a class="dropdown-toggle" id="menu-settings" role="button" data-toggle="dropdown" data-target="#" href="index.php"><i class="fa fa-th-list dx"></i></a>
		<ul class="dropdown-menu" role="menu" aria-labelledby="menu-settings">
			<li><a href="index.php"><i class="fa fa-play sx"></i> Main</a></li>
			<li><a href="sources.php"><i class="fa fa-folder-open sx"></i> Library</a></li>
			<li><a href="mpd-config.php"><i class="fa fa-cogs sx"></i> Playback</a></li>
			<li><a href="net-config.php"><i class="fa fa-sitemap sx"></i> Network</a></li>
			<li><a href="settings.php"><i class="fa fa-wrench sx"></i> System</a></li>
			<li><a href="credits.php"><i class="fa fa-trophy sx"></i> Credits</a></li>
			<li><a href="#poweroff-modal" data-toggle="modal"><i class="fa fa-power-off sx"></i> Turn off</a></li>
		</ul>
	</div>
	<div id="db-back">
		<a class="back-btn">
			<i class="fa fa-chevron-left sx"></i>
		</a>
		<span id="webradio-add">
			<a href="#webradio-modal" data-toggle="modal" title="Add New WebRadio">
				<button class="btn">
					<i class="fa fa-plus"></i>
					<em id="webradio-add-text"></em>
				</button>
			</a>
		</span>
	</div>
	<form id="db-search" action="javascript:getDB('search', '', 'file');">
		<div class="input-append">
			<input class="span2" id="db-search-keyword" type="text" value="" placeholder="Search">
			<button class="btn" type="submit"><i class="fa fa-search"></i></button>
		</div>
	</form>
</div>
<div id="menu-bottom" class="ui-footer ui-bar-f ui-footer-fixed slidedown" data-position="fixed" data-role="footer"  role="banner">
	<ul>
		<li id="open-panel-sx">
			<a v-link="{ path: 'browse' }" class="open-panel-sx">
				<i class="fa fa-music sx"></i> Browse
			</a>
		</li>
		<li id="open-panel-lib">
			<a v-link="{ path: 'library' }" class="open-panel-lib">
				<i class="fa fa-columns sx"></i> Library
			</a>
		</li>
		<li id="open-playback">
			<a v-link="{ path: 'playback' }" class="close-panels">
				<i class="fa fa-play sx"></i> Playback
			</a>
		</li>
		<li id="open-panel-dx">
			<a v-link="{ path: 'playlist' }" class="open-panel-dx">
				<i class="fa fa-list sx"></i> Playlist
			</a>
		</li>
	</ul>
</div>
<div id="main-container">
	<router-view></router-view>
</div>
<form class="form-horizontal" action="settings.php" method="post">
	<div id="poweroff-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="poweroff-modal-label" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="poweroff-modal-label">Turn off the player</h3>
		</div>
		<div class="modal-body">
			<button id="syscmd-poweroff" name="syscmd" value="poweroff" class="btn btn-primary btn-large btn-block"><i class="fa fa-power-off sx"></i> Power off</button>
			<button id="syscmd-reboot" name="syscmd" value="reboot" class="btn btn-primary btn-large btn-block"><i class="fa fa-refresh sx"></i> Reboot</button>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		</div>
	</div>
</form>
<form class="form-horizontal" action="" method="post">
	<div id="webradio-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="webradio-modal-label" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="webradio-modal-label">Add New WebRadio</h3>
		</div>
		<div class="modal-body">
			<form action="settings.php" method="POST">	
		<input name="radio-name" type="text" placeholder="WebRadio Name" />
		<input name="radio-url" type="text" placeholder="WebRadio URL"/>
		</form>
		</div>
		<div class="modal-footer">
			<div class="form-actions">
            <button class="btn btn-large" data-dismiss="modal" aria-hidden="true">Cancel</button>
            <button type="submit" class="btn btn-primary btn-large" name="save" value="save">Add</button>
        </div>
		</div>
	</div>
</form>
<form class="form-horizontal" action="" method="post">
	<div id="update-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="update-modal-label" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="update-modal-label">Add New WebRadio</h3>
		</div>
		<div class="modal-body">
			<form action="updates/check_updates.php" method="POST">	
		Cose varie da dire
		</form>
		</div>
		<div class="modal-footer">
			<div class="form-actions">
            <button class="btn btn-large" data-dismiss="modal" aria-hidden="true">Cancel</button>
            <button type="submit" class="btn btn-primary btn-large" name="save" value="save">Add</button>
        </div>
		</div>
	</div>
</form>

<!-- loader -->
<div id="loader">
	<div id="loaderbg"></div>
	<div id="loadercontent">
		<i class="fa fa-refresh fa-spin"></i>
		connecting...
	</div>
</div>
<script src="js/jquery-1.8.2.min.js"></script>
<script src="js/jquery-ui-1.11.1.custom.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-select.min.js"></script>
<script src="js/notify.js"></script>
<script src="js/jquery.countdown.js"></script>
<script src="js/jquery.scrollTo.min.js"></script>
<script src="js/jquery.knob.js"></script>
<script src="js/bootstrap-contextmenu.js"></script>
<script src="js/jquery.pnotify.min.js"></script>
<script src="js/custom_checkbox_and_radio.js"></script>
<script src="js/custom_radio.js"></script>
<script src="js/jquery.tagsinput.js"></script>
<script src="js/jquery.placeholder.js"></script>
<script src="js/parsley.min.js"></script>
<script src="js/i18n/_messages.en.js" type="text/javascript"></script>
<script src="js/jquery.pnotify.min.js"></script>
<script src="js/bootstrap-fileupload.js"></script>
<script src="js/main.js"></script>
<script src="js/volumio.utils.js"></script>
<script src="js/volumio.api.js"></script>
<script src="js/volumio.lazyloader.js"></script>
<script src="js/volumio.library.js"></script>
<script src="js/volumio.playback.js"></script>
<script src="js/application.js"></script>
<script src="js/volumio.settings.js"></script>
<!--<script src="js/jquery.dropkick-1.0.0.js"></script>-->

<?php
//WebRadio Add Dialog
// if(isset($_POST['radio-name']) && isset($_POST['radio-url'])) {
//     $url = $_POST['radio-url'];
// 	$name = $_POST['radio-name'];
//     $ret = file_put_contents('/var/lib/mpd/music/WEBRADIO/'.$name.'.pls', $url);
// 	session_start();
// 	sendMpdCommand($mpd,'update WEBRADIO');
// 	// set UI notify
// 	$_SESSION['notify']['msg'] = 'New WebRadio Added';
// 	// unlock session file
// 	playerSession('unlock');
// }
?>
<script type="text/javascript">

</script>

<!--[if lt IE 8]>
<script src="js/icon-font-ie7.js"></script>
<script src="js/icon-font-ie7-24.js"></script>
<![endif]-->
<?php
// write backend response on UI Notify popup
// if (isset($_SESSION['notify']) && $_SESSION['notify'] != '') {
// 	sleep(1);
// 	ui_notify($_SESSION['notify']);
// 	session_start();
// 	$_SESSION['notify'] = '';
// 	session_write_close();
// }
?>

</body>
</html>