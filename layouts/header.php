<!DOCTYPE html>
<html>
  <head>
  	<meta charset="utf-8"> 
    <title><?= $this->_html_title ?></title>
    <?php
	    // Дебаг
	    if (LOCAL_DEVELOPMENT) {
		    echo '<base href="http://localhost:8080/egecrm/">';
	    } else {
		    echo '<base href="/egecrm/">';
	    }
	?>
<!--     <link href="css/jquery.datetimepicker.css" rel="stylesheet"> -->
    <link href="css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="css/jquery.timepicker.css" rel="stylesheet">
	<link rel="stylesheet" href="css/hint.css"></link>
    <link href="css/bootstrap.css?ver=<?= settings()->version ?>" rel="stylesheet">
    <link href="css/animate.css?ver=<?= settings()->version ?>" rel="stylesheet">
	<link href="css/nprogress.css" rel="stylesheet">
    <link href="css/style.css?ver=<?= settings()->version ?>" rel="stylesheet">
    <link href="css/ng-showhide.css?ver=<?= settings()->version ?>" rel="stylesheet">
	<link href="css/ios7switch.css" rel="stylesheet">
	<?= $this->_css_additional ?>
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery.cookie.js"></script>
	<script type='text/javascript' src='js/comments-app-global.js?ver=<?= settings()->version ?>'></script>
	<script type="text/javascript" src="js/metro_data.js"></script>
	<script type="text/javascript" src="js/floatlabel.js"></script>
	<script type="text/javascript" src="js/nprogress.js"></script>
	<script type="text/javascript" src="js/mask.js"></script>
	<script type="text/javascript" src="js/inputmask.js"></script>
<!-- 	<script src="https://rawgit.com/RobinHerbots/jquery.inputmask/3.x/dist/jquery.inputmask.bundle.js" type="text/javascript"></script> -->
	<script type="text/javascript" src="js/angular.js"></script>
	<script type="text/javascript" src="js/angular-locale-ru.js"></script>
	<script type="text/javascript" src="js/angular-animate.js"></script>
	<script type="text/javascript" src="js/ngmap.min.js"></script>
	<script type="text/javascript" src="js/name.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/bootbox.js"></script>
	<script type="text/javascript" src="js/notify.js"></script>
	<script type="text/javascript" src="js/moment.min.js"></script>
	<script type="text/javascript" src="js/bootstrap-datepicker.min.js"></script>
	<script type="text/javascript" src="js/user-color-control.js"></script>
<!-- 	<script type="text/javascript" src="js/jquery.datetimepicker.js"></script> -->
	<script type="text/javascript" src="js/jquery.timepicker.js"></script>
	<script type="text/javascript" src="js/engine.js?ver=<?= settings()->version ?>"></script>

    <?= $this->_js_additional ?>
  </head>
  <body>
	  <div class="lightbox"></div>