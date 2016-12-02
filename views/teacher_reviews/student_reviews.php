<style>
	.dropdown-menu > li > a {
		padding: 3px 45px 3px 20px;
	}
	.bootstrap-select.btn-group .dropdown-menu li small {
		right: 10px;
	}
	.bootstrap-select.btn-group .btn .filter-option {
		white-space: initial;
		height: 20px;
	}
</style>
<div ng-app="TeacherReview" ng-controller="StudentReviews" ng-init="<?= $ang_init_data ?>" >
	<div class="panel panel-primary">
		<div class="panel-heading">
			{{ Student.last_name + ' ' + Student.first_name }} | Оценка преподавателей
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-sm-12">
					<span class="glyphicon glyphicon-hand-right pull-left" style="height: 50px; vertical-align: middle; top: 1px; margin-right: 14px; font-size: 28px"></span>
					<div style="line-height: 14px">ваш отзыв очень важен для ЕГЭ-Центра. С помощью отзывов в ЕГЭ-Центре остаются работать лучшие преподаватели. Пишите как есть, не стесняйтесь – отзывы доступны только администрации.</div>
				</div>
			</div>
			<table class="table table-hover border-reverse">
		<tr ng-repeat="Review in Reviews">
			<td style="width: 77%">
				{{Review.lesson_count}} <ng-pluralize count='Review.lesson_count' when="{
					'one': 'занятие',
					'few': 'занятия',
					'many': 'занятий',
				}"></ng-pluralize> в {{Review.year}}–{{Review.year+1}} учебном году с преподавателем {{Review.Teacher.last_name}} {{Review.Teacher.first_name}} {{Review.Teacher.middle_name}} ({{Subjects[Review.id_subject]}})
			</td>
			<td style="width: 23%">
				<a href="students/reviews/{{Review.id_teacher}}/{{Review.id_subject}}/{{Review.year}}">
					{{Review.id ? 'редактировать отзыв' : 'создать отзыв'}}
				</a>
			</td>
		</tr>
	</table>
	</div>
</div>
