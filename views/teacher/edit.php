<form id="teacher-edit" ng-app="Teacher" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>" autocomplete='off'>
	<?= partial("freetime") ?>
	<div class="row">
		<div class="col-sm-3" style="width: 13%">
			<div class='tutor-img-new'>
				<img src="{{Teacher.has_photo ? 'https://lk.a-perspektiva.ru/img/tutors/' + Teacher.id + '.' + Teacher.photo_extension : 'img/teachers/no-profile-img.gif'}}">
			</div>
		</div>
		<div class="col-sm-3" style="width: 20%">
			<div class="form-group">
				<input class="form-control" ng-model="Teacher.last_name" placeholder="фамилия" disabled>
			</div>
			<div class="form-group">
				<input class="form-control" ng-model="Teacher.first_name" placeholder="имя" disabled>
			</div>
			<div class="form-group">
				<input class="form-control" ng-model="Teacher.middle_name" placeholder="отчество" disabled>
			</div>
			<div class="form-group">
				<select class="form-control" ng-model="Teacher.gender" placeholder="пол" disabled>
					<option value='male'>мужской</option>
					<option value='female'>женский</option>
				</select>
			</div>
			<div class="form-group" style="position: relative">
				<input class="form-control" ng-model="Teacher.birth_year" placeholder="год рождения" disabled>
				<span class="inside-input">– {{ yearDifference(Teacher.birth_year) }} <ng-pluralize count="yearDifference(Teacher.birth_year)" when="{
                    'one': 'год',
                    'few': 'года',
                    'many': 'лет',
                }">лет</ng-pluralize></span>
			</div>
			<div class="form-group" style="position: relative">
				<input class="form-control" ng-model="Teacher.start_career_year" placeholder="стаж" disabled>
				<span class="inside-input">– стаж {{ yearDifference(Teacher.start_career_year) }} <ng-pluralize count="yearDifference(Teacher.start_career_year)" when="{
                    'one': 'год',
                    'few': 'года',
                    'many': 'лет',
                }">лет</ng-pluralize></span>
			</div>
			<div class="form-group">
				<?= Subjects::buildMultiSelector($Teacher->subjects, ["id" => "subjects-select", 'disabled' => 'disabled'], 'three_letters') ?>
			</div>
			<div class="form-group">
				<?= Grades::buildMultiSelector($Teacher->grades, ["id" => "public-grades", 'disabled' => 'disabled']) ?>
			</div>
		</div>
		<div class="col-sm-3" style="width: 46%">




<div class="form-group">
    <div class="input-group" 
        ng-class="{'input-group-with-hidden-span' : !phoneCorrect('teacher-phone') || (!isMobilePhone('teacher-phone') && teacher_phone_level >= 2) }">
    	<input ng-keyup id="teacher-phone" type="text" disabled
    		placeholder="телефон" class="form-control phone-masked"  ng-model="Teacher.phone">
    		
    	<div class='comment-inside-input'>
			<span class="glyphicon glyphicon-pencil text-gray" ng-show='!Teacher.phone_comment'></span>
			<input type="text" class='no-border-outline phone-comment' ng-model='Teacher.phone_comment' disabled="">
    	</div>
    	
    	<div class="input-group-btn">
	    	<button class="btn btn-default" ng-show="phoneCorrect('teacher-phone')" ng-click="callSip('teacher-phone')" ng-class="{
					'addon-bordered' : teacher_phone_level >= 2  && !isMobilePhone('teacher-phone')
				}">
				<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
			</button>
			<button ng-show="phoneCorrect('teacher-phone') && isMobilePhone('teacher-phone')" ng-class="{
					'addon-bordered' : teacher_phone_level >= 2 || !phoneCorrect('teacher-phone')
				}" class="btn btn-default" type="button" onclick="smsDialog('teacher-phone')">
					<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
			</button>
	    	<button disabled ng-hide="teacher_phone_level >= 2 || !phoneCorrect('teacher-phone')" class="btn btn-default" style='background: #EEE; color: black' type="button" ng-click="teacher_phone_level = teacher_phone_level + 1">
	    		<span class="glyphicon glyphicon-plus no-margin-right small"></span>
	    	</button>
        </div>
	</div>
