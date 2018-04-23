<div ng-show="image_loaded">
	<center autocomplete="off" id='center' class="animated fadeIn" style='animation-duration: 1.5s'>
	  <h3 class="text-danger text-center">cсылка больше не действительна</h3>
	</center>
  <?php if(@$wallpaper->user_id) :?>
  <span class="wallpaper-by animated fadeInRight">by <?= $wallpaper->user->login ?></span>
<?php endif ?>
</div>
<div ng-show="!image_loaded">
  <img src="img/svg/tail-spin.svg" />
</div>
