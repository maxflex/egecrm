<div class="panel panel-primary form-change-control" ng-app="Reports" ng-controller="AddCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
		{{Report.id ? 'Редактирование отчета №' + Report.id : 'Добавление отчета'}} по {{Subjects[Report.id_subject]}}
		<?php if (User::fromSession()->type == Teacher::USER_TYPE) :?>
			<span style="margin: 0 7px; display: inline-block; opacity: .1">|</span> {{Report.Student.last_name}} {{Report.Student.first_name}}
		<?php endif ?>
		<div class="pull-right" ng-show="Report.id">
			<span class="link-reverse link-like link-white" ng-click="deleteReport()">удалить отчет</span>
		</div>
	</div>
	<div class="panel-body">
		<?php if (User::fromSession()->type == User::USER_TYPE) :?>
		<div class="row mb">
			<div class="col-sm-6">
				Ученик: <a href="student/{{Report.id_student}}">{{Report.Student.last_name}} {{Report.Student.first_name}} {{Report.Student.middle_name}}</a>
				<br>
				Преподаватель: <a href="teachers/edit/{{Report.id_teacher}}">{{Report.Teacher.last_name}} {{Report.Teacher.first_name}} {{Report.Teacher.middle_name}}</a>
			</div>
		</div>
		<?php endif ?>
		
		
		<?php if (User::fromSession()->type == Teacher::USER_TYPE) :?>
		<div class="row mb">
			<div class="col-sm-12">
				Рекомендации к созданию отчетов:
				<ul>
					<li>пишите конкретно и по делу. Родители плохо переносят воду в отчетах</li>
					<li>старайтесь писать правду. Если у ученика очень низкий уровень знаний по предмету, оставляйте родителям больше рекомендаций, сообщите им, что нужно делать, чтобы достигнуть желаемого результата, старайтесь смягчать углы, чтобы не допустить расторжения договора</li>
					<li>пишите подробнее. В каждой группе прошло от 10 до 20 занятий и родители хотят и заслуживают получить развернутый комментарий</li>
					<li>пишите в свободной манере и от своего имени, будто общаетесь с родителем</li>
					<li>после заполнения этот отчет будет направлен родителям по e-mail, а также будет доступен родителям в личном кабинете ученика</li>
					<li>в идеале отчет по каждому ученику заполняется каждые 8-10 занятий</li>
			</div>
		</div>
		<?php endif ?>
		
		
		<div class="row mb">
			<div class="col-sm-6">
				<b>Выполнение домашнего задания</b>
				<textarea class="teacher-review-textarea form-control" ng-model="Report.homework_comment" maxlength="500"></textarea>
				<span class='count-symbols'>{{countSymbols(Report.homework_comment)}}</span>
			</div>
			<div class="col-sm-6">
				<div class="pull-right">
					<span style="margin-right: 10px">Поставьте оценку:</span>
					<span ng-repeat="n in []| range:5">
						<span class="teacher-rating" ng-click="setGrade('homework_grade', n)" ng-class="{'active': Report.homework_grade == n}">{{n}}</span>
					</span>
				</div>
				<div class="from-them">
					<span class="red">ПИШИТЕ ПОДРОБНЕЕ.</span> Например: выполняет домашние задания регулярно, относится ответственно. Однако, достаточно встретиться нетипичным, но по сути легким заданиям, Алексей теряется и не может их решить. Довольно распространенное явление у 11-классников. Считаю, что в ближайшие 3 месяца сможем общими усилиями устранить этот недостаток.
				</div>
			</div>
		</div>

		
		
		
		<div class="row mb">
			<div class="col-sm-6">
				<b>Работоспособность и активность на уроках</b>
				<textarea class="teacher-review-textarea form-control" ng-model="Report.activity_comment" maxlength="500"></textarea>
				<span class='count-symbols'>{{countSymbols(Report.activity_comment)}}</span>
			</div>
			<div class="col-sm-6">
				<div class="pull-right">
					<span style="margin-right: 10px">Поставьте оценку:</span>
					<span ng-repeat="n in []| range:5">
						<span class="teacher-rating" ng-click="setGrade('activity_grade', n)" ng-class="{'active': Report.activity_grade == n}">{{n}}</span>
					</span>
				</div>
				<div class="from-them">
					<span class="red">ПИШИТЕ ПОДРОБНЕЕ.</span> Например: Алексей работает активно. Даже иногда слишком активно, что раньше мешало остальным ученикам в группе, но сейчас этого не происходит. Хорошо усваивает материал на уроках.
				</div>
			</div>
		</div>


		
		
		<div class="row mb">
			<div class="col-sm-6">
				<b>Поведение на уроках</b>
				<textarea class="teacher-review-textarea form-control" ng-model="Report.behavior_comment" maxlength="500"></textarea>
				<span class='count-symbols'>{{countSymbols(Report.behavior_comment)}}</span>
			</div>
			<div class="col-sm-6">
				<div class="pull-right">
					<span style="margin-right: 10px">Поставьте оценку:</span>
					<span ng-repeat="n in []| range:5">
						<span class="teacher-rating" ng-click="setGrade('behavior_grade', n)" ng-class="{'active': Report.behavior_grade == n}">{{n}}</span>
					</span>
				</div>
				<div class="from-them">
					Например: нормальное, комментарии излишни.
				</div>
			</div>
		</div>
		
		
		<div class="row mb">
			<div class="col-sm-6">
				<b>Способность усваивать новый материал</b>
				<textarea class="teacher-review-textarea form-control" ng-model="Report.material_comment" maxlength="500"></textarea>
				<span class='count-symbols'>{{countSymbols(Report.material_comment)}}</span>
			</div>
			<div class="col-sm-6">
				<div class="pull-right">
					<span style="margin-right: 10px">Поставьте оценку:</span>
					<span ng-repeat="n in []| range:5">
						<span class="teacher-rating" ng-click="setGrade('material_grade', n)" ng-class="{'active': Report.material_grade == n}">{{n}}</span>
					</span>
				</div>
				<div class="from-them">
					<span class="red">ПИШИТЕ ПОДРОБНЕЕ.</span> Например: как уже было указано ранее Леша хорошо усваивает новый материал и ведет себя активно на уроках. Если будет заниматься дома, то очень хорошо напишет ЕГЭ. При такой скорости усвоения материала на уроках удивляет факт неспособности справиться с нестандартными задачами. Но как уже отмечалось, должен научиться справляться с такими ситуациями.
				</div>
			</div>
		</div>
		
		
		<div class="row mb">
			<div class="col-sm-6">
				<b>Выполнение контрольных работ, текущий уровень знаний</b>
				<textarea class="teacher-review-textarea form-control" ng-model="Report.tests_comment" maxlength="500"></textarea>
				<span class='count-symbols'>{{countSymbols(Report.tests_comment)}}</span>
			</div>
			<div class="col-sm-6">
				<div class="pull-right">
					<span style="margin-right: 10px">Поставьте оценку:</span>
					<span ng-repeat="n in []| range:5">
						<span class="teacher-rating" ng-click="setGrade('tests_grade', n)" ng-class="{'active': Report.tests_grade == n}">{{n}}</span>
					</span>
				</div>
				<div class="from-them">
					<span class="red">ПИШИТЕ ПОДРОБНЕЕ.</span> Например: выполняет контрольные работы отлично. Текущий уровень знаний по математике растет очень уверенно. В конце учебного года, что касается именно математики, можно выйти на уровень, требуемый в серьезных вузах, например, МГУ, МГТУ им. Баумана, ГУ-ВШЭ и др.
				</div>
			</div>
		</div>
		
		
		<div class="row mb">
			<div class="col-sm-6">
				<b>Рекомендации родителям</b>
				<textarea class="teacher-review-textarea form-control" ng-model="Report.recommendation" maxlength="500"></textarea>
				<span class='count-symbols'>{{countSymbols(Report.recommendation)}}</span>
			</div>
			<div class="col-sm-6">
				<div class="from-them" style="margin-top: 45px">
					<span class="red">ПИШИТЕ ПОДРОБНЕЕ.</span> Например: какой-либо необходимости контроля или воздействия со стороны родителей не вижу, так как процесс идет отлично
				</div>
			</div>
		</div>
		
		
		<div class="row mb attention">
			<div class="col-sm-6">
				<b>Прогноз баллов на экзамене (информация доступна только администраторам)</b>
				<div class="form-group payment-line">
					от <input class="form-control" ng-model="Report.expected_score_from"> 
					до <input class="form-control" ng-model="Report.expected_score_to"> 
					из возможных <input class="form-control" ng-model="Report.expected_score_total"> баллов
				</div>
			</div>
			<div class="col-sm-6">
				<div class="attention">
					<span>ВНИМАНИЕ:</span> информация по прогнозу баллов будет доступна только администраторам. Родитель или ученик ни в какой форме не будут иметь к ней доступа. Укажите в этом поле наиболее вероятный балл на ЕГЭ или ОГЭ, который по вашему мнению получит этот ученик. Нередко очень сложно давать какие-либо прогнозы, однако в данном отчете это сделать нужно обязательно.
				</div>
			</div>
		</div>
		
		<div class="row mb">
			<div class="col-sm-12">
				<div class="row mb">
					<div class="col-sm-12">
						<div class="label-inside">
							<input class="form-control" readonly value="дата создания отчета –" style="width: 170px">
							<input class="form-control bs-date-top" ng-model="Report.date" style="width: 83px">
						</div>
					</div>
				</div>
				<?php if (User::fromSession()->type == User::USER_TYPE) :?>
				<div class="row mb">
					<div class="col-sm-12">
						<label class="ios7-switch transition-control" style="font-size: 24px; top: 1px">
						    <input type="checkbox" ng-true-value="1" ng-false-value="0" ng-model="Report.available_for_parents">
						    <span class="switch"></span>
						</label> 
						<span class='label-text'>сделать отчет доступным для родителя (если отчет заполнен полностью и верно, передвиньте бегунок)</span>
					</div>
				</div>
				<div class="row mb" ng-show="Report.id">
					<div class="col-sm-12">
						<span ng-show="Report.email">
							<span ng-show="!Report.email_sent">
								Этот отчет никогда не был отправлен родителю на <span class="bold">{{Report.email}} <span ng-click="sendReport()" class="link-like" style="margin-left: 10px">отправить</span></span>
							</span>
							<span ng-show="Report.email_sent">
								Этот отчет был отправлен родителю на <span class="bold">{{Report.email}}</span> {{formatDate(Report.date_sent)}}
							</span>
						</span>
						<span ng-show="!Report.email" class="text-danger">
							<span class="glyphicon glyphicon-exclamation-sign glyphicon-big"></span>Перед отправкой отчета необходимо указать e-mail представителя
						</span>
					</div>
				</div>
				<?php endif ?>
			</div>
		</div>
		
		<div class="row center mb" ng-show="!Report.id">
			<button class="btn btn-primary" ng-click="addReport(false)" ng-disabled="adding">добавить отчёт</button> 
