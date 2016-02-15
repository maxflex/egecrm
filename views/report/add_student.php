<div ng-app="Reports" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="row mb">
		<div class="col-sm-12">
			<b>{{Student.last_name}} {{Student.first_name}}</b> (в данный момент ученик учится в {{Student.grade}} классе)
		</div>
	</div>

	<div class="row mb" ng-repeat="id_subject in getSubjects(Student.Visits)">
		<div class="col-sm-12">
			<div class="row mb">
				<div class="col-sm-12">
					<b class="m_title">{{Subjects[id_subject]}}</b> (<span ng-bind-html="Messages[id_subject] | to_trusted"></span>)
				</div>
			</div>

			<span class="link-padding">
				<div class="row" ng-repeat="Visit in Student.Visits[id_subject]">
					<div class="col-sm-12" ng-show="!isReport(Visit)">

						<span style="width: 200px" class="inline-block">
							{{formatDate(Visit.lesson_date)}} в {{formatTime(Visit.lesson_time)}} ({{getDay(Visit.lesson_date)}})

						</span>

						<span style="width: 100px" class="inline-block">
							группа {{Visit.id_group}}
						</span>

						<span style="width: 100px" class="inline-block">
							{{Visit.grade}} класс
						</span>

						<span style="width: 200px" class="inline-block">
							<span ng-show="Visit.presence == 2">не был</span>
							<span ng-show="Visit.presence == 1 && !Visit.late">был</span>
							<span ng-show="Visit.presence == 1 && Visit.late">опоздал на {{Visit.late}} <ng-pluralize count="Visit.late" when="{
								'one': 'минута',
								'few': 'минуты',
								'many': 'минут',
							}"></ng-pluralize></span>
						</span>

					</div>
					<div class="col-sm-12" ng-show="isReport(Visit)">
						<a href="teachers/reports/edit/{{Visit.id}}">отчет по {{SubjectsDative[id_subject]}} от {{formatDate(Visit.lesson_date)}}</a>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<a href="teachers/reports/add/{{Student.id}}/{{id_subject}}">создать отчет по {{SubjectsDative[id_subject]}}</a>
						<span class="label label-danger-red" ng-show="ReportRequired[id_subject]"
							style="margin-left: 5px">требуется создание отчета</span>
					</div>
				</div>
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
	.link-padding a {
		margin: 6px 0;
		display: inline-block;
	}
</style>
