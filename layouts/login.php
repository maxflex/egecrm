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
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" href="favicon.png" />
	<link href="css/app.css?ver=<?= settings()->version ?>" rel="stylesheet">
	<?= $this->_css_additional ?>
	<script type="text/javascript" src="js/vendor.js?ver=<?= settings()->version ?>"></script>
	<script type="text/javascript" src="js/engine.js?ver=<?= settings()->version ?>"></script>
	<script type="text/javascript" src="js/functions.js?ver=<?= settings()->version ?>"></script>
	<?= $this->_js_additional ?>
	<script type="text/javascript" src="js/app.js"></script>
	<script type="text/javascript" src="js/assets.js"></script>
	<script src='https://www.google.com/recaptcha/api.js?hl=ru'></script>
	<style>
	    .grecaptcha-badge {
	        visibility: hidden;
	    }
	</style>
  </head>
  <body class="content animated fadeIn"  ng-app="Login" ng-controller="LoginCtrl" ng-init="<?= $ang_init_data ?>">
