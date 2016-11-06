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

<body>
<div id="app"></div>
<script src="js/jquery-1.11.3.min.js"></script>
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
<script src="js/volumio.api.js"></script>
<script src="js/main.js"></script>
<script src="js/volumio.utils.js"></script>
<script src="js/volumio.lazyloader.js"></script>
<script src="js/volumio.library.js"></script>
<script src="js/volumio.playback.js"></script>
<script src="js/application.js"></script>
<!--<script src="js/volumio.settings.js"></script>-->
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