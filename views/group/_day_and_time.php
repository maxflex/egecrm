<div class="lightbox-new lightbox-freetime">
	<h4 style="text-transform: uppercase; margin-bottom: 25px">ДАТА И ВРЕМЯ ЗАНЯТИЙ</h4>
		<span ng-bind-html="branches_brick[Group.id_branch] | to_trusted" style="display:inline-block; margin-bottom: 10px; position: relative; left: -3px"></span>
		<span style="margin: 0 10px">
			<input type="checkbox" ng-click="selectAllWorking()"> будни
		</span>
		<span>
			<input type="checkbox" ng-click="selectAllWeek()"> вся неделя
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
						<input type="checkbox"
							ng-model="Group.day_and_time[$index + 1][n]"
							ng-click="dayAndTimeClick($index, n)"
							ng-show="weekday.schedule[n] != ''" 
							ng-value="weekday.schedule[n]"
							ng-checked="inDayAndTime($index + 1, weekday.schedule[n])"
						>
						{{weekday.schedule[n]}}
					</td>
					<td width="100">
						<input type="checkbox" ng-click="selectAllIndex(n)"> выбрать все
					</td>
				</tr>
				
			</tbody>
		</table>
	<div class="center" style="margin-top: 10px">
		<button class="btn btn-primary" ng-click="saveDayAndTime()">Сохранить</button>
	</div>
</div>