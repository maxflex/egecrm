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
	<a href="sms" class="list-group-item">SMS</a>
	<!-- @refactored -->
	<a href="groups" class="list-group-item">Группы <span class="badge pull-right"><?= Group::count(['condition' => 'ended=0 and is_unplanned=0']) ?></span></a>
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
	<a href="tests" class="list-group-item">Тесты</a>
	<a href="reports" class="list-group-item">Отчеты
		 <?php
			//$report_count = Teacher::redReportCountAll();
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
			// $missed_count = Call::missedCount();

			if ($missed_count) {
				echo '<span class="badge badge-danger pull-right">'. $missed_count .'</span>';
			}
		?>
	</a>
	<a href="map" class="list-group-item">Карта клиентов</a>
	<?php if (
		User::fromSession()->allowed(Shared\Rights::SHOW_PAYMENTS) ||
		User::fromSession()->allowed(Shared\Rights::SHOW_TEACHER_PAYMENTS) ||
		User::fromSession()->allowed(Shared\Rights::SHOW_STATS)
	) :?>
		<a class="list-group-item active">Финансы</a>
		<?php if (User::fromSession()->allowed(Shared\Rights::SHOW_PAYMENTS)) :?>
		<a href="payments" class="list-group-item">Платежи</a>
		<?php endif ?>
		<?php if (User::fromSession()->allowed(Shared\Rights::SHOW_STATS)) :?>
				 <a href="stats" class="list-group-item">Итоги</a>
		<?php endif ?>
		<?php if (User::fromSession()->allowed(Shared\Rights::SHOW_TEACHER_PAYMENTS)) :?>
			<a href="teachers/salary" class="list-group-item">Оплата преподавателей</a>
		<?php endif ?>
	<?php endif ?>

	<a class="list-group-item active">Настройки</a>
	<?php if (User::fromSession()->allowed(Shared\Rights::SHOW_TASKS)) :?>
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
	<?php if (User::fromSession()->allowed(Shared\Rights::LOGS)) :?>
		<a href="logs" class="list-group-item">Логи</a>
	<?php endif ?>
	<?php if (User::fromSession()->allowed(Shared\Rights::SHOW_CONTRACT)) :?>
		<a href="users/contract" class="list-group-item">Договор</a>
	<?php endif ?>
	<a href="settings/cabinet" class="list-group-item">Загрузка кабинетов</a>
	<?php if (User::fromSession()->allowed(Shared\Rights::SHOW_CALENDAR)) :?>
		 <a href="settings/vacations" class="list-group-item">Календарь</a>
	<?php endif ?>
	<?php if (User::fromSession()->allowed(Shared\Rights::SHOW_TEMPLATES)) :?>
		 <a href="templates" class="list-group-item">Шаблоны</a>
	<?php endif ?>
	 <a href="settings/prices" class="list-group-item">Рекомендованные цены</a>
	<?php if (User::fromSession()->allowed(Shared\Rights::SHOW_FAQ)) :?>
		 <a href="teachers/html" class="list-group-item">FAQ</a>
	<?php endif ?>
	<?php if (User::fromSession()->allowed(Shared\Rights::SHOW_USERS)) :?>
		<a href="users" class="list-group-item">Пользователи</a>
	<?php endif ?>
	<?php if (allowed(Shared\Rights::EC_STREAM)) :?>
		<a href="stream" class="cursor list-group-item">Стрим</a>
	<?php endif ?>
	<?php if (allowed(Shared\Rights::EC_ACTIVITY)) :?>
		<a href="activity" class="cursor list-group-item">Активность</a>
	<?php endif ?>
	<?php if (allowed(Shared\Rights::EMERGENCY_EXIT)) :?>
		<a onclick="emergency()" class="cursor list-group-item">Экстренный выход</a>
	<?php endif ?>
	<a href="logout" class="list-group-item">Выход</a>
</div>
