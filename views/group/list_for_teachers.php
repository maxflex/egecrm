<?= globalPartial('font_awesome') ?>
<div ng-app="Group" ng-controller="TeacherListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="row" style="position: relative">
		<div class="col-sm-12">
			<table class="table table-hover border-reverse last-item-no-border" style="position: relative;width: 100%" ng-repeat="group_year in GroupService.getYears(Groups)">
				<tr class="no-hover" ng-if="group_year">
					<td colspan="8" class="no-border-bottom">
						<h4 class="row-header">Группы {{ group_year + '-' + (group_year + 1) }} учебного года</h4>
					</td>
				</tr>
				<tr ng-repeat="Group in Groups|byYear:group_year"
					class="group-list" data-id="{{Group.id}}">
					<td width='90'>
						{{Group.id}}
						<a style='margin-left: 5px' href="teachers/groups/edit/{{Group.id}}/schedule"><i class="fa fa-calendar-o" aria-hidden="true"></i></a>
						<a style='margin-left: 5px' href="teachers/groups/journal/{{Group.id}}"><i class="fa fa-users" aria-hidden="true"></i></a>
					</td>
					<td width='90'>
						<!-- @time-refactored @time-checked -->
						<span ng-repeat='cabinet in Group.cabinets'>
							<span style='color: {{ cabinet.color }}'>{{ cabinet.label }}</span>
							<span class="remove-space">{{$last ? '' : ', '}}</span>
						</span>
					</td>
					<td width="110">
						{{Subjects[Group.id_subject]}}-{{ Group.grade_short }}<span ng-show="Group.level">-{{ GroupLevels[Group.level] }}</span>
					</td>
					<td width="120">
						{{Group.students.length}} <ng-pluralize count="Group.students.length" when="{
							'one': 'ученик',
							'few': 'ученика',
							'many': 'учеников',
						}"></ng-pluralize>
					</td>
					<td width='160'>
						<span ng-show="Group.first_lesson_date">
							<span ng-show="!Group.lesson_count.conducted">1-й урок {{Group.first_lesson_date | date:"dd.MM"}}</span>
							<span ng-show="Group.lesson_count.conducted">
								было {{Group.lesson_count.conducted}} из {{ Group.lesson_count.all }}
								<ng-pluralize count="Group.lesson_count.all" when="{
									'one': 'урока',
									'few': 'уроков',
									'many': 'уроков'
								}"></ng-pluralize>
							</span>
			            </span>
					</td>
					<td width="200">
						<!-- @time-refactored @time-checked -->
						<span ng-repeat="data in Group.day_and_time">
							<span ng-repeat="d in data">{{ d.time.weekday_name }} в {{ d.time.time }}{{$last ? '' : ', '}}</span>{{ $last ? '' : ', '}}
						</span>
					</td>
					<td width='200'>
						<span ng-if="Group.id_head_teacher">
							{{ Group.head_teacher_label }}
						</span>
					</td>
					<td width="200" style='text-align: right'>
						<span ng-show="Group.ended">заархивировано</span>
					</td>
				</tr>
			</table>
			<div ng-show="Groups.length == 0" class="center half-black small" style="margin-bottom: 30px">список групп пуст</div>
		</div>
	</div>

	<div class="row" style="position: relative" ng-if="TeacherAdditionalPayments && TeacherAdditionalPayments.length">
		<div class="col-sm-12">
			<table class="table table-hover border-reverse last-item-no-border" style="position: relative;width: 100%">
				<tr class="no-hover">
					<td colspan="8" class="no-border-bottom">
						<h4 class="row-header">Дополнительные услуги</h4>
					</td>
				</tr>
				<tr ng-repeat="payment in TeacherAdditionalPayments" class='group-list'>
					<td width='150'>
						{{ payment.date }}
					</td>
					<td width='150'>
						{{ yearLabel(payment.year) }}
					</td>
					<td width='150'>
						{{ payment.sum | number }} руб.
					</td>
					<td>
						{{ payment.purpose }}
					</td>
				</tr>
			</table>
		</div>
	</div>

	<div class="row" style="position: relative" ng-if="AdditionalLessons.length">
		<div class="col-sm-12">
			<table class="table table-hover border-reverse last-item-no-border" style="position: relative;width: 100%">
				<tr class="no-hover">
					<td colspan="8" class="no-border-bottom">
						<h4 class="row-header">Дополнительные занятия</h4>
					</td>
				</tr>
				<tr ng-repeat="Lesson in AdditionalLessons" class='group-list'>
					<td width='150'>
						{{ Lesson.lesson_date_formatted }} в {{ Lesson.lesson_time }}
					</td>
					<td width='150'>
						{{ yearLabel(Lesson.year) }}
					</td>
					<td width='150'>
						{{ Lesson.teacher_price | number }} руб.
					</td>
					<td width='100'>
						{{Subjects[Lesson.id_subject]}}{{Lesson.grade ? '-' + Lesson.grade_short : ''}}
					</td>
					<td width='100'>
						<span style='color: {{ getCabinet(Lesson.cabinet).color }}'>{{ getCabinet(Lesson.cabinet).label }}</span>
					</td>
					<td>
						{{ Lesson.students.length }} <ng-pluralize count="Lesson.students.length" when="{
							'one': 'ученик',
							'few': 'ученика',
							'many': 'учеников'
						}"></ng-pluralize>
					</td>
					<td style='text-align: right'>
						<span ng-show="Lesson.cancelled">урок отменен</span>
						<a href='teachers/lesson/{{ Lesson.id }}' ng-show='!Lesson.cancelled'>
							{{ Lesson.is_conducted ? 'урок проведен' : 'зарегистрировать урок' }}
						</a>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
