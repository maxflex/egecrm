<table class="table table-hover">
	<tr ng-repeat="Report in Reports">
		<td style='width: 20%'>
			<a ng-if='Report.id' href="reports/edit/{{Report.id}}">Отчёт №{{Report.id}}</a>
			<span class="link-like-nocolor {{Report.force_noreport ? 'text-gray' : 'text-danger'}}" 
				ng-show="Report.lesson_count >= 8 && !Report.id"
				ng-click="forceNoreport(d)"
			>
				{{Report.force_noreport ? 'отчет не требуется' : 'требуется создание отчета' }}
			</span>
		</td>
		<td style="width: 33%" ng-init="_Teacher = (Report.Teacher || Teacher)">
			<a href="teachers/edit/{{_Teacher.id}}">{{_Teacher.last_name}} {{_Teacher.first_name}} {{_Teacher.middle_name}}</a>
		</td>
		<td style='width: 7%'>
			{{three_letters[Report.id_subject]}}
		</td>
		<td style='width: 30%'>
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
	</tr>
</table>