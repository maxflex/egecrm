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
<div ng-app="TeacherReview" ng-controller="Reviews" ng-init="<?= $ang_init_data ?>" >
	<div class="panel panel-primary">
		<div class="panel-heading">
			{{ Student.last_name + ' ' + Student.first_name }} | Оценка преподавателей
		</div>
		<div class="panel-body">
			<table class="table table-hover border-reverse" style='font-size: 12px'>
		<tr ng-repeat="Review in Reviews">
			<td style="width: 9%">
				<a href="students/reviews/{{Review.id_teacher}}/{{Review.id_subject}}/{{Review.year}}">
					{{Review.id ? 'отзыв ' + Review.id : 'создать'}}
				</a>
			</td>
			<td style="width: 25%" ng-init="_Teacher = (Review.Teacher || Teacher)">
				<a href="teachers/edit/{{_Teacher.id}}">{{_Teacher.last_name}} {{_Teacher.first_name}} {{_Teacher.middle_name}}</a>
			</td>
			<td style="width: 5%">
				{{three_letters[Review.id_subject]}}
			</td>
			<td style="width: 15%">
				<a href="student/{{Review.Student.id}}">
					<span ng-show='Review.Student.last_name'>{{Review.Student.last_name}} {{Review.Student.first_name}}</span>
					<span ng-hide='Review.Student.last_name'>имя не указано</span>
				</a>
			</td>
			<td style="width: 10%">
				{{Review.lesson_count}} <ng-pluralize count='Review.lesson_count' when="{
					'one': 'занятие',
					'few': 'занятия',
					'many': 'занятий',
				}"></ng-pluralize>
			</td>
			<td style="width: 3%">
				<div ng-if="Review.comment" class="hint--bottom" data-hint="{{Review.comment}}"></div>
				<span class="hint--bottom" data-hint="Thank you!">{{Review.rating | hideZero}}</span>
			</td>	
		</tr>
	</table>
	</div>
</div>