<!-- 			<button class="btn btn-primary" ng-click="addReport(true)" ng-disabled="adding">добавить отчет и отправить родителям по e-mail</button> -->
		</div>
		<div class="row center" ng-show="Report.id">
			<button class="btn btn-primary" ng-click="editReport()" ng-disabled="saving || !form_changed" style="width: 110px">
				<span ng-show="!saving && form_changed">сохранить</span>
				<span ng-show="!saving && !form_changed">сохранено</span>
				<span ng-show="saving">сохранение</span>
			</button>
		</div>		
	</div>
</div>

<style>
	.row.mb {
		margin-bottom: 20px;
	}
	
	.count-symbols {
		position: absolute;
	    right: 4%;
	    bottom: 1%;
	    color: rgba(0, 0, 0, .3);
	}
	.label-text {
		top: -2px;
		position: relative;
	}
	.teacher-rating {
		margin: 0;
	}
	b {
		display: block;
		margin: 10px 0;
	}
	textarea {
		height: 122px !important;
	}
	.from-them {
		width: 100%;
		max-width: 100%;
		margin-top: 5px;
		min-height: 117px;		
	}
	.from-them.red {
		background: #ffb5b5 !important;
	}
	.from-them.red::before {
		border-color: #ffb5b5 !important;
	}
	.from-them.red span {
		color: #c40000;
		font-weight: bold;
	}
	.red {
		color: #c40000;
		font-weight: bold;
	}
	span.bold {
		font-weight: bold;
	}
	.teacher-rating {
	    margin-bottom: 12px !important;
	}
	.attention {
		background-color: #FFDADA !important;
		padding: 5px;
	}
	.attention  span {
		color: #c40000;
		font-weight: bold;
	}
</style>