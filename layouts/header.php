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
	<link rel="shortcut icon" href="favicon.png" />
	<link href="css/app.css?ver=<?= settings()->version ?>" rel="stylesheet">
	<?= $this->_css_additional ?>
	<script type="text/javascript" src="js/vendor.js?ver=<?= settings()->version ?>"></script>
	<script type="text/javascript" src="extentions/ckeditor/ckeditor.js"></script>
	<script type="text/javascript" src="js/metro_data.js"></script>
	<script type="text/javascript" src="js/engine.js?ver=<?= settings()->version ?>"></script>
	<script type="text/javascript" src="js/functions.js?ver=<?= settings()->version ?>"></script>
	<script type="text/javascript" src="js/pusher.js?ver=<?= settings()->version ?>"></script>
	<script type="text/javascript" src="js/search.js"></script>
	<?php if ((User::fromSession()->type == Teacher::USER_TYPE || User::fromSession()->type == Student::USER_TYPE)
			&& !LOCAL_DEVELOPMENT && !User::inViewMode()) :?>
	<?php endif ?>
	<?= $this->_js_additional ?>
	<script type="text/javascript" src="js/app.js"></script>
	<script type="text/javascript" src="js/assets.js"></script>
	<script>
		listenToSession('<?= SSO_PUSHER_APP_KEY ?>', <?= User::id() ?>)
	</script>
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
