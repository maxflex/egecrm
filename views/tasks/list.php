<div class="panel panel-primary" ng-app="Task" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">Задачи
		<div class="pull-right">
			<span class="link-reverse pointer" ng-click="addTask()">добавить задачу</span>
		</div>
	</div>
	<div class="panel-body" style="position: relative">
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
				<div ng-repeat="Task in Tasks | reverse" class="row task-line" ng-hide="!Task.html.trim()">
					<div class="col-sm-11" style="width: 93%">
						<div class="task-separator"></div>
						<div ng-bind-html="Task.html | unsafe" name="task-{{Task.id}}" ng-click="editTask(Task)"></div>
						<span ng-repeat="file in Task.files" class="attachment" ng-hide="editingTask(Task)">
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
					</div>
					<div class="col-sm-1">
					</div>
					<img src="img/task/{{Task.id_status}}.png" class="task-status-toggle" ng-click="toggleTaskStatus(Task)">
				</div>
		</div>
	</div>
</div>
