<div class="panel panel-primary" ng-app="Task" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">Задачи
		<div class="pull-right">
			<span class="link-reverse pointer" ng-click="addTask()">добавить задачу</span>
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<?php if (User::fromSession()->type == User::USER_TYPE) :?>
			<div class="top-links pull-left">
				<?php if ($_GET["type"] == 0 || empty($_GET["type"])) { ?>
				<span style="margin-right: 15px; font-weight: bold">crm</span>
				<?php } else { ?>
				<a href="tasks" style="margin-right: 15px">crm</a>
				<?php } ?>

				<?php if ($_GET["type"] == 1) { ?>
				<span style="margin-right: 15px; font-weight: bold">seo</span>
				<?php } else { ?>
				<a href="tasks/seo" style="margin-right: 15px">seo</a>
				<?php } ?>
			</div>
		<?php endif ?>

			<div class="top-links pull-right">
				<?php if ($_GET["list"] != TaskStatuses::CLOSED) { ?>
				<span style="margin-right: 15px; font-weight: bold">актуальные</span>
				<?php } else { ?>
				<a href="<?= pathLevelUp($_SERVER['REQUEST_URI']) ?>" style="margin-right: 15px">актуальные</a>
				<?php } ?>

				<?php if ($_GET["list"] == TaskStatuses::CLOSED) { ?>
				<span style="margin-right: 0; font-weight: bold">закрытые</span>
				<?php } else { ?>
				<a href="<?= $_SERVER['REQUEST_URI'] ?>/<?= TaskStatuses::CLOSED ?>" style="margin-right: 0">закрытые</a>
				<?php } ?>
			</div>

		<div id="task-app">
<!--
				<div class="task-line" style="padding-bottom: 10px; text-align: right">
					<select style="width: 150px; margin-right: 10px" ng-model="search.id_status">
						<option value="">все</option>
						<option disabled>──────────────</option>
						<option ng-repeat="(id_status, status) in task_statuses"
							ng-value="id_status" ng-selected="id_status == Task.id_status">{{status}}</option>
					</select>
				</div>
-->
				<div ng-repeat="Task in Tasks" class="row task-line" ng-hide="!Task.html.trim()">
					<div class="col-sm-12">
						<div class="task">
							<div class="text-gray pull-right" ng-show="<?= allowed(Shared\Rights::IS_DEVELOPER, true) ?>">#{{Task.id}}</div>
							<div ng-bind-html="Task.html | unsafe" name="task-{{Task.id}}" ng-click="editTask(Task)"></div>
							<span ng-repeat="file in Task.files" class="attachment-no-underline" ng-hide="editingTask(Task)">
								<span class="glyphicon glyphicon-paperclip"></span><a target="_blank" href="files/task/{{file.name}}" style="">{{file.uploaded_name}}</a> ({{file.size}})
							</span>

							<div class="small" style="text-align: right" ng-show="editingTask(Task)">
								<span class="btn-file link-like link-reverse small" ng-click="deleteTask(Task)">удалить задачу</span>
								<span class="btn-file link-like link-reverse small" ng-hide="Task.files.length >= 3">
									<span>добавить файл</span>
									<input name="task_file" type="file" id="fileupload{{Task.id}}" data-url="upload/task/" style="width: 85px; cursor: pointer">
								</span>

								<div ng-repeat="file in Task.files" class="loaded-file">
									<span style="color: black">{{file.uploaded_name}}</span>
									<a target="_blank" href="files/task/{{file.name}}" class="link-reverse small">скачать</a>
									<span class="link-like link-reverse small" ng-click="deleteTaskFile(Task, $index)">удалить</span>
								</div>
							</div>
							<div style="margin-top: 10px">
								<comments entity-id="Task.id" entity-type="TASK" user="user"></comments>
							</div>

							<div class="task-status-div">
								<span style="margin-right: 10px; opacity: .8; color: {{Task.User.color}}">{{Task.User.login}}</span>
								<span class="task-status task-status-{{Task.id_status}}" ng-click="toggleTaskStatus(Task)">
									<span ng-show="Task.id_status==1">новое</span>
									<span ng-show="Task.id_status==2">новое для Макса</span>
									<span ng-show="Task.id_status==3">новое для Шамшода</span>
									<span ng-show="Task.id_status==4">выгружено на GitHub</span>
									<span ng-show="Task.id_status==5">выгружено на Production (тестируется)</span>
									<span ng-show="Task.id_status==6">выгружено на Production (готово)</span>
									<span ng-show="Task.id_status==7">требует доработки</span>
									<span ng-show="Task.id_status==8">закрыто</span>
								</span>
							</div>
						</div>
					</div>
				</div>
		</div>
	</div>
</div>
