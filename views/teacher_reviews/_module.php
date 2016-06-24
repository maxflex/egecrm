<div style='position: relative'>
	<div id="frontend-loading"></div>
	<table class="table table-hover border-reverse">
		<tr ng-repeat="d in data">
			<td style="width: 10%">
				<a href="{{ <?= User::fromSession()->isStudent(true) ?> ? 'students/' : ''}}reviews/{{d.id_teacher}}/{{d.id_subject}}/{{d.year}}{{<?= User::fromSession()->isStudent(true) ?> ? '' : '/' + d.id_entity}}">
					{{d.id ? 'отзыв ' + d.id : 'создать'}}
				</a>
			</td>
			<td style="width: 28%">
				<a href="teachers/edit/{{d.Teacher.id}}">{{d.Teacher.last_name}} {{d.Teacher.first_name}} {{d.Teacher.middle_name}}</a>
			</td>
			<td style="width: 7%">
				{{three_letters[d.id_subject]}}
			</td>
			<td style="width: 22%">
				<a href="student/{{d.Student.id}}">
					<span ng-show='d.Student.last_name'>{{d.Student.last_name}} {{d.Student.first_name}}</span>
					<span ng-hide='d.Student.last_name'>имя не указано</span>
				</a>
			</td>
			<td style="width: 10%">
				{{d.lesson_count}} <ng-pluralize count='d.lesson_count' when="{
					'one': 'занятие',
					'few': 'занятия',
					'many': 'занятий',
				}"></ng-pluralize>
			</td>
			<td style="width: 3%">
				{{d.rating | hideZero}}
			</td>
			<td style="width: 3%">
				{{d.admin_rating | hideZero}}
			</td>
			<td style="width: 3%">
				{{d.admin_rating_final | hideZero}}
			</td>
			<td style="width: 14%">
				<span ng-show='d.id'>
					<span ng-class="{
							'text-danger': d.published == 0,
							'text-success': d.published == 1,
							'text-gray': d.published == 2
						}">{{ enum[d.published] }}</span>
				</span>
			</td>			
		</tr>
	</table>
</div>

<pagination
	ng-show='(data && data.length) && (counts.all > <?= TeacherReview::PER_PAGE ?>)'
	ng-model="current_page"
	ng-change="pageChanged()"
	total-items="counts.all"
	max-size="10"
	items-per-page="<?= TeacherReview::PER_PAGE ?>"
	first-text="«"
	last-text="»"
	previous-text="«"
	next-text="»"
>
</pagination>

<div ng-show="data === undefined" style="padding: 100px" class="small half-black center">
	загрузка отзывов...
</div>
<div ng-show="data === null" style="padding: 100px" class="small half-black center">
	нет отзывов
</div>