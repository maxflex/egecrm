<table class="table table-hover border-reverse" style='font-size: 12px'>
	<tr ng-repeat="Review in Reviews">
		<td style="width: 9%">
			<a href="{{ <?= User::fromSession()->isStudent(true) ?> ? 'students/' : ''}}reviews/{{Review.id_teacher}}/{{Review.id_subject}}/{{Review.year}}{{<?= User::fromSession()->isStudent(true) ?> ? '' : '/' + (Review.id_entity || Review.id_student)}}">
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
		<td style="width: 4%" class="vertical-gray">
			{{Review.code | hideZero}}
		</td>
		<td style="width: 10%">
			<span ng-show='Review.id'>
				<span ng-class="{
						'text-danger': Review.published == 0,
						'text-success': Review.published == 1
					}">{{ enum[Review.published] }}</span>
			</span>
		</td>
		<td style="width: 13%">
			<span style="color: {{ Review.Student.color || 'black' }}">{{Review.Student.user_login || 'system'}}</span>
		</td>		
	</tr>
</table>