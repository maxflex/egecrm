<?php if (!$this->_custom_panel) { ?>
	</div>
	</div>
<?php } ?>
  	</div>
  </div>
  <script src="js/vendor/ga.js"></script>
  <script>
	listenToLogout(<?= User::fromSession()->id ?>)
  </script>
 </body>
</html>