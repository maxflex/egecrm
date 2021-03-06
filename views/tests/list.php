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
					<span class="link-like active">список тестов</span>
					<a class="link-like" href="tests/students">список тестов по ученикам</a>
				</div>
			</div>
		</div>
		<table class="table table-divlike">
			<tr ng-repeat="Test in Tests">
				<td>
					<a href="tests/edit/{{Test.id}}">{{Test.name}}</a>
				</td>
			</tr>
		</table>
	</div>
</div>
