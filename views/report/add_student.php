<div ng-app="Reports" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="row mb">
		<div class="col-sm-12">
			<b>{{Student.last_name}} {{Student.first_name}}</b> ({{Student.grade}} класс)
		</div>
	</div>
	
	<div class="row mb" ng-repeat="id_subject in getSubjects(Student.Visits)">
		<div class="col-sm-12">
			<div class="row mb">
				<div class="col-sm-12">
					<b class="m_title">{{Subjects[id_subject]}}</b>
				</div>
			</div>
			<div class="row" ng-repeat="Visit in Student.Visits[id_subject]">
				<div class="col-sm-12" ng-show="!isReport(Visit)">
					{{formatDate(Visit.lesson_date)}} – 
					<span ng-show="Visit.presence == 1 && !Visit.late">был</span>
					<span ng-show="Visit.presence == 1 && Visit.late">опоздал на {{Visit.late}} <ng-pluralize count="Visit.late" when="{
						'one': 'минута',
						'few': 'минуты',
						'many': 'минут',
					}"></ng-pluralize></span>
					<span ng-show="Visit.presence == 2">не был</span>
				</div>
				<div class="col-sm-12" ng-show="isReport(Visit)">
					<a href="teachers/reports/edit/{{Visit.id}}">отчет от {{formatDate(Visit.lesson_date)}}</a>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<a href="teachers/reports/add/{{Student.id}}/{{id_subject}}">создать отчет по {{SubjectsDative[id_subject]}}</a>
				</div>
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
		display: block;
	}
	b.m_title:first-letter {
	    text-transform: capitalize;
	}
	.row.mb {
		margin-bottom: 20px;
	}
</style>