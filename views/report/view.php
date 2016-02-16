<div class="panel panel-primary form-change-control" ng-app="Reports" ng-controller="AddCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
		Отчёт преподавателя по  {{Subjects[Report.id_subject]}} {{Report.Teacher.last_name}} {{Report.Teacher.first_name}} {{Report.Teacher.middle_name}}
	</div>
	<div class="panel-body">
		<div class="row mb">
			<div class="col-sm-12">
				<div class="row">
					<div class="col-sm-12">
						<b>Выполнение домашнего задания:</b>
						<span style="margin-right: 5px">оценка</span>
						<span class="teacher-rating active default" style="margin: 0 !important" ng-show="Report.homework_grade" ng-class="{
						'active': !empty(Report.homework_grade),
						'bg-red': Report.homework_grade <= 3 && !empty(Report.homework_grade),
						'bg-orange': Report.homework_grade == 4 && !empty(Report.homework_grade),
						}">{{Report.homework_grade}}</span>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						{{Report.homework_comment}}
					</div>
				</div>
			</div>
		</div>
		<div class="row mb">
			<div class="col-sm-12">
				<div class="row">
					<div class="col-sm-12">
						<b>Работоспособность и активность на уроках:</b>
						<span style="margin-right: 5px">оценка</span>
						<span class="teacher-rating active default" style="margin: 0 !important" ng-show="Report.activity_grade" ng-class="{
						'active': !empty(Report.activity_grade),
						'bg-red': Report.activity_grade <= 3 && !empty(Report.activity_grade),
						'bg-orange': Report.activity_grade == 4 && !empty(Report.activity_grade),
						}">{{Report.activity_grade}}</span>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						{{Report.activity_comment}}
					</div>
				</div>
			</div>
		</div>
		<div class="row mb">
			<div class="col-sm-12">
				<div class="row">
					<div class="col-sm-12">
						<b>Поведение на уроках:</b>
						<span style="margin-right: 5px">оценка</span>
						<span class="teacher-rating active default" style="margin: 0 !important" ng-show="Report.behavior_grade" ng-class="{
						'active': !empty(Report.behavior_grade),
						'bg-red': Report.behavior_grade <= 3 && !empty(Report.behavior_grade),
						'bg-orange': Report.behavior_grade == 4 && !empty(Report.behavior_grade),
						}">{{Report.behavior_grade}}</span>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						{{Report.behavior_comment}}
					</div>
				</div>
			</div>
		</div>
		<div class="row mb">
			<div class="col-sm-12">
				<div class="row">
					<div class="col-sm-12">
						<b>Способность усваивать новый материал:</b>
						<span style="margin-right: 5px">оценка</span>
						<span class="teacher-rating active default" style="margin: 0 !important" ng-show="Report.material_grade" ng-class="{
						'active': !empty(Report.material_grade),
						'bg-red': Report.material_grade <= 3 && !empty(Report.material_grade),
						'bg-orange': Report.material_grade == 4 && !empty(Report.material_grade),
						}">{{Report.material_grade}}</span>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						{{Report.material_comment}}
					</div>
				</div>
			</div>
		</div>
		<div class="row mb">
			<div class="col-sm-12">
				<div class="row">
					<div class="col-sm-12">
						<b>Выполнение контрольных работ, текущий уровень знаний:</b>
						<span style="margin-right: 5px">оценка</span>
						<span class="teacher-rating active default" style="margin: 0 !important" ng-show="Report.tests_grade" ng-class="{
						'active': !empty(Report.tests_grade),
						'bg-red': Report.tests_grade <= 3 && !empty(Report.tests_grade),
						'bg-orange': Report.tests_grade == 4 && !empty(Report.tests_grade),
						}">{{Report.tests_grade}}</span>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						{{Report.tests_comment}}
					</div>
				</div>
			</div>
		</div>
		<div class="row mb">
			<div class="col-sm-12">
				<div class="row">
					<div class="col-sm-12">
						<b>Рекомендации родителям:</b>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						{{Report.recommendation}}
					</div>
				</div>
			</div>
		</div>
		
		<div class="row mb" style="margin-bottom: 40px">
			<div class="col-sm-12">
				<?= Html::teacherImg('Report.Teacher', 'pull-left', [
						'style' => 'margin-right: 15px'
				]) ?>
				<i>Преподаватель по {{Subjects[Report.id_subject]}}<br>
					{{Report.Teacher.last_name}} {{Report.Teacher.first_name}} {{Report.Teacher.middle_name}}<br>
					Дата составления данного отчета: {{formatDate2(Report.date)}}<br>
				</i>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-12">
				<span class="glyphicon glyphicon-info-sign glyphicon-big"></span>Если у Вас есть вопросы, пожалуйста, звоните по единому номеру ЕГЭ-Центра (495) 646-85-92
			</div>
		</div>
		
	</div>
</div>

<style>
	.row.mb {
		margin-bottom: 20px;
	}
	
	.count-symbols {
		position: absolute;
	    right: 2.5%;
	    bottom: 1%;
	    color: rgba(0, 0, 0, .3);
	}
	.label-text {
		top: -2px;
		position: relative;
	}
	.teacher-rating {
		height: 28px;
	    width: 28px;
	    line-height: 26px;
	    margin-bottom: 12px !important;
	}
</style>