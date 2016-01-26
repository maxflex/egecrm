<!-- ЛАЙТБОКС ОТПРАВКА SMS -->
<div class="lightbox-new lightbox-sms">
	<input type="hidden" id="sms-mode" value="1">
	<h4 style="text-align: center" id="sms-number">
		<span class="text-danger">Номер не установлен!</span>
	</h4>
	<div class="row">
		<div class="col-sm-12" id="sms-history">
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12" style="text-align: center">
			<div class="form-group" style="position: relative; margin-bottom: 0">
				<textarea rows="8" class="form-control" style="width: 100%" placeholder="Текст сообщения" id="sms-message"></textarea>
			<span class="pull-right" id="sms-counter" style="position: absolute; right: 16px; bottom: 7px; color: #999; background: white; z-index: 9; border-radius: 5px">
				0 СМС
			</span>
			</div>
			
			<div class="sms-template-list">
				<span onclick="smsTemplate(1)">подтверждение договоренности</span>
				<span onclick="smsTemplate(2)">нет связи с клиентом</span>
				<span onclick="smsTemplate(3)">нет связи с ожидающими и не решившими</span>
				
				<div class="sms-group-controls" style="float: right; display: none">
					<span style="margin-right: 7px; color: black; border-bottom: none">
						<input type="checkbox" onclick="ang_scope.to_students = !ang_scope.to_students; ang_scope.$apply()" checked> ученикам
					</span>
					<span style="color: black; border-bottom: none">
						<input type="checkbox" onclick="ang_scope.to_representatives = !ang_scope.to_representatives; ang_scope.$apply()"> представителям
					</span>
				</div>
			</div>
			
			<div id="sms-template-1" class="sms-template">
				Здравствуйте! Запись на курсы ЕГЭ-Центра по адресу: Мясницкая, д. 40, стр. 1, 203 каб. При себе иметь Ваш паспорт и паспорт ребенка. 8 (495) 646-85-92, <?= User::fromSession()->first_name ? User::fromSession()->first_name : "{{имя}}" ?>
			</div>
			
			<div id="sms-template-2" class="sms-template">
				Здравствуйте! Вы оставляли заявку в ЕГЭ-Центр. Не удалось до Вас дозвониться, просьба перезвонить по тел. 8 (495) 646-85-92, <?= User::fromSession()->first_name ? User::fromSession()->first_name : "{{имя}}" ?>
			</div>
			
			<div id="sms-template-3" class="sms-template">
				Имя, здравствуйте. В ЕГЭ-Центре-Филиал формируются группы и расписание. Не могу до Вас дозвониться. Перезвоните, пожалуйста, по тел. (495) 646-85-92. <?= User::fromSession()->first_name ? User::fromSession()->first_name : "{{имя}}" ?>.
			</div>
			
			<button class="btn btn-primary ajax-sms-button" onclick="sendSms()">Отправить</button>
		</div>
	</div>
</div>
<!-- /ЛАЙТБОКС ОТПРАВКА SMS -->

<!-- ЛАЙТБОКС ОТПРАВКА EMAIL -->
<div class="lightbox-new lightbox-email">
	<input type="hidden" id="email-mode" value="1">
	<h4 style="text-align: center" id="email-address">
		<span class="text-danger">email не установлен!</span>
	</h4>
	<div class="row">
		<div class="col-sm-12" id="email-history">
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12" style="text-align: center">
			<div class="form-group">
				<input class="form-control" placeholder="Тема сообщения" id="email-subject">
			</div>
			<div class="form-group">
				<textarea rows="8" class="form-control" style="width: 100%" placeholder="Текст сообщения" id="email-message"></textarea>
				<div class="email-template-list sms-template-list" style="float: left">
					<span onclick="generateEmailTemplate()">шаблон для тестов</span>
				</div>
				<div class="email-group-controls" style="float: left; display: none">
					<span style="margin-right: 7px">
						<input type="checkbox" onclick="ang_scope.to_students = !ang_scope.to_students; ang_scope.$apply()" checked> ученикам
					</span>
					<span>
						<input type="checkbox" onclick="ang_scope.to_representatives = !ang_scope.to_representatives; ang_scope.$apply()"> представителям
					</span>
				</div>
				<div class="small" style="text-align: right">
					<span class="btn-file link-like link-reverse small">
						<span>добавить файл</span>
						<input id="email-files" data-url="upload/email/" type="file" name="email_file">
					</span>
					<div id="email-files-list">
					</div>
				</div>
			</div>
			<button class="btn btn-primary ajax-email-button" onclick="sendEmail()">Отправить</button>
		</div>
	</div>
