<!DOCTYPE html>
<html>
  <head>
  	<meta charset="utf-8">
    <title><?= $this->_html_title ?></title>
    <?php
	    // Дебаг
	    if (LOCAL_DEVELOPMENT) {
		    echo '<base href="' . BASE_LOCAL . BASE_ADDON . '">';
	    } else {
		    echo '<base href="/">';
	    }
	?>
<!--     <link href="css/jquery.datetimepicker.css" rel="stylesheet"> -->
	<link rel="shortcut icon" href="favicon.png" />
    <link href="css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="css/jquery.timepicker.css" rel="stylesheet">
	<link rel="stylesheet" href="css/hint.css"></link>
    <link rel="stylesheet" href="js/bower/phoneapi/dist/css/phone.css"></link>
    <link href="css/bootstrap.css?ver=<?= settings()->version ?>" rel="stylesheet">
    <link href="css/animate.css?ver=<?= settings()->version ?>" rel="stylesheet">
	<link href="css/nprogress.css" rel="stylesheet">
    <link href="css/style.css?ver=<?= settings()->version ?>" rel="stylesheet">
    <link href="css/ng-showhide.css?ver=<?= settings()->version ?>" rel="stylesheet">
	<link href="css/ios7switch.css" rel="stylesheet">
	<link href="css/ladda-themeless.css" rel="stylesheet">
	<link href="css/search.css" rel="stylesheet">

	<link rel="stylesheet" type="text/css" href="css/corner-morph.css" />
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
	<script type="text/javascript" src="extentions/ckeditor/ckeditor.js"></script>
	<script type="text/javascript" src="js/ngmap.min.js"></script>
	<script type="text/javascript" src="js/name.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/bootbox.js"></script>
	<script type="text/javascript" src="js/notify.js"></script>
	<script type="text/javascript" src="js/moment.min.js"></script>
	<script type="text/javascript" src="js/bootstrap-datepicker.min.js"></script>
	<script type="text/javascript" src="js/bootstrap-datetimepicker.js"></script>

	<script type="text/javascript" src="js/user-color-control.js"></script>
<!-- 	<script type="text/javascript" src="js/jquery.datetimepicker.js"></script> -->
	<script type="text/javascript" src="js/jquery.timepicker.js"></script>

	<script type="text/javascript" src="js/jquery.ui.widget.js"></script>
	<script type="text/javascript" src="js/jquery.iframe-transport.js"></script>
	<script type="text/javascript" src="js/jquery.fileupload.js"></script>

	<script type="text/javascript" src="js/underscore.js"></script>

	<script type="text/javascript" src="js/engine.js?ver=<?= settings()->version ?>"></script>
	<script type="text/javascript" src="js/md5.js?"></script>
	<script type="text/javascript" src="js/functions.js?ver=<?= settings()->version ?>"></script>

    <script type="text/javascript" src="//js.pusher.com/3.0/pusher.min.js"></script>
    <script type="text/javascript" src="js/pusher.js?ver=<?= settings()->version ?>"></script>
    <script type="text/javascript" src="js/bower/vue/dist/vue.js"></script>
    <script type="text/javascript" src="/js/ng-search-app.js"></script>
    <!-- <script src="js/bower/vue-resource/dist/vue-resource.js"></script> -->
	  <script type="text/javascript" src="js/bower/phoneapi/dist/js/pusher.js"></script>

	<script src="js/spin.js"></script>
	<script src="js/ladda.js"></script>

	<?php if ((User::fromSession()->type == Teacher::USER_TYPE || User::fromSession()->type == Student::USER_TYPE)
			&& !LOCAL_DEVELOPMENT && !User::inViewMode()) :?>
	<script type="text/javascript" src="js/ga.js"></script>
	<?php endif ?>

    <?= $this->_js_additional ?>
  </head>
  <body class="content">
	  <div class="lightbox"></div>

	  <div id="logout-modal" class="modal" role="dialog">
		  <div class="modal-dialog">
		    <div class="modal-content">
		      <div class="modal-header">
		        <h4 class="modal-title center">Сессия завершится через <span id="logout-seconds"></span>...</h4>
		      </div>
		      <div class="modal-footer center">
		        <button type="button" class="btn btn-primary" data-dismiss="modal" style="margin-top: 25px" onclick="continueSession()">продолжить сессию</button>
		      </div>
		    </div>
		  </div>
		</div>