</div>
<div class="form-group" ng-show="teacher_phone_level >= 2">
    <div class="input-group" 
        ng-class="{'input-group-with-hidden-span' : !phoneCorrect('teacher-phone-2')  || (!isMobilePhone('teacher-phone') && teacher_phone_level >= 3) }">
    	<input ng-keyup id="teacher-phone-2" type="text" disabled
    		placeholder="телефон 2" class="form-control phone-masked" ng-model="Teacher.phone2">
    		
    	<div class='comment-inside-input'>
			<span class="glyphicon glyphicon-pencil text-gray" ng-show='!Teacher.phone2_comment'></span>
			<input type="text" class='no-border-outline phone-comment' ng-model='Teacher.phone2_comment' disabled="">
    	</div>

			
    	<div class="input-group-btn">
    		<button class="btn btn-default" ng-show="phoneCorrect('teacher-phone-2')" ng-click="callSip('teacher-phone-2')"  ng-class="{
					'addon-bordered' : teacher_phone_level >= 3  && !isMobilePhone('teacher-phone-2')
				}">
				<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
			</button>
			<button ng-show="phoneCorrect('teacher-phone-2') && isMobilePhone('teacher-phone-2')" ng-class="{
					'addon-bordered' : teacher_phone_level >= 3 || !phoneCorrect('teacher-phone-2')
				}" class="btn btn-default" type="button"  onclick="smsDialog('teacher-phone-2')">
					<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
			</button>
        	<button style='background: #EEE; color: black' disabled ng-hide="teacher_phone_level >= 3 || !phoneCorrect('teacher-phone-2')" class="btn btn-default" type="button" ng-click="teacher_phone_level = teacher_phone_level + 1">
        		<span class="glyphicon glyphicon-plus no-margin-right small"></span>
        	</button>
        </div>
	</div>
</div>
<div class="form-group" ng-show="teacher_phone_level >= 3">
	<div class="input-group" 
		ng-class="{'input-group-with-hidden-span' : !phoneCorrect('teacher-phone-3')  || !isMobilePhone('teacher-phone-3') }">
        <input type="text" id="teacher-phone-3" placeholder="телефон 3"  disabled
        	class="form-control phone-masked" ng-model="Teacher.phone3">
        	
        	<div class='comment-inside-input'>
				<span class="glyphicon glyphicon-pencil text-gray" ng-show='!Teacher.phone3_comment'></span>
				<input type="text" class='no-border-outline phone-comment' ng-model='Teacher.phone3_comment' disabled="">
	    	</div>

        	
        	<div class="input-group-btn">
	        	<button class="btn btn-default" ng-show="phoneCorrect('teacher-phone-3')" ng-click="callSip('teacher-phone-3')"  ng-class="{
					'addon-bordered' : !isMobilePhone('teacher-phone-3')
				}">
					<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
				</button>
				<button style='background: #EEE; color: black' disabled ng-show="phoneCorrect('teacher-phone-3') && isMobilePhone('teacher-phone-3')" ng-class="{
						!phoneCorrect('teacher-phone-3')
					}" class="btn btn-default" type="button"  onclick="smsDialog('teacher-phone-3')">
						<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
				</button>
            </div>
	</div>
</div>
            <div class="input-group fakeInput">
                <input placeholder="email" ng-model="Teacher.email" disabled class="form-control no-border-outline">

                <div class='comment-inside-input'>
                    <span class="glyphicon glyphicon-pencil text-gray" ng-show='!Teacher.email_comment'></span>
                    <input type="text" class='no-border-outline phone-comment' ng-model='Teacher.email_comment' disabled="">
                </div>


                <div class="input-group-btn" style="box-sizing: border-box;">
                    <button class="btn btn-default" type="button" ng-disabled="!Teacher.email" ng-click="emailDialog(Teacher.email)">
                        <span class="glyphicon glyphicon-envelope no-margin-right small" ></span>
                    </button>
                </div>
            </div>


        </div>
		<div class="col-sm-3"  style="width: 20%">
			<div class="form-group">
				<select class="form-control" ng-model="Teacher.in_egecentr" placeholder="пол" disabled>
					<option value='0'>не работает в ЕГЭ-Центре</option>
					<option value='1'>работает в ЕГЭ-Центре</option>
				</select>
			</div>
			
			<div class="form-group">
				<div class="input-group">
			      <input placeholder="логин" disabled ng-model="Teacher.login" class="form-control">
			      <span class="input-group-addon pointer" ng-click="toggleBanned()">
			      	<span class="glyphicon glyphicon-lock no-margin-right small" ng-class="{
				      	'text-danger': Teacher.banned
			      	}"></span>
