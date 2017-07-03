<!-- @rights-refactored -->
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
<!-- форма поиска -->
<div class="modal" id="searchModal" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<input type="text" placeholder="искать" id="searchQueryInput" v-on:keyup="keyup" v-on:keydown.up.prevent  v-model="query">
			<!--<input type="text" ng-model="query" ng-keyup="key($event)" ng-keydown="stoper($event)" placeholder="искать" id="searchQueryInput">-->
			<div id="searchResult">
				<div class="searchResultWraper" v-if="query!='' && !loading && results == 0">
					<div class="notFound" v-if="!error">cовпадений нет</div>
				</div>
				<div v-if="results > 0" v-for="(index, row) in lists" class="resultRow" v-bind:class="{active : ((index+1) ==  active)}">
					<div v-if="row.type == 'students'">
						<a v-bind:href="row.link">{{ row.last_name }} {{ row.first_name }} {{ row.middle_name }}</a>  - ученик
					</div>

					<div v-if="row.type == 'representatives'">
						<a v-bind:href="row.link">{{ row.last_name }} {{ row.first_name }} {{ row.middle_name }}</a>  - представитель
					</div>

					<div v-if="row.type == 'tutors'">
						<a v-bind:href="row.link">{{ row.last_name }} {{ row.first_name }} {{ row.middle_name }}</a>  - преподаватель
					</div>

					<div v-if="row.type == 'requests'">
						<a v-bind:href="row.link">{{ row.name || 'имя не указано' }}</a>  - заявка
					</div>

                    <div v-if="row.type == 'contracts'">
                        <a v-bind:href="row.link">№{{ row.id_contract }}</a>  - договор
                    </div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- конец формы поиска -->

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
    					// $missed_count = Call::missedCount();

    					if ($missed_count) {
    						echo '<span class="badge badge-danger pull-right">'. $missed_count .'</span>';
    					}
    				?>
    			</a>
    			<a href="contracts" class="list-group-item">Версии договоров</a>
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
    					$new_tasks_count = Task::countNew(0);

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
    		         <a href="settings/vocations" class="list-group-item">Календарь</a>
                <?php endif ?>
                <?php if (User::fromSession()->allowed(Shared\Rights::SHOW_TEMPLATES)) :?>
    		         <a href="templates" class="list-group-item">Шаблоны</a>
                <?php endif ?>
                <?php if (User::fromSession()->allowed(Shared\Rights::SHOW_FAQ)) :?>
    		         <a href="teachers/html" class="list-group-item">FAQ</a>
                <?php endif ?>
                <?php if (User::fromSession()->allowed(Shared\Rights::SHOW_USERS)) :?>
    				<a href="users" class="list-group-item">Пользователи</a>
    			<?php endif ?>
                <?php if (allowed(Shared\Rights::EC_STREAM)) :?>
    				<a href="stream" class="cursor list-group-item">Стрим</a>
    			<?php endif ?>
                <?php if (allowed(Shared\Rights::EMERGENCY_EXIT)) :?>
    				<a onclick="emergency()" class="cursor list-group-item">Экстренный выход</a>
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
