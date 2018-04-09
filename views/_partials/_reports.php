<table class="table table-hover border-reverse small">
	<tr ng-repeat="Report in Reports">
		<td style='width: 11%'>
			<a ng-if='Report.id' href="reports/edit/{{Report.id}}">Отчёт №{{Report.id}}</a>
			<span ng-show="!Report.id">
				<span ng-show="!Report.force_noreport">
					<span class="link-like-nocolor text-gray" ng-show="Report.lesson_count < <?= Report::LESSON_COUNT ?>" ng-click="forceNoreport(Report)">мало занятий</span>
					<span class="link-like-nocolor text-danger" ng-show="Report.lesson_count >= <?= Report::LESSON_COUNT ?>" ng-click="forceNoreport(Report)">требуется отчет</span>
				</span>
				<span ng-show="Report.force_noreport" class="link-like-nocolor text-gray" ng-click="forceNoreport(Report)">отчет не требуется</span>
			</span>
		</td>
		<td style="width: 21%" ng-init="_Teacher = (Report.Teacher || Teacher)">
			<a href="teachers/edit/{{_Teacher.id}}">{{_Teacher.last_name}} {{_Teacher.first_name[0]}}. {{_Teacher.middle_name[0]}}.</a>
		</td>
		<td style='width: 6.5%'>
			{{three_letters[Report.id_subject]}}<span ng-show="Report.grade">-{{ grades_short[Report.grade] }}</span>
		</td>
		<td style='width: 10%'>
			<div class="report-grade-line" ng-if="Report.id">
				<?= globalPartial('report_circle', ['field' => 'homework_grade']) ?>
				<?= globalPartial('report_circle', ['field' => 'activity_grade']) ?>
				<?= globalPartial('report_circle', ['field' => 'behavior_grade']) ?>
				<?= globalPartial('report_circle', ['field' => 'material_grade']) ?>
				<?= globalPartial('report_circle', ['field' => 'tests_grade']) ?>
			</div>
		</td>
		<td style='width: 18%'>
			<a href="student/{{Report.Student.id}}">
				<span ng-show='Report.Student.last_name'>{{Report.Student.last_name}} {{Report.Student.first_name}}</span>
				<span ng-hide='Report.Student.last_name'>имя не указано</span>
			</a>
		</td>
		<td style='width: 10%'>
			{{Report.lesson_count}} <ng-pluralize count='Report.lesson_count' when="{
				'one': 'занятие',
				'few': 'занятия',
				'many': 'занятий',
			}"></ng-pluralize>
		</td>
		<td width="15%">
			<span class="text-danger" ng-hide="!Report.id || Report.available_for_parents">не доступен в ЛК</span>
		</td>
		<td width="10%; text-align: right">
			{{ Report.date }}
		</td>
	</tr>
</table>
