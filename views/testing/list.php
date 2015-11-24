<div ng-app="Testing" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<a href="testing/add">Создать период для тестирования</a>
	
	<table class="table table-divlike" style="margin-top: 20px">
		<tr ng-repeat="Testing in Testings">
			<td>
				<a href="testing/edit/{{Testing.id}}">Тест №{{Testing.id}}</a>
			</td>
			<td>
				<span ng-show="Testing.start_time && Testing.end_time">{{Testing.start_time}} – {{Testing.end_time}}</span>
			</td>
			<td>
				<span ng-repeat="id_subject in Testing.subjects_9">
					{{Subjects[id_subject]}}-9{{($last && !Testing.subjects_11.length) ? '' : ', '}}
				</span>
				<span ng-repeat="id_subject in Testing.subjects_11">
					{{Subjects[id_subject]}}-11{{$last ? '' : ', '}}
				</span>
			</td>
			<td>
			</td>
			<td>
				Кабинет №{{Testing.Cabinet.number}}
			</td>
		</tr>
	</table>
</div>
