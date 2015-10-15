<form id="teacher-edit" ng-app="Teacher" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>" autocomplete='off'>
	<?= partial("freetime") ?>
	<div class="row">
		<div class="col-sm-3">
			<div class="form-group">
				<input class="form-control" ng-model="Teacher.last_name" placeholder="фамилия">
			</div>
			<div class="form-group">
				<input class="form-control" ng-model="Teacher.first_name" placeholder="имя">
			</div>
			<div class="form-group">
				<input class="form-control" ng-model="Teacher.middle_name" placeholder="отчество">
			</div>
		</div>
		<div class="col-sm-3">
			<div class="form-group">
				<input placeholder="логин" ng-model="Teacher.login" class="form-control">
			</div>
			<div class="form-group">
				<input placeholder="пароль" type="text" ng-model="Teacher.password" class="form-control">
			</div>
			<div class="form-group">
				<input placeholder="email" ng-model="Teacher.email" class="form-control">
			</div>
		</div>
		<div class="col-sm-3">
			<?= Html::phones('teacher') ?>
		</div>
		<div class="col-sm-3">
			<div class="form-group">
				<?= Subjects::buildMultiSelector($Teacher->subjects, ["id" => "subjects-select"]) ?>
			</div>
			<div class="form-group">
				<input placeholder="оценка эксперта" ng-model="Teacher.expert_mark" class="form-control">
			</div>
			<div class="form-group">
				<div class="input-group">
					<input placeholder="ID в базе" ng-model="Teacher.id_a_pers" class="form-control digits-only">
					<span class="input-group-btn">
			        	<button class="btn btn-default" type="button" ng-disabled="!Teacher.id_a_pers" ng-click="goToTutor()">
			        		<span class="glyphicon glyphicon-user no-margin-right"></span>
			        	</button>
					</span>
				</div>
			</div>
		</div>
	</div>
	<div class="row" style="margin-bottom: 10px">
		<div class="col-sm-9">
			<?php if ($Teacher->id) :?>
	        	<?= Branches::buildMultiSelector($Teacher->branches, ["id" => "teacher-branches"]) ?>
			<?php else :?>
	            <?= Branches::buildSvgSelector($Teacher->branches, [
		            "ng-model" => "Teacher.branches",
		            "id" => "teacher-branches",
		        ], true) ?>
		    <?php endif ?>
		</div>
		<div class="col-sm-3">
			<input ng-model="Teacher.rubbles" placeholder="кол-во рублей за занятие" class="form-control">
		</div>
	</div>
	<div class="row" style="margin-bottom: 10px">
		<div class="col-sm-12">
			<textarea class="form-control" ng-model="Teacher.comment" rows="4"></textarea>
		</div>
	</div>
	
	<?php if ($Teacher->id) :?>
	<div class="row" style="margin-bottom: 10px" ng-hide="Teacher.branches.length == 0 || Teacher.branches[0] == ''">
		<div class="col-sm-3">
		    <h4 style="margin-top: 0" class="row-header">Свободное время</h4>
		    <div ng-repeat="id_branch in Teacher.branches">
			    <span ng-bind-html="branches_brick[id_branch] | to_trusted" style="width: 50px; display: inline-block"></span>
			    <span ng-repeat="weekday in weekdays" class="group-freetime-block">
					<span class="freetime-bar" ng-repeat="time in weekday.schedule track by $index" 
						ng-class="{
							'empty': !inFreetime2(time, freetime[id_branch][$parent.$index + 1])
						}" ng-hide="time == ''" style="position: relative; top: 3px">
					</span>
				</span>
		    </div>
		    
			<div ng-show="Teacher.schedule_date" class="small" style="margin-top: 13px">актуальность: {{Teacher.schedule_date}}</div>
	        <div style="margin-top: 5px">
	            <span class="link-like link-reverse small" ng-click="openFreetime()" 
	            	style="margin-left: 0" ng-hide="!Teacher.branches[0]">редактировать</span>
	        </div>
	    </div>
		<div class="col-sm-9">
			<div class="pull-right">
				Входов: <?= User::getLoginCount($Teacher->id, Teacher::USER_TYPE) ?>
			</div>
		</div>
	</div>
	<div class="row" style="margin-bottom: 10px">
		<div class="col-sm-12">
		    <h4 style="margin-top: 0" class="row-header">{{(Groups && Groups.length > 0) ? 'ГРУППЫ' : 'НЕТ ГРУПП'}}</h4>
		    <?= globalPartial("groups_list") ?>
		</div>
	</div>
	
	<div class="row" style="margin-bottom: 10px">
		<div class="col-sm-12">
		    <h4 style="margin-top: 0" class="row-header">{{(Teacher.Reviews && Teacher.Reviews.length > 0) ? 'ОТЗЫВЫ' : 'НЕТ ОТЗЫВОВ'}}</h4>
			
			<div class="row">
				<div class="col-sm-12">	
					<div ng-repeat="Review in Teacher.Reviews" class="clear-sms" style="margin-left: 11px">
						<div class="from-them">
							<span>{{Review.comment}}</span>
							<div style="text-align: right; margin-top: 5px" class="save-coordinates">
								<a href="student/{{Review.Student.id}}" target="_blank">
									{{Review.Student.last_name}} {{Review.Student.first_name}}</a>, {{coordinate_time(Review.date)}}<br>
								Оценка: <b>{{Review.rating}}</b>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>	
	</div>
	
	
	
		
	<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
	<div class="lightbox-new lightbox-addpayment" style="width: 551px; left: calc(50% - 275px)">
		<h4>{{new_payment.id ? "Редактировать" : "Добавить"}} платеж</h4>
		<div class="form-group payment-line">
			<div class="form-group inline-block">
				<?= Payment::buildSelector(false, false, ["ng-model" => "new_payment.id_status", "style" => "width: 180px"]) ?>
		    </div>
			<div class="form-group inline-block">
				на сумму
		    </div>
			<div class="form-group inline-block">
				<input type="text" class="form-control digits-only" id="payment-sum" ng-model="new_payment.sum"  ng-keydown="watchEnter($event)"> от
			</div>
			<div class="form-group inline-block">
				<input class="form-control bs-date" id="payment-date" ng-model="new_payment.date">
			</div>
		</div>
		<div class="form-group payment-inline" ng-show="new_payment.id_status == <?= Payment::PAID_CARD ?>">
			<h4>Номер карты</h4>
			<div class="form-group inline-block">
				<input class="form-control" disabled placeholder="XXXX" style="width: 60px; display: inline-block; margin-left: 5px"> - 
				<input class="form-control" disabled placeholder="XXXX" style="width: 60px; display: inline-block"> - 
				<input class="form-control" disabled placeholder="XXXX" style="width: 60px; display: inline-block"> - 
				<input class="form-control digits-only" maxlength="4" ng-model="new_payment.card_number" 
					style="width: 60px; display: inline-block">
			</div>
		</div>
		<center>
			<button class="btn btn-primary" ng-click="addPayment()">{{new_payment.id ? "Редактировать" : "Добавить"}}</button>
		</center>
	</div>
	<!-- /ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
		
	<div class="row" style="position: relative">
		<div class="col-sm-12">
			<h4 class="row-header">
				<span ng-show="Data.length">ЗАНЯТИЯ</span>
				<span ng-show="!Data.length">НЕТ ЗАНЯТИЙ</span>
			</h4>
			<table class="table table-divlike">
				<tr ng-repeat="d in Data">
					<td>
						<a href="groups/edit/{{d.id_group}}">Группа №{{d.id_group}}</a>
					</td>
					<td>
						{{formatDate(d.lesson_date)}}
					</td>
					<td>
						{{formatTime(d.lesson_time)}}
					</td>
					<td>
						{{d.teacher_price | number}} рублей
					</td>
				</tr>
			</table>

		</div>
	</div>
	
	<div class="row">
		<div class="col-sm-12">
			<h4 class="row-header">ПЛАТЕЖИ
			    <a class="link-like link-reverse link-in-h" ng-click="addPaymentDialog()">добавить</a>
		    </h4>
		    <div class="form-group payment-line">
				<div ng-repeat="payment in payments | reverse" style="margin-bottom: 5px"> 
					<span class="label label-success" ng-class="{'label-danger' : payment.id_status == <?= Payment::NOT_PAID_BILL ?>}">
					{{payment_statuses[payment.id_status]}}<span ng-show="payment.id_status == <?= Payment::PAID_CARD ?>">{{payment.card_number ? " *" + payment.card_number.trim() : ""}}</span></span>
					
					<span class="capitalize">{{payment_types[payment.id_type]}}</span>
					Платеж на сумму {{payment.sum}} <ng-pluralize count="payment.sum" when="{
						'one' : 'рубль',
						'few' : 'рубля',
						'many': 'рублей',
					}"></ng-pluralize> от {{payment.date}}
						<span class="save-coordinates">({{payment.user_login}} {{formatDate2(payment.first_save_date) | date:'yyyy.MM.dd в HH:mm'}})
						</span>
						<a class="link-like link-reverse small" ng-click="confirmPayment(payment)" ng-show="!payment.confirmed">подтвердить</a>
						<span class="label pointer label-success" ng-show="payment.confirmed" ng-click="confirmPayment(payment)">подтвержден</span>
						<a class="link-like link-reverse small" ng-click="editPayment(payment)">редактировать</a>
						<a class="link-like link-reverse small" ng-click="deletePayment($index, payment)">удалить</a>
				</div>
		    </div>
		</div>
	</div>
	
	
	
	<?php endif ?>
	<div class="row" style="margin-top: 10px">
		<div class="col-sm-12 center">
	    	<button class="btn btn-primary save-button" ng-disabled="saving || !form_changed" ng-hide="!Teacher.id" style="width: 100px">
	    		<span ng-show="form_changed">Сохранить</span>
	    		<span ng-show="!form_changed && !saving">Сохранено</span>
	    	</button>
	    	
	    	<button class="btn btn-primary save-button" ng-hide="Teacher.id" style="width: 100px">
				добавить
	    	</button>
	    	
		</div>
	</div>
</form>