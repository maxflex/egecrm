<div class="panel panel-primary form-change-control" ng-app="Tests" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">Тесты
		<div class="pull-right">
			<a href="tests/create">добавить тест</a>
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<div class="row mb">
			<div class="col-sm-12">
				<div class="top-links">
					<a class="link-like" href="tests" ng-class="{'active': current_tab == 'tests'}">список тестов</a>
					<a class="link-like" href="tests/students" ng-class="{'active': current_tab == 'students'}">список тестов по ученикам</a>
				</div>
			</div>
		</div>
		<?= partial('tests_list') ?>
		<?= partial('student_tests_list') ?>
	</div>
</div>