<!-- 			        <input type="checkbox" aria-label="заблокирован"> -->
			      </span>
			    </div>
			</div>
			<div class="form-group">
				<input placeholder="пароль" disabled type="text" ng-model="Teacher.password" class="form-control">
			</div>
			
			<div class="form-group">
				<?= Branches::buildMultiSelector($Teacher->branches, ["id" => "teacher-branches", 'disabled' => 'disabled']) ?>
			</div>
		</div>
<!--
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
-->
	</div>

	<!-- /Публичная информация -->
	<?php if ($Teacher->id) :?>
	<div class="row" style="margin-bottom: 10px">
		<div class="col-sm-12">
		    <h4 style="margin-top: 0" class="row-header" ng-model="group_collapsed" ng-click="group_collapsed = Groups.length && !group_collapsed">{{(Groups && Groups.length > 0) ? 'ГРУППЫ' : 'НЕТ ГРУПП'}}</h4>
		    <div ng-show="group_collapsed">
                <?= globalPartial("groups_list") ?>
            </div>
		</div>
	</div>

	<div class="row" style="margin-bottom: 10px">
		<div class="col-sm-12">
		    <h4 style="margin-top: 0" class="row-header" ng-model="review_collapsed" ng-click="review_collapsed = Teacher.Reviews.length && !review_collapsed">
                {{(Teacher.Reviews && Teacher.Reviews.length > 0) ? 'ОТЗЫВЫ' : 'НЕТ ОТЗЫВОВ'}}
            </h4>

			<div class="row"  ng-show="review_collapsed">
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
	<div id="addpayment" class="lightbox-new lightbox-addpayment" style="width: 551px; left: calc(50% - 275px)">
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
                <input class="form-control bs-date" id="payment-date" ng-model="new_payment.date" pattern="[0-9]{2}.[0-9]{2}.[0-9]{4}">
			</div>
		</div>
        <script>
            $("#payment-date").inputmask("99.99.9999");
        </script>
		<div class="form-group payment-inline" ng-show="new_payment.id_status == <?= Payment::PAID_CARD ?>">
			<h4>Номер карты</h4>
			<div class="form-group inline-block">
				<input class="form-control" disabled placeholder="XXXX" style="width: 60px; display: inline-block; margin-left: 5px"> -
				<input class="form-control" disabled placeholder="XXXX" style="width: 60px; display: inline-block"> -
				<input class="form-control" disabled placeholder="XXXX" style="width: 60px; display: inline-block"> -
				<input class="form-control digits-only" id="payment-card" maxlength="4" ng-model="new_payment.card_number"
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
			<h4 class="row-header" style="margin-top: 0">
				<span ng-show="Data.length" ng-model="data_collapsed" ng-click="data_collapsed = !data_collapsed">ЗАНЯТИЯ</span>
				<span ng-show="!Data.length">НЕТ ЗАНЯТИЙ</span>
			</h4>
			<table class="table table-divlike" ng-show="data_collapsed">
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
				<tr>
					<td colspan="2"></td>
					<td><b>к выплате</b></td>
					<td><b>{{toBePaid() | number}} рублей</b></td>
				</tr>
			</table>

		</div>
	</div>

	<div class="row">
		<div class="col-sm-12">
			<h4 class="row-header" ng-model="payment_collapsed" ng-click="payment_collapsed = !payment_collapsed">ПЛАТЕЖИ
			    <a class="link-like link-reverse link-in-h" ng-click="addPaymentDialog()">добавить</a>
		    </h4>
		    <div class="form-group payment-line" ng-show="payment_collapsed">
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

	<?= partial('reports') ?>



	<?php endif ?>
</form>
