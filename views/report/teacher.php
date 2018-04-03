<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<div ng-app="Reports" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="row mb">
		<div class="col-sm-12">
            <div class='user-img'>
                <img src="{{Teacher.has_photo ? 'http://static.a-perspektiva.ru/img/tutors/' + Teacher.id + '.' + Teacher.photo_extension : 'img/teachers/no-profile-img.gif'}}">
            </div>
            <div style='margin-bottom: 5px'>
                <b>{{ Teacher.last_name }} {{ Teacher.first_name }} {{ Teacher.middle_name }}</b>
            </div>
            <div style='margin-bottom: 5px'>
                Преподаватель по <span ng-repeat='id_subject in Teacher.subjects'>{{ AllSubjects[id_subject] }}{{ !$last ? ' и ' : ''}}</span>
            </div>
		</div>
	</div>

    <div class="row" ng-repeat='year in getYears()'>
        <div class="col-sm-12 link-padding">
            <b>Занятия {{ year }}–{{ year + 1}} учебного года</b>
        </div>
        <div ng-repeat="Visit in getByYears(year)" class="col-sm-12" style='margin-bottom: 5px'>
            <div ng-if='!isReport(Visit)'>
                <span class='inline-block' style='width: 200px'>
                    {{ formatDate(Visit.lesson_date)}} в {{formatTime(Visit.lesson_time) }}
                </span>
                <span class='inline-block' style='width: 150px'>
                    {{ getDay(Visit.lesson_date) }}
                </span>
                <span class='inline-block' style='width: 150px'>
                    кабинет {{ Visit.cabinet_number }}
                </span>
                <span class='inline-block' style='width: 150px'>
                    группа {{ Visit.id_group }}
                </span>
                <span class='inline-block' style='width: 100px'>
                    {{ Subject.three_letters }}
                </span>
                <span class='inline-block' style='width: 150px'>
                    {{ Visit.grade_label }}
                </span>
                <span class='inline-block' style='width: 150px'>
                    <span ng-show="Visit.presence == 2">не был</span>
                    <span ng-show="Visit.presence == 1 && !Visit.late">был</span>
                    <span ng-show="Visit.presence == 1 && Visit.late">опоздал на {{Visit.late}} <ng-pluralize count="Visit.late" when="{
                        'one': 'минута',
                        'few': 'минуты',
                        'many': 'минут',
                    }"></ng-pluralize></span>
                </span>
            </div>
            <div ng-if='isReport(Visit)' style='margin-top: 20px'>
                <a href="students/reports/view/{{ Visit.id }}" class='link-report'>
					<i class="fa fa-paperclip text-primary" aria-hidden="true"></i>
					отчет по {{ Subject.dative }} от {{formatDate(Visit.lesson_date)}}
				</a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 link-padding" ng-if='!id_group'>
            <span>Ученик прекратил обучение в группе</span>
        </div>
    </div>
    <div class="row">
        <div ng-repeat="Lesson in PlannedLessons" class="col-sm-12 text-gray" style='margin-bottom: 5px'>
            <span class='inline-block' style='width: 200px'>
                {{ formatDate(Lesson.date)}} в {{formatTime(Lesson.time) }}
            </span>
            <span class='inline-block' style='width: 150px'>
                {{ getDay(Lesson.date) }}
            </span>
            <span class='inline-block' style='width: 150px'>
                кабинет {{ Lesson.cabinet_number }}
            </span>
            <span class='inline-block' style='width: 150px'>
                группа {{ Lesson.id_group }}
            </span>
            <span class='inline-block' style='width: 100px'>
                {{ Subject.three_letters }}
            </span>
            <span class='inline-block' style='width: 150px'>
                {{ Student.grade_label }}
            </span>
            <span class='inline-block' style='width: 150px'>
                планируется
            </span>
        </div>
    </div>
</div>

<style>
	tr.inner td {
		border-top: none !important;
		padding-top: 0 !important;
	//	padding-bottom: 0 !important;
	}
	tr.inner:hover {
		background: none !important;
	}
	b.m_title {
		display: inline-block;
	}
	b.m_title:first-letter {
	    text-transform: capitalize;
	}
	.row.mb {
		margin-bottom: 20px;
	}
	.link-padding * {
		margin: 20px 0;
		display: inline-block;
	}
    .text-danger {
        font-size: 12px;
    }
</style>
