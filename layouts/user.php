<link rel="stylesheet" href="js/bower/angular-bootstrap-colorpicker/css/colorpicker.min.css">
<script type="text/javascript" src="js/bower/angular-bootstrap-colorpicker/js/bootstrap-colorpicker-module.min.js"></script>
<script type="text/javascript" src="js/bower/cropper/dist/cropper.js"></script>
<script src="//cdn.jsdelivr.net/jquery.color-animation/1/mainfile"></script>
<link rel="stylesheet" href="js/bower/cropper/dist/cropper.min.css">

<?php if (in_array(User::fromSession()->id, [-1])) :?>
<div class="menu-wrap">
	<nav class="menu">
		<div class="profile">
			<span class="circle-default"></span><span style="margin-left: 5px"><?= User::fromSession()->login ?></span>
		</div>
		<div class="link-list">
			<?php foreach (User::getOnlineList()->online as $User) :?>
				<div>
					<span class="circle-default"></span><a href="<?= $User->last_action_link?>" target="_blank"><span><?= $User->login?></span></a>
				</div>
			<?php endforeach ?>
			<?php foreach (User::getOnlineList()->offline as $User) :?>
				<div>
					<span class="circle-default circle-offline"></span><a href="<?= $User->last_action_link?>" target="_blank"><span><?= $User->login?></span></a>
					<i><script>document.write( moment(<?= $User->last_action_time?> * 1000).fromNow() )</script></i>
				</div>
			<?php endforeach ?>
		</div>
	</nav>
</div>
<button class="menu-button" id="open-button"><?= count(User::getOnlineList()->online) + 1 ?> <span class="circle-default"></span></button>
<?php endif ?>

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
				<textarea rows="4" class="form-control" style="width: 100%" placeholder="Текст сообщения" id="sms-message"></textarea>
			<span class="pull-right" id="sms-counter" style="position: absolute; right: 16px; bottom: 7px; color: #999; background: white; z-index: 9; border-radius: 5px">
				0 СМС
			</span>
			</div>

			<div class="sms-template-list">
				<span onclick="smsTemplate(1)">подтверждение договоренности</span>
				<span onclick="smsTemplate(2)">нет связи с клиентом</span>
				<span onclick="smsTemplate(3)">нет связи с ожидающими и не решившими</span>
				<span onclick="loginPasswordTemplate()">логин/пароль</span>
				<span onclick="newTestTemplate()">новый тест</span>

				<div class="sms-group-controls" style="float: right; display: none">
					<span style="margin-right: 7px; color: black; border-bottom: none">
						<input type="checkbox" onclick="ang_scope.to_students = !ang_scope.to_students; ang_scope.$apply()" checked> ученикам
					</span>
					<span style="color: black; border-bottom: none">
						<input type="checkbox" onclick="ang_scope.to_representatives = !ang_scope.to_representatives; ang_scope.$apply()"> представителям
					</span>
					<span style="color: black; border-bottom: none" id="sms-to-teacher">
						<input type="checkbox" onclick="ang_scope.to_teacher = !ang_scope.to_teacher; ang_scope.$apply()"> преподавателю
					</span>
				</div>
			</div>

			<div style="clear: both">
				<button class="btn btn-primary ajax-sms-button" onclick="sendSms()">Отправить</button>
			</div>
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


<?= globalPartial('phone_api'); ?>

