<div class="navbar navbar-inverse navbar-fixed-top">
<div class="navbar-inner">
  <div class="container" style="width: 80%;">
    <a class="brand" href="#" style="padding: 0 10px 0 0"><img src="img/logo/ratie_longshadow_text.png" height="50" width="159"></a>
	<div class="top-loader">
		<img src="img/loader/spinner.gif">
	</div>
    <div class="nav-collapse">
	<ul class="nav pull-right">
	<?php
		/************ МЕНЮ ДЛЯ ЗАЛОГИНЕННЫХ ************/
		
		// Выводим пункт меню «Уведомления» только если пользователь залогинен
		if (!empty($_SESSION["user"])) {
			// Получаем пользователя
			$User = User::fromSession();
	?>
						
			<!-- ПУНКТ МЕНЮ «ПОЛЬЗОВАТЕЛЬ» -->
			<li <?=(menuActive("user", null, array("user" => $User->login)))?> >
        		<a id="menu-stats" href="<?= $User->login ?>"><span class="glyphicon glyphicon-align-left"></span></a>
        	 </li>
        	<!-- КОНЕЦ МЕНЮ «ПОЛЬЗОВАТЕЛЬ» -->
			
			<!-- ПУНКТ МЕНЮ «НОВОСТИ/ПРОФИЛЬ» -->
			<?php
				echo "<li ".menuActive("profile", null, array(), array("action" => "edit")).">";
				// Если есть новости, выводим
				$NewsCount = $User->newsCount();
				
				if ($NewsCount) {
					// Если кол-во новостей меньше 10, то циферка больше шрифтом
					echo '<span class="count '.($NewsCount < 10 ? 'd1' : '').'">'.$NewsCount.'</span>';
				}
				
			// Отображаем сам пункт меню
			?>
        	<a href="profile">
        		<!-- <span class="flaticon-church2"></span> -->
        		<span class="glyphicon glyphicon-user"></span>
        	</a>
			</li>
			<!-- КОНЕЦ МЕНЮ «НОВОСТИ/ПОЛЬЗОВАТЕЛЬ» -->


			<!-- ПУНКТ МЕНЮ «СООБЩЕНИЯ» -->
			<?php
				echo "<li ".menuActive("user", null, array("comment" => 0, "user" => ""), array()).">";
				// Если есть новости, выводим
				$NewsCount = $User->newMessagesCount();
				
				if ($NewsCount) {
					// Если кол-во новостей меньше 10, то циферка больше шрифтом
					echo '<span class="count '.($NewsCount < 10 ? 'd1' : '').'">'.$NewsCount.'</span>';
				}
				
			// Отображаем сам пункт меню
			?>
        	<a href="messages">
        		<!-- <span class="flaticon-mail"></span> -->
				<span class="glyphicon glyphicon-comment"></span>
        	</a>
			</li>
        	<!-- КОНЕЦ МЕНЮ «СООБЩЕНИЯ» -->

			
			<!-- ПУНКТ МЕНЮ «НАСТРОЙКИ» -->
			<li <?=(menuActive("profile", "edit"))?> >
        		<a href="profile/edit"><span class="glyphicon glyphicon-th"></span></a>
        	 </li>
        	<!-- КОНЕЦ МЕНЮ «НАСТРОЙКИ» -->
        	
        	<!-- ПУНКТ МЕНЮ «ВЫХОД» -->
        	<li>
        		<a href="logout"><span class="glyphicon glyphicon-log-out"></span></a>
        	</li>
        	<!-- КОНЕЦ МЕНЮ «ВЫХОД» -->
	<?php
		} else {
			/************ МЕНЮ ДЛЯ НЕ ЗАЛОГИНЕННЫХ ************/
			// Иначе пункты меню для незалогененных
			?>
			<li>
        		<a href="/#login"><span class="glyphicon glyphicon-log-in inline"></span>Вход</a>
        	</li>
			<?php
		}
	?>
      </ul>
	</div><!-- /.nav-collapse -->
  </div>
</div><!-- /navbar-inner -->
</div>  

<!-- БЭКГРАУНД -->
<div class="bg-blue" height="100%" width="100%"></div>