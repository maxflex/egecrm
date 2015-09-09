<div class="lightbox-new lightbox-freetime">
	<h4 style="text-transform: uppercase; margin-bottom: 25px">СВОБОДНОЕ ВРЕМЯ
		<input type="text" class="form-control pull-right bs-date" name="Teacher[schedule_date]" ng-model="Teacher.schedule_date"
			placeholder="актуальность расписания" style="font-weight: normal; width: 200px">
	</h4>
	<div ng-repeat="id_branch in Teacher.branches">
		<span ng-bind-html="branches_brick[id_branch] | to_trusted" style="display:inline-block; margin-bottom: 10px; position: relative; left: -3px"></span>
		<span style="margin: 0 10px">
			<input type="checkbox" ng-click="selectAllWorking(id_branch)"> будни
		</span>
		<span>
			<input type="checkbox" ng-click="selectAllWeek(id_branch)"> вся неделя
		</span>
		<table class="table table-divlike">
			<thead>
				<tr>
					<td ng-repeat="weekday in weekdays">{{weekday.short}}</td>
				</tr>
			</thead>
			<tbody>
				<tr ng-repeat="n in [] | range:4">
					<td ng-repeat="weekday in weekdays">
						<input type="checkbox" ng-model="freetime[id_branch][$index + 1][n]"
							name="Teacher[freetime][{{id_branch}}][{{$index + 1}}][{{n}}]"
							ng-click="freetimeClick(id_branch, $index, n)"
							ng-show="weekday.schedule[n] != ''" 
							ng-value="weekday.schedule[n]"
							ng-checked="inFreetime(id_branch, $index + 1, weekday.schedule[n])"
						> 
						{{weekday.schedule[n]}}
					</td>
					<td width="100">
						<input type="checkbox" ng-click="selectAllIndex(id_branch, n)"> выбрать все
					</td>
				</tr>
				
			</tbody>
		</table>
	</div>
	<div class="center" style="margin-top: 10px">
		<button class="btn btn-primary" ng-click="saveFreetime()">Сохранить</button>
	</div>
</div>