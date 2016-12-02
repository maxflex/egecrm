<link rel="stylesheet" href="js/bower/angular-bootstrap-colorpicker/css/colorpicker.min.css">
<script type="text/javascript" src="js/bower/angular-bootstrap-colorpicker/js/bootstrap-colorpicker-module.min.js"></script>
<script type="text/javascript" src="js/bower/cropper/dist/cropper.js"></script>
<script src="//cdn.jsdelivr.net/jquery.color-animation/1/mainfile"></script>
<link rel="stylesheet" href="js/bower/cropper/dist/cropper.min.css">
<link media="all" rel="stylesheet" type="text/css" href="js/bower/simple-hint/dist/simple-hint.css" />


<script type="text/javascript" src="js/vendor.js"></script>
<script type="text/javascript" src="js/app.js"></script>
<script type="text/javascript" src="js/assets.js"></script>
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
<div class="modal fade" id="searchModal" tabindex="-1" ng-controller="SearchCtrl">
	<div class="modal-dialog">
		<div class="modal-content">
			<input type="text" ng-model="query" ng-keyup="key($event)" ng-keydown="stoper($event)" placeholder="искать" id="searchQueryInput">
			<div id="searchResult">

			</div>
		</div>
	</div>
</div>


<div class="row">
	<div class="col-sm-2" style="margin-left: 10px">
	<div>
		<div class="list-group">
			<a class="list-group-item active">Основное <span class="search_icon" id="searchModalOpen"><span class="glyphicon glyphicon-search no-margin-right"></span></span></a>
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
			<a href="clients" class="list-group-item">Клиенты</a>
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
			<a href="calls/missed" class="list-group-item">Пропущенные вызовы
				<?php
					$missed_count = Call::missedCount();

					if ($missed_count) {
						echo '<span class="badge badge-danger pull-right">'. $missed_count .'</span>';
					}
				?>
			</a>
			<a href="contracts" class="list-group-item">Версии договоров</a>
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
			<a href="logs" class="list-group-item">Логи</a>
			<?php if (User::fromSession()->show_contract) :?>
				<a href="users/contract" class="list-group-item">Договор</a>
			<?php endif ?>
			<a href="settings/cabinet" class="list-group-item">Загрузка кабинетов</a>
			<a href="settings/vocations" class="list-group-item">Календарь</a>
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
