<div class="panel panel-primary" ng-app="Task"  ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">Задачи
		<div class="pull-right">
			<span class="link-reverse pointer" ng-click="addTask()">добавить задачу</span>
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<div id="task-app">
				<div class="task-line" style="padding-bottom: 10px; text-align: right">
					<select style="width: 150px; margin-right: 10px" ng-model="search.id_status">
						<option value="">все</option>
						<option disabled>──────────────</option>
						<option ng-repeat="(id_status, status) in task_statuses" 
							ng-value="id_status" ng-selected="id_status == Task.id_status">{{status}}</option>
					</select>
				</div>
				<div ng-repeat="Task in Tasks | reverse | filter:{id_status: search.id_status}" 
					class="task-line" ng-hide="!Task.html.trim()" ng-class="{'no-border-bottom' : $last}">
					<div ng-bind-html="Task.html" name="task-{{Task.id}}" ng-click="editTask(Task)"></div>
					<div class="controls-buttons">
						<select class="pull-right" style="width: 150px; margin-right: 10px" ng-model="Task.id_status" ng-change="saveTask(Task)">
							<option selected><?= TaskStatuses::$title ?></option>
							<option disabled>──────────────</option>
							<option ng-repeat="(id_status, status) in task_statuses" 
								ng-value="id_status" ng-selected="id_status == Task.id_status">{{status}}</option>
						</select>
					</div>
				</div>
		</div>
	</div>
</div>
