<div class="panel panel-primary" ng-app="Reports" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
		Отчёты
		<div class="pull-right">
<!--
			<span class="link-white link-reverse link-like">
				отправить отчет родителю по e-mail
			</span>
-->
		</div>
	</div>
	<div class="panel-body">
        <div class="top-links pull-left">
			<a ng-class="{'active': year == <?= $year ?>}" href='students/reports/{{ year }}'
                ng-repeat='year in <?= Years::json() ?>'>{{ year + '-' + (year + 1)  }}</a>
		</div>
		<table class="table table-hover">
			<tr ng-repeat="d in data">
				<td style="width: 20%">
					<a href="students/reports/teacher/{{ d.Teacher.id }}/{{ d.id_subject }}">{{d.Teacher.last_name}} {{d.Teacher.first_name}} {{d.Teacher.middle_name}}</a>
				</td>
				<td style="width: 15%">
					{{ d.lessons_count }} <ng-pluralize count="d.lessons_count" when="{
						'one': 'занятие',
						'few': 'занятия',
						'many': 'занятий'
					}"></ng-pluralize>
				</td>
                <td style="width: 10%">
                    {{ Subjects[d.id_subject]}}
                </td>
                <!-- <td style="width: 15%">
                    <span ng-if='d.id_group'>группа {{ d.id_group }}</span>
                </td> -->
                <td style="width: 15%">
                    <span ng-if='d.id_group'>группа {{ d.id_group }}</span>
                </td>
				<td style="width: 10%">
					<span ng-show="d.reports_count">
                        {{ d.reports_count }}
                        <ng-pluralize count="d.reports_count" when="{
							'one': 'отчет',
							'few': 'отчета',
							'many': 'отчетов'
						}"></ng-pluralize>
                    </span>
					<span ng-show="!d.reports_count">отчетов нет</span>
				</td>
			</tr>
		</table>
	</div>
</div>
