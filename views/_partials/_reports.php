<table class="table table-hover border-reverse">
	<tr ng-repeat="Report in Reports">
		<td style='width: 15%'>
			<a ng-if='Report.id' href="reports/edit/{{Report.id}}">Отчёт №{{Report.id}}</a>
			<span class="link-like-nocolor {{Report.force_noreport ? 'text-gray' : 'text-danger'}}" 
				ng-show="Report.lesson_count >= 8 && !Report.id"
				ng-click="forceNoreport(Report)"
			>
				{{Report.force_noreport ? 'отчет не требуется' : 'требуется создание отчета' }}
			</span>
		</td>
		<td style="width: 20%" ng-init="_Teacher = (Report.Teacher || Teacher)">
			<a href="teachers/edit/{{_Teacher.id}}">{{_Teacher.last_name}} {{_Teacher.first_name}} {{_Teacher.middle_name}}</a>
		</td>
		<td style='width: 7%'>
			{{three_letters[Report.id_subject]}}
		</td>
		<td style='width: 20%'>
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
		<td width="10%">
			<span class="text-danger" ng-hide="!Report.id || Report.available_for_parents">не доступен в ЛК</span>
		</td>
		<td width="13%">
			<span class="text-danger" ng-hide="!Report.id || Report.email_sent">e-mail не отправлен</span>
		</td>
	</tr>
</table>