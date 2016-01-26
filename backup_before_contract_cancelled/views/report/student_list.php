<div ng-app="Reports" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<span ng-repeat="(id_teacher, Data) in Visits">
		<span ng-repeat="(id_subject, VisitList) in Data">
			<div class="row mb">
				<div class="col-sm-12">
					<div class="row mb">
						<div class="col-sm-12">
							<b>Отчёт преподавателя {{Teachers[id_teacher].last_name}} {{Teachers[id_teacher].first_name}} {{Teachers[id_teacher].middle_name}}</b>
							({{Subjects[id_subject]}})
						</div>
					</div>
					
					<span class="link-padding">
					<div class="row" ng-repeat="Visit in VisitList">
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
							<a href="students/reports/{{Visit.id}}">отчет по {{SubjectsDative[id_subject]}} от {{formatDate(Visit.lesson_date)}}</a>
						</div>
					</div>
					</span>
					
					<div class="row">
						<div class="col-sm-12">
							<span ng-show="PlannedLessons[id_teacher][id_subject]" class="text-gray">
								планируется еще {{PlannedLessons[id_teacher][id_subject]}} <ng-pluralize count="PlannedLessons[id_teacher][id_subject]" when="{
									'one': 'занятие',
									'few': 'занятия',
									'many': 'занятий',
								}"></ng-pluralize>
							</span>
							<span ng-show="!PlannedLessons[id_teacher][id_subject]" class="text-gray">
								занятий с этим преподавателем больше не планируется
							</span>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<span ng-show="noReports(Visits[id_teacher][id_subject])">пока не создано ни одного отчета. В течение года отчеты создаются примерно каждые 8-12 занятий</span>
							<span ng-show="!noReports(Visits[id_teacher][id_subject])">следующий отчет будет создан примерно через 8-12 занятий после предыдущего отчета</span>
						</div>
					</div>
				</div>
			</div>
		</span>
	</span>
<!--
	<div class="row mb" ng-repeat="id_subject in getSubjects(Visits)">
		<div class="col-sm-12">
			<div class="row mb">
				<div class="col-sm-12">
					<b>Отчёт преподавателя {{Teachers[id_subject].last_name}} {{Teachers[id_subject].first_name}} {{Teachers[id_subject].middle_name}}</b>
					({{Subjects[id_subject]}})
				</div>
			</div>
			<div class="row" ng-repeat="Visit in Visits[id_subject]">
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
					<a href="students/reports/{{Visit.id}}">отчет от {{formatDate(Visit.lesson_date)}}</a>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<span ng-show="PlannedLessons[id_subject]" class="text-gray">
						планируется еще {{PlannedLessons[id_subject]}} <ng-pluralize count="PlannedLessons[id_subject]" when="{
							'one': 'занятие',
							'few': 'занятия',
							'many': 'занятий',
						}"></ng-pluralize>
					</span>
					<span ng-show="!PlannedLessons[id_subject]" class="text-gray">
						занятий с этим преподавателем больше не планируется
					</span>
				</div>
			</div>
			<div class="row" ng-show="noReports(Visits[id_subject])">
				<div class="col-sm-12">
					пока не создано ни одного отчета. В течение года каждым преподавателем создается 2-3 отчета.
				</div>
			</div>
		</div>
	</div>
-->
</div>

<style>
	.row.mb {
		margin-bottom: 20px;
	}
	.link-padding a {
		margin: 6px 0;
		display: inline-block;
	}
</style>