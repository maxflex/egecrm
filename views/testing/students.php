<style>
	.table tr td {
		line-height: 39px !important;
	}
</style>
<div ng-app="Testing" ng-controller="StudentsCtrl" ng-init="<?= $ang_init_data ?>">
	<div ng-show="!TestingData || !TestingData.Testings">
		<div class="half-black center" style="margin: 50px 0">нет тестирований для вашего предмета и класса</div>
	</div>
	<table class="table table-divlike" style="margin-top: 20px">
		<tr ng-repeat="Testing in TestingData.Testings">
			<td>
				{{formatDate(Testing.date)}}
			</td>
			<td>
				<span ng-show="Testing.start_time && Testing.end_time">{{Testing.start_time}} – {{Testing.end_time}}</span>
			</td>
			<td>
				Кабинет №{{Testing.Cabinet.number}}
			</td>
			<td width="300">
				<select class="form-control" ng-show="!getTesting(Testing)" ng-model="Testing.selected_subject">
					<option selected value="">выберите предмет</option>
					<option disabled>──────────────</option>
					<option ng-repeat="id_subject in getAvailable(Testing)" value="{{id_subject}}">{{Subjects[id_subject]}}</option>
				</select>
				<span ng-show="getTesting(Testing)">{{Subjects[getTesting(Testing).id_subject]}}</span>
			</td>
			<td width="300" class="center">
				<button class="btn btn-primary" ng-show="!getTesting(Testing)" ng-click="addTesting(Testing)" 
					ng-disabled="!Testing.selected_subject">записаться</button>
				<span class="text-success" ng-show="getTesting(Testing)">вы записаны</span>
			</td>
		</tr>
	</table>
</div>