<div class="row">
  <div class="col-sm-2" style="margin-left: 10px">
	  <div>
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
    <a class="list-group-item active">Основное</a>
    <a href="requests" class="list-group-item">Заявки
	    <?php
			// Количество новых заявок
			$new_request_count = Request::countNew();

			// Если есть новые заявки
			if ($new_request_count) {
				echo '<span class="badge pull-right" id="request-count">'. $new_request_count .'</span>';
			}
		?>
		<span id='request-counter' class='pull-right' style="margin-right: 3px; opacity: 0; font-size: 13px; font-weight: bold">+1</span>
	</a>
    <a href="clients" class="list-group-item">Клиенты 
	    <?php if (User::fromSession()->id != 1) :?>
	    <span class="badge pull-right"><?= Student::countWithActiveContract() ?></span>
	    <?php endif ?>
	</a>
    <a href="sms" class="list-group-item">История SMS</a>
	<!-- @refactored -->
    <a href="groups" class="list-group-item">Группы <span class="badge pull-right"><?= Group::count(['condition' => 'ended=0']) ?></span></a>
	<a href="stats/visits/total" class="list-group-item">Посещаемость
		<?php
			if (!LOCAL_DEVELOPMENT) {
				$journal_errors = Group::getLastWeekMissing(true);
				
				if ($journal_errors > 0) {
					echo '<span class="badge badge-danger pull-right" >'. $journal_errors .'</span>';
				}
				
/*
				$journal_errors = memcached()->get("JournalErrors");

				$journal_errors_count = 0;
				foreach ($journal_errors as $date => $values) {
					$journal_errors_count += count($values);
				}

				if ($journal_errors_count > 0) {
					echo '<span class="badge badge-danger pull-right" >'. $journal_errors_count .'</span>';
				}
*/
			}
		?>
	</a>
    <a href="testing" class="list-group-item">Тестирование</a>
    <a href="tests" class="list-group-item">Тесты</a>
    <a href="reports" class="list-group-item">Отчеты
	     <?php
		    $report_count = Teacher::redReportCountAll();
		    if ($report_count) {
	    ?>
		    <span id='red-report-count' class="badge pull-right"><?= $report_count ?></span>
		<?php
			}
		?>
    </a>
    <a href="reviews" class="list-group-item">Отзывы
	    <?php
		    $review_count = TeacherReview::countByYear();
		    if ($review_count) {
	    ?>
		    <span class="badge pull-right badge-danger"><?= $review_count ?></span>
		<?php
			}
		?>
    </a>
    <a href="teachers" class="list-group-item">Преподаватели</a>
	<a class="list-group-item active">Финансы</a>
	<a href="payments" class="list-group-item">Платежи
	    <?php
		    $unconfirmed_payment_count = Payment::countUnconfirmed();

		    if ($unconfirmed_payment_count && User::fromSession()->id != 1) {
				echo '<span class="badge pull-right">'. $unconfirmed_payment_count .'</span>';
		    }
		?>
    </a>
    <a href="stats" class="list-group-item">Итоги</a>
	<a href="teachers/salary" class="list-group-item">Оплата преподавателей</a>


    <a class="list-group-item active">Настройки</a>
	<?php if (User::fromSession()->allowedToSeeTasks()) :?>
	    <a href="tasks" class="list-group-item">Задачи
		<?php
			// Количество новых заявок
			$new_tasks_count = Task::countNew(0);

			// Если есть новые заявки
			if ($new_tasks_count) {
				echo '<span class="badge pull-right">'. $new_tasks_count .'</span>';
			}
		?>
	<?php endif ?>
	<?php if (User::fromSession()->show_contract) :?>
	    <a href="users/contract" class="list-group-item">Договор</a>
	<?php endif ?>
	<a href="settings/cabinet" class="list-group-item">Загрузка кабинетов</a>
    <a href="settings/vocations" class="list-group-item">Календарь</a>
    <a href="test/clientsmap" class="list-group-item">Карта клиентов</a>
    <a href="templates" class="list-group-item">Шаблоны</a>
    <a href="teachers/html" class="list-group-item">FAQ</a>
    <?php if (User::fromSession()->show_users) : ?>
        <a href="users" class="list-group-item">Пользователи</a>
    <?php endif ?>
    <a href="logout" class="list-group-item">Выход</a>
  </div>
	</div>
  </div>
  <div class="col-sm-9 content-col" style="padding: 0; width: 80.6%;">

  	<?php if (!$this->_custom_panel) { ?>
		<div class="panel panel-primary">
		<div class="panel-heading">
			<?= $this->tabTitle() ?>
		</div>
		<div class="panel-body">
	<?php } ?>
