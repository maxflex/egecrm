<!DOCTYPE html>
<html>
  <head>
  	<meta charset="utf-8"> 
    <title><?= $this->_html_title ?></title>
    <link href="css/bootstrap.css?ver=<?= settings()->version ?>" rel="stylesheet">
    <link href="css/animate.css?ver=<?= settings()->version ?>" rel="stylesheet">
    <link href="css/style.css?ver=<?= settings()->version ?>" rel="stylesheet">
	<?= $this->_css_additional ?>
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/angular.js"></script>
	<script type="text/javascript" src="js/angular-animate.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/bootbox.js"></script>
<!-- 	<base href="http://website.ru/"> -->
    <?= $this->_js_additional ?>
  </head>
  <body>