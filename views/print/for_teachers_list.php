<div class="panel panel-primary" ng-app="Print" ng-controller="TeachersCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
		Список заданий на печать
		<div class="pull-right">
			<a href="teachers/print/add" id="add-task">добавить задание</a>
		</div>
	</div>
	<div class="panel-body">
		<div class="row" ng-repeat="Task in PrintTasks" style="border-bottom: 1px dashed #eee; margin-bottom: 10px; padding-bottom: 10px">
			<div class="col-sm-10">
				<b>
					<a href="groups/edit/{{Task.id_group}}/schedule" target="_blank">Группа №{{Task.id_group}}</a>, {{formatDate(Task.Lesson.date)}} в {{Task.Lesson.time}}
				</b>
				<div class="small half-black">{{Task.comment}}</div>
				<span ng-repeat="file in Task.files" class="attachment">
					<span class="glyphicon glyphicon-paperclip"></span><a target="_blank" href="files/print/{{file.name}}" style="">{{file.uploaded_name}} ({{file.size}})</a>
				</span>
			</div>
			<div class="col-sm-2">
				<span class="pull-right label label-default print-label" ng-show="Task.id_status == 0">новое задание</span>
				<span class="pull-right label label-warning print-label" ng-show="Task.id_status == 1">печатается</span>
				<span class="pull-right label label-success print-label" ng-show="Task.id_status == 2">напечатано</span>
			</div>
		</div>
		<div class="row" ng-show="!PrintTasks || !PrintTasks.length">
			<div class="col-sm-12">
				<center class="half-black small" style="padding: 25px 0">
				список заданий пуст
				</center>
			</div>
		</div>
	</div>
</div>