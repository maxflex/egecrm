<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<div ng-app="Reports" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="row mb">
		<div class="col-sm-12">
            <div class='user-img'>
				<img ng-src="img/students/{{ Student.has_photo_cropped ? Student.id + '.' + Student.photo_extension : 'no-profile-img.gif' }}">
            </div>
            <div style='margin-bottom: 5px'>
                <b>{{Student.last_name}} {{Student.first_name}}</b>
            </div>
            <div style='margin-bottom: 5px'>
                В данный момент ученик учится в {{Student.grade_label}}e
            </div>
		</div>
	</div>

	<div ng-repeat="year in years">
		<div ng-repeat="month in [9, 10, 11, 12, 1, 2, 3, 4, 5, 6, 7]" ng-if="Lessons[year][month]" class="visits-block">
			<h4>{{ months[month] }} {{ month >= 9 ? year : year + 1 }}</h4>
			<table class="table small table-hover border-reverse last-item-no-border">
				<?= partial('lessons_line', ['Lessons' => 'Lessons[year][month]']) ?>
			</table>
		</div>
	</div>

    <div class="row">
        <div class="col-sm-12 link-padding" ng-if='!id_group'>
            <span style='margin-bottom: 0'>Ученик прекратил обучение в группе</span>
        </div>
        <div class="col-sm-12 link-padding" ng-show='id_group'>
            <a href="teachers/reports/add/{{ Student.id }}/{{ Subject.id }}" class='link-report' style='padding: 8px 15px'>создать отчет по {{ Subject.dative }}</a>
            <span class="text-danger" ng-show="<?= $report_required ?>" style="margin-left: 20px">требуется создание отчета</span>
        </div>
    </div>

	<div ng-if="PlannedLessons">
		<div ng-repeat="year in years">
			<div ng-repeat="month in [9, 10, 11, 12, 1, 2, 3, 4, 5, 6, 7]" ng-if="PlannedLessons[year][month]" class="visits-block">
				<h4>{{ months[month] }} {{ month >= 9 ? year : year + 1 }}</h4>
				<table class="table small table-hover border-reverse last-item-no-border">
					<?= partial('lessons_line', ['Lessons' => 'PlannedLessons[year][month]']) ?>
				</table>
			</div>
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
