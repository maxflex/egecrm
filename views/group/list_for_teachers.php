<div ng-app="Group" ng-controller="TeacherListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="row" style="position: relative">
		<div class="col-sm-12">
			<table class="table table-hover border-reverse last-item-no-border" style="position: relative;width: 100%" ng-repeat="group_year in GroupService.getYears(Groups)">
				<tr class="no-hover" ng-if="group_year">
					<td colspan="8" class="no-border-bottom">
						<h4 class="row-header default-case no-margin">Группы {{ group_year + '-' + (group_year + 1) }} учебного года</h4>
					</td>
				</tr>
				<tr ng-repeat="Group in Groups|byYear:group_year"
					class="group-list" data-id="{{Group.id}}">
					<td width="5%">
						{{Group.id}}
					</td>
					<td width="8%">
						<!-- @time-refactored @time-checked -->
						<span ng-repeat='cabinet in Group.cabinets'>
							<span style='color: {{ cabinet.color }}'>{{ cabinet.label }}</span>
							<span class="remove-space">{{$last ? '' : ', '}}</span>
						</span>
					</td>
					<td width="100">
						{{Subjects[Group.id_subject]}}-{{Group.grade}}<span ng-show="Group.level">-{{ GroupLevels[Group.level] }}</span>
					</td>
					<td width="10%">
						{{Group.students.length}} <ng-pluralize count="Group.students.length" when="{
							'one': 'ученик',
							'few': 'ученика',
							'many': 'учеников',
						}"></ng-pluralize>
					</td>
					<td>
						<span ng-show="Group.first_schedule">
							<span ng-show="!Group.past_lesson_count">1-й урок {{ Group.first_schedule | date:"dd.MM"}}</span><span ng-show="Group.past_lesson_count">был {{Group.past_lesson_count}} <ng-pluralize count="Group.past_lesson_count" when="{
								'one': 'урок',
								'few': 'урока',
								'many': 'уроков'
							}"></ng-pluralize></span></span><span ng-show="Group.first_schedule && Group.schedule_count > 0">, всего {{Group.schedule_count }}</span>
					</td>
					<td width="16%">
						<!-- @time-refactored @time-checked -->
						<span ng-repeat="data in Group.day_and_time">
							<span ng-repeat="d in data">{{ d.time.weekday_name }} в {{ d.time.time }}{{$last ? '' : ', '}}</span>{{ $last ? '' : ', '}}
						</span>
					</td>
					<td width="100px">
						<a href="teachers/groups/edit/{{Group.id}}/schedule">расписание</a>
					</td>
					<td width="100px">
						<a href="teachers/groups/journal/{{Group.id}}">посещаемость</a>
					</td>
					<td width="13%">
						<span ng-show="Group.ended">заархивировано</span>
					</td>
				</tr>
			</table>
			<div ng-show="Groups.length == 0" class="center half-black small" style="margin-bottom: 30px">список групп пуст</div>
		</div>
	</div>
</div>
