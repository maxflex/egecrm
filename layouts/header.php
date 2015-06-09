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
	<?= $this->_css_additional ?>
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/nprogress.js"></script>
	<script type="text/javascript" src="js/mask.js"></script>
	<script type="text/javascript" src="js/inputmask.js"></script>
	<script type="text/javascript" src="js/angular.js"></script>
	<script type="text/javascript" src="js/angular-animate.js"></script>
	<script type="text/javascript" src="js/ngmap.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/bootbox.min.js"></script>
	<script type="text/javascript" src="js/notify.js"></script>
	<script type="text/javascript" src="js/moment.min.js"></script>
	<script type="text/javascript" src="js/bootstrap-datepicker.min.js"></script>
<!-- 	<script type="text/javascript" src="js/jquery.datetimepicker.js"></script> -->
	<script type="text/javascript" src="js/jquery.timepicker.js"></script>
	<script type="text/javascript" src="js/engine.js?ver=<?= settings()->version ?>"></script>

    <?= $this->_js_additional ?>
  </head>
  <body>