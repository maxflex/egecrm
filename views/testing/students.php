<style>
	.table tr td {
		line-height: 39px !important;
	}
</style>
<div ng-app="Testing" ng-controller="StudentsCtrl" ng-init="<?= $ang_init_data ?>">
	<div>Пройдите пробный ЕГЭ и узнайте уровень вашей готовности к экзамену на сегодняшний день. Пробный ЕГЭ в ЕГЭ-Центре:<br>
		- проводится на настоящих бланках Федерального Центра Тестирования<br>
		- условия проведения экзамена максимально приближены к будущему ЕГЭ-2016<br>
		- тесты проверяются экспертами ЕГЭ<br>
		Стоимость тестирования – 800 рублей. Время проверки 7 дней. <br>
		<span class="text-danger">Если Вам не подходит время или пробное тестирование по нужному предмету недоступно, просто подождите, когда будет создано еще одно тестирование.</span>
	</div>
	<table class="table table-divlike" style="margin-top: 20px">
		<tr ng-repeat="Testing in Testings" ng-show="getAllSubjects(Testing).length > 0">
			<td>
				Тестирование №{{Testing.id}}
			</td>
			<td>
				{{formatDate(Testing.date)}}, начало в {{Testing.start_time}}
			</td>
			<td>
				{{Testing.Cabinet.number}} кабинет
			</td>
			<td>
				<div ng-hide="Testing.closed">
					<span ng-show="Testing.max_students - Testing.Students.length > 0">
						свободно {{Testing.max_students - Testing.Students.length}} из {{Testing.max_students}}
					</span>
					<span ng-show="Testing.max_students - Testing.Students.length <= 0">
						мест нет
					</span>
				</div>
			</td>
			<td style="width: 360px !important">
				<div ng-hide="Testing.closed">
					<select class="form-control subject-select" ng-show="!getTesting(Testing)" ng-model="Testing.selected_subject">
						<option selected value="">доступно {{getAllSubjects(Testing).length}} из 10 предметов</option>
						<option disabled>──────────────</option>
						<option ng-repeat="(id_subject, nothing) in Subjects" value="{{id_subject}}" ng-disabled="!isAvailable(Testing, id_subject)"
							ng-show="grade == 11">пробный ЕГЭ по {{Subjects[id_subject]}} ({{minutes_11[id_subject]}} минут)</option>
						<option ng-repeat="(id_subject, nothing) in Subjects" value="{{id_subject}}" ng-disabled="!isAvailable(Testing, id_subject)"
							ng-show="grade == 9">пробный ОГЭ по {{Subjects[id_subject]}} ({{minutes_9[id_subject]}} минут)</option>
	<!-- 					<option ng-repeat="id_subject in getAll(Testing)" value="{{id_subject}}">{{Subjects[id_subject]}}</option> -->
					</select>
					<span ng-show="getTesting(Testing)">
						<span ng-show="grade == 9">
							пробный ОГЭ по {{Subjects[getTesting(Testing).id_subject]}} ({{minutes_9[getTesting(Testing).id_subject]}} минут)
						</span>
						<span ng-show="grade == 11">
							пробный ЕГЭ по {{Subjects[getTesting(Testing).id_subject]}} ({{minutes_11[getTesting(Testing).id_subject]}} минут)
						</span>
					</span>
				</div>
				<div ng-show="Testing.closed">
					Запись окончена
				</div>
			</td>
			<td class="center">
				<div ng-hide="Testing.closed">
					<button class="btn btn-primary" ng-show="!getTesting(Testing)" ng-click="addTesting(Testing)" 
						ng-disabled="!Testing.selected_subject || Testing.adding || (Testing.max_students - Testing.Students.length <= 0)">записаться</button>
					<span class="text-success" ng-show="getTesting(Testing)">вы записаны</span>
				</div>
			</td>
		</tr>
	</table>
	
	<div class="row">
		<div class="col-sm-12">
			<div class="alert alert-info">
				В течение декабря 2015 года будет создано в сумме 15 тестирований
			</div>
		</div>
	</div>
	
</div>