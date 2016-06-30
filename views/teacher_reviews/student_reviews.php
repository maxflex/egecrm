<style>
	.dropdown-menu > li > a {
		padding: 3px 45px 3px 20px;
	}
	.bootstrap-select.btn-group .dropdown-menu li small {
		right: 10px;
	}
	.bootstrap-select.btn-group .btn .filter-option {
		white-space: initial;
		height: 20px;
	}
</style>
<div ng-app="TeacherReview" ng-controller="Reviews" ng-init="<?= $ang_init_data ?>" >
	<div class="panel panel-primary">
	<div class="panel-heading">
		{{ Student.last_name + ' ' + Student.first_name }} | Оценка преподавателей
	</div>
	<div class="panel-body">
		
		<?= partial('module') ?>
        <div style="margin-top: 15px" class="pull-right">
            ответственный:
            <span id="request-user-display"
                  ng-click="toggleUser()"
                  class="user-pick ng-binding"
                  style="color: {{ Student.color }}">{{ Student.user_login || 'system' }}</span>
        </div>
	</div>
</div>
