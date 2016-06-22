<td width="400">
	<a href="teachers/edit/{{Teacher.id}}">
		<span ng-show="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
			{{Teacher.last_name}} {{Teacher.first_name}} {{Teacher.middle_name}}
		</span>
		<span ng-hide="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
			Неизвестно
		</span>
	</a>
	 (<span ng-repeat="id_subject in Teacher.subjects">{{subjects[id_subject]}}{{$last ? "" : "+"}}</span>)
</td>
<td>
	<span ng-show="Teacher.banned" class="glyphicon glyphicon-lock text-danger"></span>
</td>
<td><span style='margin-left: 6px'>{{ Teacher.statuses[5] }}</span></td>
<td><span style='margin-left: 6px'>{{ Teacher.statuses[4] }}</span></td>
<td><span style='margin-left: 6px'>{{ Teacher.statuses[3] }}</span></td>
<td><span style='margin-left: 6px'>{{ Teacher.statuses[2] }}</span></td>
<td><span style='margin-left: 6px'>{{ Teacher.statuses[1] }}</span></td>
<td width='100' class="text-gray"><span style='margin-left: 6px'>{{ Teacher.statuses[0] }}</span></td>
<td>
	<span class="label label-danger-red" ng-show="Teacher.student_subject_counts">
		требуется создать {{Teacher.student_subject_counts}} <ng-pluralize count="Teacher.student_subject_counts" when="{
			'one': 'отчет',
			'few': 'отчета',
			'many': 'отчетов',
		}"></ng-pluralize>
	</span>
</td>
<td>
	<span ng-show="Teacher.schedule_date">{{Teacher.schedule_date}}</span>
</td>