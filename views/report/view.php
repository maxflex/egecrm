<div class="panel panel-primary form-change-control" ng-app="Reports" ng-controller="AddCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
		Отчёт № {{Report.id}}
	</div>
	<div class="panel-body">
		<div class="row mb">
			<div class="col-sm-12">
				Преподаватель: {{Report.Teacher.last_name}} {{Report.Teacher.first_name}} {{Report.Teacher.middle_name}}

			</div>
		</div>
		<div class="row mb">
			<div class="col-sm-12">
				Дата: {{Report.date}}
			</div>
		</div>
		<div class="row mb">
			<div class="col-sm-12">
				<div class="row">
					<div class="col-sm-12">
						<h4>Выполнение домашнего задания</h4>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<span style="margin-right: 5px">Оценка:</span>
						<span class="teacher-rating active default" ng-show="Report.activity_grade">{{Report.activity_grade}}</span>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						Комментарий: <i class="half-black">{{Report.activity_comment}}</i>
					</div>
				</div>
			</div>
		</div>
		
		<div class="row mb">
			<div class="col-sm-12">
				<div class="row">
					<div class="col-sm-12">
						<h4>Работоспособность и активность на уроках</h4>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<span style="margin-right: 5px">Оценка:</span>
						<span class="teacher-rating active default" ng-show="Report.homework_grade">{{Report.homework_grade}}</span>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						Комментарий: <i class="half-black">{{Report.homework_comment}}</i>
					</div>
				</div>
			</div>
		</div>
		
		
		<div class="row mb">
			<div class="col-sm-12">
				<div class="row">
					<div class="col-sm-12">
						<h4>Поведение на уроках</h4>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<span style="margin-right: 5px">Оценка:</span>
						<span class="teacher-rating active default" ng-show="Report.behavior_grade">{{Report.behavior_grade}}</span>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						Комментарий: <i class="half-black">{{Report.behavior_comment}}</i>
					</div>
				</div>
			</div>
		</div>
		
		<div class="row mb">
			<div class="col-sm-12">
				<div class="row">
					<div class="col-sm-12">
						<h4>Способность усваивать новый материал</h4>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<span style="margin-right: 5px">Оценка:</span>
						<span class="teacher-rating active default" ng-show="Report.material_grade">{{Report.material_grade}}</span>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						Комментарий: <i class="half-black">{{Report.material_comment}}</i>
					</div>
				</div>
			</div>
		</div>
		
		<div class="row mb">
			<div class="col-sm-12">
				<div class="row">
					<div class="col-sm-12">
						<h4>Выполнение контрольных работ, текущий уровень знаний</h4>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<span style="margin-right: 5px">Оценка:</span>
						<span class="teacher-rating active default" ng-show="Report.tests_grade">{{Report.tests_grade}}</span>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						Комментарий: <i class="half-black">{{Report.tests_comment}}</i>
					</div>
				</div>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-12">
				<div class="row">
					<div class="col-sm-12">
						<h4>Рекомендации родителям</h4>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						Комментарий: <i class="half-black">{{Report.recommendation}}</i>
					</div>
				</div>
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