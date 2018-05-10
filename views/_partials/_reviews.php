<table class="table table-hover border-reverse" style='font-size: 12px' <?= ($review_by_year ? ' ng-repeat="review_year in getReviewsYears()"' : '') ?>>
	<tr class="no-hover" ng-if="review_year">
		<td colspan="8" class="no-border-bottom" style='padding-top: 0'>
			<h4 class="row-header">Отзывы {{ review_year + '-' + (review_year + 1) }} учебного года</h4>
		</td>
	</tr>
	<tr ng-repeat="Review in Reviews <?= ($review_by_year ? '|byYear:review_year' : '')?>">
		<td style="width: 9%">
			<a href="{{ <?= User::fromSession()->isStudent(true) ?> ? 'students/' : ''}}reviews/{{Review.id_teacher}}/{{Review.id_subject}}/{{Review.year}}{{<?= User::fromSession()->isStudent(true) ?> ? '' : '/' + (Review.id_entity || Review.id_student)}}">
				{{Review.id ? 'отзыв ' + Review.id : 'создать'}}
			</a>
		</td>
		<td ng-init="_Teacher = (Review.Teacher || Teacher)">
			<div style='width: 150px'>
				<a href="teachers/edit/{{_Teacher.id}}" ng-class="{
					'no-link': is_teacher && headed_teachers.indexOf(_Teacher.id) === -1
				}">{{_Teacher.last_name}} {{_Teacher.first_name[0]}}. {{_Teacher.middle_name[0]}}.</a>
			</div>
		</td>
		<td style="width: 7.5%">
			{{ three_letters[Review.id_subject] }}-{{ grades_short[Review.grade] }}
		</td>
		<td>
			<div style='width: 190px'>
				<a href="<?= User::isTeacher() ? "teachers/" : '' ?>student/{{Review.Student.id}}" ng-class="{
					'no-link': is_teacher && headed_students.indexOf(Review.Student.id) === -1
				}">
					<span ng-show='Review.Student.last_name'>{{Review.Student.last_name}} {{Review.Student.first_name}}</span>
					<span ng-hide='Review.Student.last_name'>имя не указано</span>
				</a>
			</div>
		</td>
		<td>
			<div style='width: 125px'>
				{{Review.lesson_count}} <ng-pluralize count='Review.lesson_count' when="{
					'one': 'занятие',
					'few': 'занятия',
					'many': 'занятий',
				}"></ng-pluralize>
			</div>
		</td>
		<td style="width: 3%" class="vertical-gray vertical-gray-left">
			<div ng-if="Review.comment" class="hint--bottom" data-hint="{{Review.comment}}"></div>
			<span class="hint--bottom" data-hint="Thank you!">{{Review.rating | hideZero}}</span>
		</td>
		<td style="width: 3%" class="vertical-gray">
			<div ng-if="Review.admin_comment" class="hint--bottom" data-hint="{{Review.admin_comment}}"></div>
			<span ng-show="Review.admin_rating > 0">{{ (Review.admin_rating == 6 ? 0 : Review.admin_rating)}}</span>
		</td>
		<td style="width: 3%" class="vertical-gray">
			<div ng-if="Review.admin_comment_final" class="hint--bottom" data-hint="{{Review.admin_comment_final}}"></div>
			<span ng-show="Review.admin_rating_final > 0">{{(Review.admin_rating_final == 6 ? 0 : Review.admin_rating_final)}}</span>
		</td>
		<td style="width: 9%" class="vertical-gray">
			<span ng-if="+(Review.score)">{{ Review.score }} из {{ Review.max_score }}</span>
        </td>
		<td style="width: 10%">
			<span ng-show='Review.id'>
				<span ng-class="{
						'text-danger': Review.published == 0,
						'text-success': Review.published == 1
					}">{{ enum[Review.published] }}</span>
			</span>
		</td>
		<td style="width: 10%">
			<span ng-show='Review.id'>
				<span ng-class="{
						'text-danger': Review.approved == 0,
						'text-success': Review.approved == 1
					}">{{ enum_approved[Review.approved] }}</span>
			</span>
		</td>
		<td style="width: 13%">
			<span style="color: {{ Review.Student.color || 'black' }}">{{Review.Student.user_login || 'system'}}</span>
		</td>
	</tr>
</table>
<style>
	.hint--bottom::after{
		white-space: pre-line;
	}
</style>