</div>
<!-- /ЛАЙТБОКС ОТПРАВКА EMAIL -->


<div class="row">
  <div class="col-sm-2" style="margin-left: 10px">
	  <div <?php if (User::fromSession()->id == 69) :?>class="fixed-menu"<?php endif ?>>
	  		<form id="global-search" action="search" method="post" style="margin-bottom: 10px">
		<div class="input-group">
		  <input id="global-search-text" type="text" class="form-control" placeholder="Поиск..." name="text" value="<?= $_POST["text"] ?>">
		  <span class="input-group-btn">
		    <button class="btn btn-default" type="submit">
		    <span class="glyphicon glyphicon-search no-margin-right"></span>
		    </button>
		  </span>
		</div><!-- /input-group -->
		</form>
	<div class="list-group">
    <a href="#" class="list-group-item active">Меню</a>
    <a href="requests" class="list-group-item">Заявки 
	    <?php
			// Количество новых заявок
			$new_request_count = Request::countNew();
			
			// Если есть новые заявки
			if ($new_request_count) {
				echo '<span class="badge pull-right">'. $new_request_count .'</span>';
			}
		?>
	</a>
	<a href="notifications" class="list-group-item">Напоминания</a>
	<a href="stats" class="list-group-item">Итоги</a>
    <a href="clients" class="list-group-item">Клиенты</a>
    <a href="sms" class="list-group-item">SMS</a>
    <a href="payments" class="list-group-item">Платежи</a>
    <a href="teachers" class="list-group-item">Преподаватели</a>
    <a href="groups" class="list-group-item">Группы</a>
    <a href="#" class="list-group-item active">Настройки</a>
	<?php if (in_array(User::fromSession()->id, [1, 69])): ?>
	    <a href="tasks" class="list-group-item">Задачи
		<?php
			// Количество новых заявок
			$new_tasks_count = Task::countNew();
			
			// Если есть новые заявки
			if ($new_tasks_count) {
				echo '<span class="badge pull-right">'. $new_tasks_count .'</span>';
			}
		?>
	<?php endif ?>
    <a href="rating" class="list-group-item">Рейтинг</a>
    <a href="settings/vocations" class="list-group-item">Календарь</a>
    <a href="test/clientsmap" class="list-group-item">Карта клиентов</a>
    <a href="settings/students" class="list-group-item">Ученики</a>
    <a href="settings/cabinets" class="list-group-item">Кабинеты</a>
    <a href="logout" class="list-group-item">Выход</a>
  </div>
<!--
    <div class="sidebar-nav">
      <div class="navbar navbar-default" role="navigation">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <span class="visible-xs navbar-brand">ЕГЭ Центр</span>
        </div>
        <div class="navbar-collapse collapse sidebar-navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="#">Заявки <span class="badge pull-right">23</span></a></a></li>
            <li><a href="#">Преподователи</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Ученики <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="#"><span class="glyphicon glyphicon-plus"></span> Добавить</a></li>
                <li class="divider"></li>
                <li><a href="#">Горячие</a></li>
                <li><a href="#">С договором</a></li>
                <li><a href="#">Новые</a></li>
                <li><a href="#">Отказы</a></li>
              </ul>
            </li>
            <li><a href="#">Группы</a></li>
            <li><a href="#">Школы</a></li>
          </ul>
        </div>
      </div>
    </div>
-->
	</div>
  </div>
  <div class="col-sm-9" style="padding: 0; width: 80.6%;">
    
  	<?php if (!$this->_custom_panel) { ?>
		<div class="panel panel-primary">
		<div class="panel-heading">
			<?= $this->tabTitle() ?>
		</div>
		<div class="panel-body">
	<?php } ?>