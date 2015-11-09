<style>
	table tr td:not(:first-child) {
		text-align: center;
	}
	table.left-align tr td {
		text-align: left;
	}
</style>

<div ng-app="Stats" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="top-links">
		<?php if ($_GET["group"] == "d" || empty($_GET["group"])) { ?>
		<span style="margin-right: 15px; font-weight: bold">по дням</span>
		<?php } else { ?>
		<a href="stats/visits/students?group=d" style="margin-right: 15px">по дням</a>
		<?php } ?>
		
		<?php if ($_GET["group"] == "w") { ?>
		<span style="margin-right: 15px; font-weight: bold">по неделям</span>
		<?php } else { ?>
		<a href="stats/visits/students?group=w" style="margin-right: 15px">по неделям</a>
		<?php } ?>
		
		<?php if ($_GET["group"] == "m") { ?>
		<span style="margin-right: 15px; font-weight: bold">по месяцам</span>
		<?php } else { ?>
		<a href="stats/visits/students?group=m" style="margin-right: 15px">по месяцам</a>
		<?php } ?>
		
		<?php if ($_GET["group"] == "y") { ?>
		<span style="margin-right: 15px; font-weight: bold">по годам</span>
		<?php } else { ?>
		<a href="stats/visits/students?group=y" style="margin-right: 15px">по годам</a>
		<?php } ?>
		
		<div class="pull-right">
			<span class="link-like active">общая посещаемость</span>
			<a href="stats/visits/teachers">по преподавателям</a>
		</div>
		
	</div>
	
	<table class="table table-hover">
		<thead style="font-weight: bold">
			<tr>
				<td>
				</td>
				<td>
					кол-во занятий
				</td>
				<td>
					были на занятии
				</td>
				<td>
					опоздали
				</td>
				<td>
					пропустили
				</td>
				<td>
					доля пропуска
				</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach($stats as $date => $stat): ?>
			<tr>
				<td>
					<span class="pointer" ng-click="dateLoad('<?= $date ?>')">
						<?= strftime("%d %b %Y", strtotime($date)) ?>
					</span>
					<?php if (count($errors[$date]) > 0) :?>
						<span class="badge badge-danger" style="margin-left: 10px"><?= count($errors[$date]) ?></span>
					<?php endif ?>
				</td>
				<td>
					<?= $stat['lesson_count'] ? $stat['lesson_count'] : '' ?>
				</td>
				<td>
					<?= $stat['visit_count'] ? $stat['visit_count'] : '' ?>
				</td>
				<td>
					<?= $stat['late_count'] ? $stat['late_count'] : '' ?>
				</td>
				<td>
					<?= $stat['abscent_count'] ? $stat['abscent_count'] : '' ?>
				</td>
				<td>
					<?= $stat['visit_count'] ? $stat['late_percent'] . '%' : '' ?>
				</td>
			</tr>
			<tr id="<?= $date ?>" style="display: none" class="no-hover">
				<td colspan="6">
					<table class="table table-divlike left-align" style="margin: 0; width: 90%">
						<tr ng-repeat="Schedule in Schedules['<?= $date ?>']">
							<td>
								<span ng-class="{
									'text-gray'		: isFutureLesson(Schedule),
									'text-danger'	: isMissingLesson(Schedule),
								}">
									{{Schedule.time}}
								</span></td>
							<td>
								<div ng-bind-html="Schedule.Group.branch | to_trusted"></div>
							</td>
							<td>
								<a ng-class="{
									'gray-link'		: isFutureLesson(Schedule),
									'text-danger'	: isMissingLesson(Schedule),
								}" href="groups/edit/{{Schedule.id_group}}" target="_blank">Группа {{Schedule.id_group}}</a>
							</td>
							<td width="150">
								<span ng-class="{
									'text-gray'		: isFutureLesson(Schedule),
									'text-danger'	: isMissingLesson(Schedule),
								}">
									{{Subjects[Schedule.Group.id_subject]}}{{Schedule.Group.grade ? '-' + Schedule.Group.grade : ''}}
								</span>
							</td>
							<td>
								<a ng-class="{
									'gray-link'		: isFutureLesson(Schedule),
									'text-danger'	: isMissingLesson(Schedule),
								}" target="_blank" href="groups/edit/{{Schedule.id_group}}/schedule">расписание</a>
							</td>
							<td>
								<span ng-class="{
									'text-gray'		: isFutureLesson(Schedule),
									'text-danger'	: isMissingLesson(Schedule),
								}">
									{{Schedule.Group.students.length}} <ng-pluralize count="Schedule.Group.students.length" when="{
										'one': 'ученик',
										'few': 'ученика',
										'many': 'учеников',
									}"></ng-pluralize>
								</span>
							</td>
							<td>
								<a ng-class="{
									'gray-link'		: isFutureLesson(Schedule),
									'text-danger'	: isMissingLesson(Schedule),
								}" target="_blank" href="teachers/edit/{{Schedule.Group.Teacher.id}}">{{Schedule.Group.Teacher.last_name}} {{Schedule.Group.Teacher.first_name}} {{Schedule.Group.Teacher.middle_name}}</a>
							</td>
							<td>
								<span ng-class="{
									'label-red-visits': Schedule.lesson_number == 1,
								}">
									<span ng-class="{
										'text-gray'		: isFutureLesson(Schedule),
										'text-danger'	: isMissingLesson(Schedule),	
									}">{{Schedule.lesson_number}} урок</span>
								</span>
							</td>
						</tr>
					</table>
					<span class="vertical-margin small" ng-show="Schedules['<?= $date ?>'] === undefined">загрузка...</span>
					<span class="vertical-margin small" ng-show="Schedules['<?= $date ?>'] === false">нет групп на эту дату</span>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
	<?php if ($_GET["group"] == "d" || empty($_GET["group"])) :?>
<!--
	<pagination
	  ng-model="currentPage"
	  ng-change="pageStudentChanged()"
	  total-items="<?= round(VisitJournal::fromFirstLesson() / StatsController::PER_PAGE) ?>"
	  max-size="10"
	  items-per-page="<?= StatsController::PER_PAGE ?>"
	  first-text="«"
	  last-text="»"
	  previous-text="«"
	  next-text="»"
	>
	</pagination>
-->
	<?php endif ?>
</div>