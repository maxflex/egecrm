<div id="panel-loading">Загрузка...</div>
<form id="request-edit" ng-app="Request" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>" autocomplete='off'>
	
	<!-- ЛАЙТБОКС РЕДАКТИРОВАНИЕ ПЕЧАТИ ДОГОВОРА ВРУЧНУЮ -->
		<div class="lightbox-new lightbox-manualedit">
			<h4 style="margin-bottom: 20px">РЕДАКТИРОВАНИЕ ДОГОВОРА</h4>
			<div class="row">
				<textarea id="contract-manual-edit"></textarea>
				<div class="display-none" id="contract-manual-div"></div>
			<center style="margin-top: 10px">
				<button class="btn btn-primary ajax-payment-button" ng-click="runPrintManual()">Печать</button>
			</center>
			</div>
		</div>
		<!-- /ЛАЙТБОКС РЕДАКТИРОВАНИЕ ПЕЧАТИ ДОГОВОРА ВРУЧНУЮ -->
		
		<?= partial('contract_edit') ?>
		
		<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
		<div class="lightbox-new lightbox-addpayment">
			<h4>{{new_payment.id ? "Редактировать" : "Добавить"}} платеж</h4>
			<div class="form-group payment-line">
				<div class="form-group inline-block">
					<?= Payment::buildSelector(false, false, ["ng-model" => "new_payment.id_status", "style" => "width: 180px"]) ?>
			    </div>
				<div class="form-group inline-block">
					<?= PaymentTypes::buildSelector(false, false, ["ng-model" => "new_payment.id_type"]) ?> на сумму
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
				<button class="btn btn-primary ajax-payment-button" ng-click="addPayment()">{{new_payment.id ? "Редактировать" : "Добавить"}}</button>
			</center>
		</div>
		<!-- /ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->

		<!-- ЛАЙТБОКС ВЫБОР ПОЛЬЗОВАТЕЛЯ ДЛЯ ПЕЧАТИ ДОГОВОРА -->
		<div class="lightbox-new lightbox-print">
			<h4>Выберите пользователя</h4>
					<select class="form-control" id="user-print-select" ng-model="id_user_print">
						<option selected="" value="">пользователь</option>
						<option disabled="" value="">──────────────</option>
						<option value="0">без менеджера</option>
						<option ng-repeat="user in users" ng-value="user.id" ng-hide="!user.agreement">{{user.login}}</option>
					</select>
			<center style="margin-top: 20px">
				<button class="btn btn-primary" ng-click="editBeforePrint()" ng-disabled="!id_user_print" style="width: 140px">Редактировать</button>
				<button class="btn btn-primary" ng-click="runPrint()" ng-disabled="id_user_print == ''" style="width: 140px">Печать</button>
			</center>
		</div>
		<!-- /ЛАЙТБОКС ВЫБОР ПОЛЬЗОВАТЕЛЯ ДЛЯ ПЕЧАТИ ДОГОВОРА -->
		
		<!-- ЛАЙТБОКС КАРТА -->
		<div class="lightbox-element lightbox-map">
			<map zoom="10" disable-default-u-i="true" scale-control="true" zoom-control="true" zoom-control-options="{style:'SMALL'}">
				<transit-layer></transit-layer>
				<custom-control position="TOP_RIGHT" index="1">
				<div class="input-group gmap-search-control">
		          <input type="text" id="map-search" class="form-control" ng-keyup="gmapsSearch($event)" placeholder="Поиск...">
		          <span class="input-group-btn">
				    <button class="btn btn-default" ng-click="gmapsSearch($event)">
				    <span class="glyphicon glyphicon-search no-margin-right"></span>
				    </button>
				  </span>
				</div>
		        </custom-control>
			</map>
			<button class="btn btn-default map-save-button" ng-click="saveMarkersToServer()">Сохранить</button>
		</div>
		<!-- КОНЕЦ /КАРТА И ЛАЙТБОКС -->
	
	<div class="panel panel-primary panel-edit" ng-show="show_request_panel">
		<div class="panel-heading">
			 
			<?php if ($Request->adding) :?>
			Добавление заявки
			<?php else :?>
			Редактирование заявки №<?= $Request->id ?>
			<?php endif ?>
			
			
			<div class="pull-right">
				
				<span class="link-reverse pointer" ng-click="toggleMinimizeStudent()">{{student.minimized ? "развернуть" : "свернуть"}}</span>
				
				<?php if (!$Request->adding) :?>
					<span class="link-reverse pointer" style="margin-left: 10px" onclick="lightBoxShow('glue')">перенести в другой профиль</span>
					
					<?php if ($Request->getDuplicates()): ?>
						<span class="link-reverse pointer" style="margin-left: 10px" onclick='deleteRequest(<?= $Request->id ?>)'>удалить заявку</span>
					<?php endif ?>
				<?php endif ?>
			</div>
		</div>
		<div class="panel-body">
			
<!-- 	<img src="img/svg/loading-bars.svg" alt="Загрузка страницы..." id="svg-loading"> -->
		<!-- СКЛЕЙКА КЛИЕНТОВ -->
		<div class="lightbox-new lightbox-glue">
			<div style="height: 75px">
				<h4>Перенести в другой профиль</h4>
			    <input id="id-student-glue" type="text" class="form-control" placeholder="ID ученика" ng-model="id_student_glue" ng-change="findStudent()">
			</div>
			<center>
				<span ng-show="request_duplicates.length > 1">
					<button class="btn btn-primary" type="button" ng-disabled="!GlueStudent" ng-click="glue(0)" id="save-glue-button">перенести</button>		
				</span>
				<span ng-show="request_duplicates.length <= 1">
					<button class="btn btn-primary" type="button" ng-disabled="!GlueStudent" ng-click="glue(1)">перенести с удалением ученика</button>
					<button class="btn btn-primary" type="button" ng-disabled="!GlueStudent" ng-click="glue(0)">скопировать заявку в указанного ученика</button>
				</span>
			</center>
		</div>
		<!-- /СКЛЕЙКА КЛИЕНТОВ -->
		
	<!-- Скрытые поля -->
	<input type="hidden" name="id_request" value="<?= $Request->id ?>">

	<!-- если нажата сохранить, то всегда обнулять  adding -->
	<input type="hidden" name="Request[adding]" value="0">
	<input type="hidden" name="Request[id_student]" id="id_student_force" value="<?= $Request->id_student ?>">

	<input type="hidden" id="subjects_json" name="subjects_json">
	<input type="hidden" id="payments_json" name="payments_json">

	<input type="hidden" ng-value="markerData() | json"  name="marker_data">
	<!-- Конец /скрытые поля -->


	<!-- ВКЛАДКИ ЗАЯВОК -->
	<div class="row" style="margin-bottom: 20px" ng-hide="<?= ($Request->adding && !$_GET["id_student"]) ?>">
		<div class="col-sm-12">
			<ul class="nav nav-tabs">
			<li ng-repeat="request_duplicate in request_duplicates" ng-class="{'active' : request_duplicate == <?= $Request->id ?>}">
				<a href="requests/edit/{{request_duplicate}}" ng-class="{'half-opacity-color': request_duplicate_comments[request_duplicate] == false}">
					Заявка #{{request_duplicate}}
				</a>
			</li>
			<li ng-class="{'active' : <?= ($Request->adding && $Request->id_student) ?>}">
				<a href="requests/add?id_student=<?= $Request->id_student ?>">
					добавить заявку
				</a>
			</li>
		</ul>
		</div>
	</div>
	<!-- /ВКЛАДКИ ЗАЯВОК -->

	<!-- ДАННЫЕ ПО ЗАЯВКЕ С САЙТА И УВЕДОМЛЕНИЯ -->
	<div class="row page-title">
		<div class="col-sm-12">
			<h4>Данные по заявке

				<span ng-hide="<?= $Request->adding ?>">
					<span class="hint--right" data-hint="Время создания: <?= dateFormat($Request->date) ?>">
						<span class="glyphicon glyphicon-info-sign opacity-pointer no-margin-right" style="font-size: 14px; cursor: default"></span>
					</span>
<!--
					<span class="link-like link-reverse link-in-h" onclick="lightBoxShow('glue')">
						перенести в другой профиль</span>
-->
<!--
					<span class="link-like link-reverse link-in-h" ng-show="request_duplicates.length > 1" onclick='deleteRequest(<?= $Request->id ?>)'>
						удалить заявку
					</span>
-->
				</span>
			</h4>
		</div>
	</div>


    <div class="row">
        <div class="col-sm-9">
            <div class="row">
               <?= Subjects::buildColSelector($Request->subjects, "Request[subjects]") ?>
                <div class="col-sm-4">
                    <div class="form-group">
                        <?= Grades::buildSelector($Request->grade, "Request[grade]") ?>
                    </div>

                    <div class="form-group">
                        <input placeholder="имя" class="form-control" name="Request[name]" value="<?= $Request->name ?>">
                    </div>

                    <div class="form-group">
						
						<div class="form-group">
				            <div class="input-group" 
					            ng-class="{'input-group-with-hidden-span' : !phoneCorrect('request-phone') || (!isMobilePhone('request-phone') && request_phone_level >= 2) }">
			                	<input ng-keyup id="request-phone" type="text"
			                		placeholder="телефон" class="form-control phone-masked"  name="Request[phone]" value="<?= $Request->phone ?>">
			                	<div class="input-group-btn">
			                				<button class="btn btn-default" ng-show="phoneCorrect('request-phone') && isMobilePhone('request-phone')" ng-click="callSip('request-phone')">
				                				<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
			                				</button>
											<button  ng-show="phoneCorrect('request-phone') && isMobilePhone('request-phone')" ng-class="{
													'addon-bordered' : request_phone_level >= 2 || !phoneCorrect('request-phone')
												}" class="btn btn-default" type="button" onclick="smsDialog('request-phone')">
													<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
											</button>
								        	<button ng-hide="request_phone_level >= 2 || !phoneCorrect('request-phone')" class="btn btn-default" type="button" ng-click="request_phone_level = request_phone_level + 1">
								        		<span class="glyphicon glyphicon-plus no-margin-right small"></span>
								        	</button>
						            </div>
							</div>
						</div>
						
						<div class="form-group" ng-show="request_phone_level >= 2">
				            <div class="input-group" 
					            ng-class="{'input-group-with-hidden-span' : !phoneCorrect('request-phone-2')  || (!isMobilePhone('request-phone') && request_phone_level >= 3) }">
			                	<input ng-keyup id="request-phone-2" type="text"
			                		placeholder="телефон 2" class="form-control phone-masked"  name="Request[phone2]" value="<?= $Request->phone2 ?>">
			                	<div class="input-group-btn">
				                	<button class="btn btn-default" ng-show="phoneCorrect('request-phone-2') && isMobilePhone('request-phone-2')" ng-click="callSip('request-phone-2')">
		                				<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
	                				</button>
									<button ng-show="phoneCorrect('request-phone-2') && isMobilePhone('request-phone-2')" ng-class="{
											'addon-bordered' : request_phone_level >= 3 || !phoneCorrect('request-phone-2')
										}" class="btn btn-default" type="button"  onclick="smsDialog('request-phone-2')">
											<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
									</button>
						        	<button ng-hide="request_phone_level >= 3 || !phoneCorrect('request-phone-2')" class="btn btn-default" type="button" ng-click="request_phone_level = request_phone_level + 1">
						        		<span class="glyphicon glyphicon-plus no-margin-right small"></span>
						        	</button>
					            </div>
							</div>
						</div>
						
						
						<div class="form-group" ng-show="request_phone_level >= 3">
							<div class="input-group" 
								ng-class="{'input-group-with-hidden-span' : !phoneCorrect('request-phone-3')  || !isMobilePhone('request-phone-3') }">
				                <input type="text" id="request-phone-3" placeholder="телефон 3" 
				                	class="form-control phone-masked"  name="Request[phone3]" value="<?= $Request->phone3 ?>">
				                	<div class="input-group-btn">
					                	<button class="btn btn-default" ng-show="phoneCorrect('request-phone-3') && isMobilePhone('request-phone-3')" ng-click="callSip('request-phone-3')">
						                	<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
		                				</button>
										<button ng-show="phoneCorrect('request-phone-3') && isMobilePhone('request-phone-3')" ng-class="{
												!phoneCorrect('request-phone-3')
											}" class="btn btn-default" type="button"  onclick="smsDialog('request-phone-3')">
												<span class="glyphicon glyphicon-envelope no-margin-right" style="font-size: 12px"></span>
										</button>
						            </div>
							</div>
			            </div>



                    </div>

                    <div class="form-group">
<!--                         <?= Branches::buildSvgSelector($Request->id_branch, ["id" => "request-branch", "name" => "Request[id_branch]"]) ?> -->
						<?= Branches::buildMultiSelector($Request->branches, [
							"id" 	=> "request-branches",
							"name"	=> "Request[branches][]",
						], "филиалы") ?>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-bottom: 30px">
		        <div class="col-sm-12">
			        <div class="row">
				        <div class="col-sm-12">
							<textarea class="form-control" placeholder="комментарий" name="Request[comment]"><?= $Request->comment ?></textarea>
				        </div>
			        </div>
			        <div class="row" style="margin-top: 10px">
				        <div class="col-sm-12">
				               <div class="comment-block">
								<div id="existing-comments-{{id_request}}">
									<div ng-repeat="comment in request_comments">
										<div id="comment-block-{{comment.id}}">
											<span style="color: {{comment.User.color}}" class="comment-login">{{comment.User.login}}: </span>
											<div style="display: initial" id="comment-{{comment.id}}" onclick="editComment(this)" commentid="{{comment.id}}">
												{{comment.comment}}</div>
											<span class="save-coordinates">{{comment.coordinates}}</span>
											<span ng-attr-data-id="{{comment.id}}" class="glyphicon opacity-pointer text-danger glyphicon-remove glyphicon-2px" 
												onclick="deleteComment(this)"></span>
										</div>
									</div>
								</div>
								<div style="height: 25px">
									<span class="pointer no-margin-right comment-add" id="comment-add-{{id_request}}"
								place="<?= Comment::PLACE_REQUEST ?>" id_place="{{id_request}}">комментировать</span>
									<span class="comment-add-hidden">
										<span class="comment-add-login comment-login" id="comment-add-login-{{id_request}}" style="color: <?= User::fromSession()->color ?>"><?= User::fromSession()->login ?>: </span>
										<input class="comment-add-field" id="comment-add-field-{{id_request}}" type="text"
											placeholder="введите комментарий..." request="{{id_request}}" data-place='REQUEST_EDIT_REQUEST' >
									</span>
								</div>
							</div>
				        </div>
			        </div>
		        </div>
		    </div>
        </div>
		<div class="col-sm-3">
			<div class="form-group">
                <input type="text" class="form-control bs-datetime" placeholder="дата создания заявки" name="Request[date]" 
                	value="<?= $Request->adding ? "" : $Request->date ?>">
			</div>
			<div class="form-group">
                <?= RequestStatuses::buildSelector($Request->id_status, "Request[id_status]") ?>
			</div>
			<div class="form-group">
				<?= User::buildSelector($Request->id_user, "Request[id_user]") ?>
            </div>

			<div class="form-group">
                <?= Sources::buildSelector($Request->id_source, 'Request[id_source]') ?>
            </div>
        </div>
        <?= partial("save_button", ["Request" => $Request]) ?>
    </div>
    <!-- /ДАННЫЕ ПО ЗАЯВКЕ С САЙТА И УВЕДОМЛЕНИЯ -->


	<!-- ЗАКАРЫВАЕМ СТАРЫЙ PANEL-BODY И ОТКРЫВАЕМ НОВЫЙ -->
	</div></div>
	
	<div class="panel panel-primary panel-edit" ng-hide="student.minimized">
		<div class="panel-heading">
			Редактирование профиля ученика №<?= $Request->Student->id ?>
			<div class="pull-right">
				
				<span class="link-reverse pointer" ng-click="toggleMinimizeRequest()">{{show_request_panel ? "свернуть" : "развернуть"}}</span>
				
				<?php if (!empty($Request->Student->login)) :?>
				<a style="margin-left: 10px" class="like-white" href="as/student/<?= $Request->Student->id ?>">режим просмотра</a>
				<?php endif ?>
				
				<?php if (!$Request->adding) :?>
				<span style="margin-left: 10px" class='link-reverse pointer' id='delete-student' onclick='deleteStudent(<?= $Request->Student->id ?>)'>удалить профиль</span>
				<?php endif ?>
			</div>
		</div>
		<div class="panel-body">
    
    <div class="row" ng-hide="student.minimized">
	    <div class="col-sm-12">
		    <div class="row">
			    <div class="col-sm-3">
				    <h4 style="margin-top: 0" class="row-header">Ученик</h4>
				    <div class="form-group">
		                <input type="text" placeholder="имя" class="form-control" name="Student[first_name]" ng-model="student.first_name">
		            </div>
		            <div class="form-group">
		                <input type="text" placeholder="фамилия" class="form-control" name="Student[last_name]" ng-model="student.last_name">
		            </div>
		            <div class="form-group">
		                <input type="text" placeholder="отчество" class="form-control" name="Student[middle_name]" ng-model="student.middle_name">
		            </div>
		            <div class="form-group">
		                <?= Grades::buildSelector($Request->Student->grade, "Student[grade]", ["ng-model" => "student.grade"]) ?>
		            </div>
		            <div class="form-group">
			            <div class="input-group" ng-class="{'input-group-with-hidden-span': !emailFull(student.email)}">
			                <input type="text"  placeholder="e-mail" class="form-control" name="Student[email]" ng-model="student.email">
			                <div class="input-group-btn">
				                <button class="btn btn-default" ng-show="emailFull(student.email)" ng-click="emailDialog(student.email)">
				                	<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
				                </button>
			                </div>
			            </div>			                
		            </div>

					<div>
			        	<div class="form-group">
				            <div class="input-group" 
					            ng-class="{'input-group-with-hidden-span' : !phoneCorrect('student-phone')  || (!isMobilePhone('student-phone') && student_phone_level >= 2) }">
			                	<input ng-keyup id="student-phone" type="text"
			                		placeholder="телефон" class="form-control phone-masked"  name="Student[phone]" ng-model="student.phone">
			                	<div class="input-group-btn">
					                	<button class="btn btn-default" ng-show="phoneCorrect('student-phone') && isMobilePhone('student-phone')" ng-click="callSip('student-phone')">
						                	<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
		                				</button>
										<button ng-show="phoneCorrect('student-phone') && isMobilePhone('student-phone')" ng-class="{
												'addon-bordered' : student_phone_level >= 2 || !phoneCorrect('student-phone')
											}" class="btn btn-default" type="button" onclick="smsDialog('student-phone')">
												<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
										</button>
							        	<button ng-hide="student_phone_level >= 2 || !phoneCorrect('student-phone')" class="btn btn-default" type="button" ng-click="student_phone_level = student_phone_level + 1">
							        		<span class="glyphicon glyphicon-plus no-margin-right small"></span>
							        	</button>
						            </div>
							</div>
						</div>
						
						<div class="form-group" ng-show="student_phone_level >= 2">
				            <div class="input-group" 
					            ng-class="{'input-group-with-hidden-span' : !phoneCorrect('student-phone-2')  || (!isMobilePhone('student-phone-2') && student_phone_level >= 3) }">
			                	<input ng-keyup id="student-phone-2" type="text"
			                		placeholder="телефон 2" class="form-control phone-masked"  name="Student[phone2]" ng-model="student.phone2">
			                	<div class="input-group-btn">
				                	<button class="btn btn-default" ng-show="phoneCorrect('student-phone-2') && isMobilePhone('student-phone-2')" ng-click="callSip('student-phone-2')">
					                	<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
	                				</button>
									<button ng-show="phoneCorrect('student-phone-2') && isMobilePhone('student-phone-2')" ng-class="{
											'addon-bordered' : student_phone_level >= 3 || !phoneCorrect('student-phone-2')
										}" class="btn btn-default" type="button"  onclick="smsDialog('student-phone-2')">
											<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
									</button>
						        	<button ng-hide="student_phone_level >= 3 || !phoneCorrect('student-phone-2')" class="btn btn-default" type="button" ng-click="student_phone_level = student_phone_level + 1">
						        		<span class="glyphicon glyphicon-plus no-margin-right small"></span>
						        	</button>
					            </div>
							</div>
						</div>
						
						
						<div class="form-group" ng-show="student_phone_level >= 3">
							<div class="input-group" ng-class="{'input-group-with-hidden-span' : !phoneCorrect('student-phone-3') || !isMobilePhone('student-phone-3') }">
				                <input type="text" id="student-phone-3" placeholder="телефон 3" 
				                	class="form-control phone-masked"  name="Student[phone3]" ng-model="student.phone3">
				                	<div class="input-group-btn">
					                	<button class="btn btn-default" ng-show="phoneCorrect('student-phone-3') && isMobilePhone('student-phone-3')" ng-click="callSip('student-phone-3')">
						                	<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
		                				</button>
										<button ng-show="phoneCorrect('student-phone-3') && isMobilePhone('student-phone-3')" ng-class="{
												!phoneCorrect('student-phone-3')
											}" class="btn btn-default" type="button"  onclick="smsDialog('student-phone-3')">
												<span class="glyphicon glyphicon-envelope no-margin-right" style="font-size: 12px"></span>
										</button>
						            </div>
							</div>
			            </div>    
					</div>
					
					 <div class="form-group">
			            <input class="form-control" ng-model="student.school" name="Student[school]" placeholder="№ школы">
		            </div>
					
					<div class="form-group">
						<input placeholder="серия" class="form-control half-field passport-number" id="student-passport-series"
							name="StudentPassport[series]" value="<?= $Request->Student->Passport->series ?>">
						<input placeholder="номер" class="form-control half-field pull-right passport-number" id="student-passport-number"
							name="StudentPassport[number]" value="<?= $Request->Student->Passport->number ?>">
		            </div>
   		            <div class="form-group">
						<?=
						   Html::date([
						   	"id" 			=> "student-passport-birthday",
			               	"class"			=> "form-control",
			               	"name"			=> "StudentPassport[date_birthday]",
			               	"placeholder"	=> "дата рождения",
			               	"value"			=> $Request->Student->Passport->date_birthday,
			               ]);
			            ?>
		            </div>
		            <div class="form-group" style="white-space: nowrap">
			            <span class="link-like" ng-click="showMap()"><span class="glyphicon glyphicon-map-marker"></span>Метки</span>
			            <span class="text-primary">({{markers.length}})</span>
		            </div>
			    </div>
			    <div class="col-sm-3">
				    <h4 style="margin-top: 0" class="row-header">Представитель</h4>
				    <div class="form-group">
		                <input type="text" placeholder="имя" class="form-control" name="Representative[first_name]" ng-model="representative.first_name">
		            </div>
		            <div class="form-group">
		                <input type="text" placeholder="фамилия" class="form-control" name="Representative[last_name]"
											ng-model="representative.last_name">
		            </div>
		            <div class="form-group">
		                <input type="text" placeholder="отчество" class="form-control" name="Representative[middle_name]" ng-model="representative.middle_name">
		            </div>
		            <div class="form-group">
		                <input type="text" placeholder="статус" class="form-control" name="Representative[status]" ng-model="representative.status">
		            </div>
		            <div class="form-group">
			            <div class="input-group" ng-class="{'input-group-with-hidden-span': !emailFull(representative.email)}">
			                <input type="text" placeholder="e-mail" class="form-control" name="Representative[email]" ng-model="representative.email">
								<div class="input-group-btn">
					                <button class="btn btn-default" ng-show="emailFull(representative.email)" ng-click="emailDialog(representative.email)">
					                	<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
					                </button>
								</div>
			            </div>
		            </div>
		      
					<div>
			        	<div class="form-group">
				            <div class="input-group" 
				ng-class="{'input-group-with-hidden-span' : !phoneCorrect('representative-phone')  || (!isMobilePhone('representative-phone') && representative_phone_level >= 2)  }">
			                	<input ng-keyup id="representative-phone" type="text"
			                		placeholder="телефон" class="form-control phone-masked"  name="Representative[phone]" ng-model="representative.phone">
			                	<div class="input-group-btn">
				                	<button class="btn btn-default" ng-show="phoneCorrect('representative-phone') && isMobilePhone('representative-phone')" 
				                		ng-click="callSip('representative-phone')">
					                	<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
	                				</button>
									<button ng-show="phoneCorrect('representative-phone') && isMobilePhone('representative-phone')" ng-class="{
											'addon-bordered' : representative_phone_level >= 2 || !phoneCorrect('representative-phone')
										}" class="btn btn-default" type="button" onclick="smsDialog('representative-phone')">
											<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
									</button>
						        	<button ng-hide="representative_phone_level >= 2 || !phoneCorrect('representative-phone')" class="btn btn-default" type="button" ng-click="representative_phone_level = representative_phone_level + 1">
						        		<span class="glyphicon glyphicon-plus no-margin-right small"></span>
						        	</button>
						            </div>
							</div>
						</div>
						
						<div class="form-group" ng-show="representative_phone_level >= 2">
				            <div class="input-group" 
				ng-class="{'input-group-with-hidden-span' : !phoneCorrect('representative-phone-2')  || (!isMobilePhone('representative-phone-2') && representative_phone_level >= 3)  }">
			                	<input ng-keyup id="representative-phone-2" type="text"
			                		placeholder="телефон 2" class="form-control phone-masked"  name="Representative[phone2]" ng-model="representative.phone2">
			                	<div class="input-group-btn">
				                	<button class="btn btn-default" ng-show="phoneCorrect('representative-phone-2') && isMobilePhone('representative-phone-2')" 
				                		ng-click="callSip('representative-phone-2')">
					                	<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
	                				</button>
									<button ng-show="phoneCorrect('representative-phone-2') && isMobilePhone('representative-phone-2')" ng-class="{
											'addon-bordered' : representative_phone_level >= 3 || !phoneCorrect('representative-phone-2')
										}" class="btn btn-default" type="button"  onclick="smsDialog('representative-phone-2')">
											<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
									</button>
						        	<button ng-hide="representative_phone_level >= 3 || !phoneCorrect('representative-phone-2')" class="btn btn-default" type="button" ng-click="representative_phone_level = representative_phone_level + 1">
						        		<span class="glyphicon glyphicon-plus no-margin-right small"></span>
						        	</button>
					            </div>
							</div>
						</div>
						
						
						<div class="form-group" ng-show="representative_phone_level >= 3">
							<div class="input-group" 
				ng-class="{'input-group-with-hidden-span' : !phoneCorrect('representative-phone-3')  || !isMobilePhone('representative-phone-3')  }">
				                <input type="text" id="representative-phone-3" placeholder="телефон 3" 
				                	class="form-control phone-masked"  name="Representative[phone3]" ng-model="representative.phone3">
				                	<div class="input-group-btn">
					                	<button class="btn btn-default" ng-show="phoneCorrect('representative-phone-3') && isMobilePhone('representative-phone-3')" 
					                		ng-click="callSip('representative-phone-3')">
						                	<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
		                				</button>
										<button ng-show="phoneCorrect('representative-phone-3') && isMobilePhone('representative-phone-3')" ng-class="{
												!phoneCorrect('representative-phone-3')
											}" class="btn btn-default" type="button"  onclick="smsDialog('representative-phone-3')">
												<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
										</button>
						            </div>
							</div>
			            </div>    
					</div>
					
					<div class="form-group">
						 <textarea placeholder="адрес фактического проживания"  style="height: 123px"
						 	class="form-control" name="Representative[address]" ng-model="representative.address">
		                </textarea>
					</div>
		      
		      
		      
		      
		      
			    </div>
			    <div class="col-sm-3">
				    <h4 style="margin-top: 0" class="row-header">Паспорт</h4>
				    <div class="form-group">
						<input placeholder="серия" class="form-control half-field passport-number" id="passport-series"
							name="Passport[series]" ng-model="representative.Passport.series">
						
						<input placeholder="номер" class="form-control half-field pull-right passport-number" id="passport-number"
							name="Passport[number]" ng-model="representative.Passport.number">
		            </div>
		            <div class="form-group">
								<?=
								   Html::date([
								   	"id" 			=> "passport-birthday",
					               	"class"			=> "form-control",
					               	"name"			=> "Passport[date_birthday]",
					               	"placeholder"	=> "дата рождения",
//					               	"value"			=> $Request->Student->Representative->Passport->date_birthday,
									"ng-model"		=> "representative.Passport.date_birthday",
					               ]);
					            ?>
		            </div>
		            <div class="form-group">
		                <textarea style="height: 79px" placeholder="кем выдан" class="form-control" name="Passport[issued_by]"><?= $Request->Student->Representative->Passport->issued_by ?></textarea>
		            </div>
		            <div class="form-group">
			            <input class="form-control" ng-model="representative.Passport.code" name="Passport[code]" placeholder="код подразделения" id="code-podr">
		            </div>
		            <div class="form-group">
								<?=
								   Html::date([
								   	"id" 			=> "passport-issue-date",
					               	"class"			=> "form-control",
					               	"name"			=> "Passport[date_issued]",
					               	"placeholder"	=> "когда",
//					               	"value"			=> $Request->Student->Representative->Passport->date_issued
									"ng-model"		=> "representative.Passport.date_issued",
					               ]);
					            ?>
		            </div>
		            <div class="form-group">
		                <textarea style="height: 123px" placeholder="адрес" class="form-control" name="Passport[address]" ng-model="representative.Passport.address">
		                </textarea>
		            </div>
			    </div>
				<div class="col-sm-3">
		            <?php if (!empty($Request->Student->login)) :?>
		            <h4 style="margin-top: 0" class="row-header">Данные для входа</h4>
		            <div>
			            <span style="width: 75px; display: inline-block">Логин: </span><i><?= $Request->Student->login ?></i>
		            </div>
		            <div>
			            <span style="width: 75px; display: inline-block">Пароль:</span><i><?= $Request->Student->password ?></i>
		            </div>
		            <div>
			           <span style="width: 75px; display: inline-block">Входов:</span><?= User::getLoginCount($Request->Student->id, Student::USER_TYPE) ?>
		            </div>
		            <?php endif ?>
			    </div>
		    </div>
			<div class="row">
			    <div class="col-sm-9">
					<div class="form-group">
			            <?= Branches::buildSvgSelector($Request->Student->branches, [
				            "name" => "Student[branches][]", 
				            "ng-model" => "student.branches",
				            "id" => "student-branches",
				        ], true) ?>
		            </div>

				    <div class="form-group">

					    <div class="comment-block">
							<div id="existing-comments-{{student.id}}">
								<div ng-repeat="comment in student_comments">
									<div id="comment-block-{{comment.id}}">
										<span style="color: {{comment.User.color}}" class="comment-login">{{comment.User.login}}: </span>
										<div style="display: initial" id="comment-{{comment.id}}" commentid="{{comment.id}}" onclick="editComment(this)">{{comment.comment}}</div>
										<span class="save-coordinates">({{comment.coordinates}})</span>
										<span ng-attr-data-id="{{comment.id}}" 
											class="glyphicon opacity-pointer text-danger glyphicon-remove glyphicon-2px" onclick="deleteComment(this)"></span>
									</div>
								</div>
							</div>
							<div style="height: 25px">
								<span class="pointer no-margin-right comment-add" id="comment-add-{{student.id}}"
									place="<?= Comment::PLACE_STUDENT ?>" id_place="{{student.id}}">комментировать</span>
								<span class="comment-add-hidden">
									<span class="comment-add-login comment-login" id="comment-add-login-{{student.id}}" style="color: <?= User::fromSession()->color ?>"><?= User::fromSession()->login ?>: </span>
									<input class="comment-add-field" id="comment-add-field-{{student.id}}" type="text"
										placeholder="введите комментарий..." request="{{student.id}}" data-place='REQUEST_EDIT_STUDENT' >
								</span>
							</div>
					    </div>

				    </div>

			    </div>
		    </div>
	    </div>
    </div>

    <div class="row" ng-hide="student.minimized">
	    <div class="col-sm-12">
		    <h4 class="row-header">ДОГОВОРЫ
			    <a ng-click="addContractDialog()" class="link-like link-reverse link-in-h">добавить</a>
<!-- 			    <button class="btn btn-default btn-xs" ng-click="addContract()"><span class="glyphicon glyphicon-plus no-margin-right"></span></button> -->
			</h4>

			<!-- ДАГАВАРА -->
			<div ng-repeat="contract in contracts | reverse" ng-hide="contract.deleted">

				<!-- вкладки догаваров -->
				<ul class="nav nav-tabs" ng-show="contract.History.length > 0">
					<li ng-repeat="contract_history in contract.History" id="contract_history_li_{{contract.id}}_{{contract_history.id}}">
						<a href="#contract_history_{{contract.id}}_{{contract_history.id}}" data-toggle="tab" aria-expanded="false">{{$index + 1}} версия</a>
					</li>
					<li class="active" id="contract_history_li_{{contract.id}}">
						<a href="#contract_history_{{contract.id}}" data-toggle="tab" aria-expanded="true">текущая версия</a>
					</li>
				</ul>
				<!-- /вкладки догаваров -->

				<div class="tab-content" style="margin: 15px 0" ng-class="{'border-top-separator' : (!contract.History.length && !$first)}">
					<!--  основной догавар -->
					<div class="tab-pane active" id="contract_history_{{contract.id}}">
						<div class="row">
							<div class="col-sm-5">
								<div style="margin-bottom: 3px">
									<span style="display: inline-block; width: 200px">договор №</span>
									<span>{{contract.id}}</span>
								</div>
								<div style="margin-bottom: 3px">
									<span style="display: inline-block; width: 200px">класс</span>
									<span>{{contract.grade}}</span>
								</div>
								<div style="margin-bottom: 3px">
									<span style="display: inline-block; width: 200px">дата заключения</span>
									<span>{{formatContractDate(contract.date)}}</span>
								</div>
								<div style="margin-bottom: 3px" ng-show="contract.cancelled">
									<span style="display: inline-block; width: 200px">дата расторжения</span>
									<span>{{formatContractDate(contract.cancelled_date)}}</span>
								</div>
								<div style="margin-bottom: 3px">
									<span style="display: inline-block; width: 200px">общая сумма</span>
									<span>{{contract.sum | number}} руб.</span>
								</div>
								<div style="margin-bottom: 3px">
									<span style="display: inline-block; width: 200px">сумма 1 семестра</span>
									<span>{{contractFirstPart(contract) | number}} руб.</span>
								</div>
								<div style="margin-bottom: 25px">
									<span style="display: inline-block; width: 200px">сумма 2 семестра</span>
									<span>{{contractSecondPart(contract) | number}} руб.</span>
								</div>
								
								<div ng-repeat="subject in contract.subjects | orderBy:'id_subject'" style="margin-bottom: 3px; white-space: nowrap">
									<span style="display: inline-block; width: 200px" ng-class="{
										'text-warning'	: subject.status == 1 && !contract.cancelled,	
										'text-danger'	: contract.cancelled,
									}">{{subject.name}}</span>
									<span ng-show="!subject.count2">{{subject.count}}
										<ng-pluralize count="subject.count" when="{
													'one' 	: 'занятие',
													'few'	: 'занятия',
													'many'	: 'занятий',
										}"></ng-pluralize>
									</span>
									<span ng-show="subject.count2">{{subject.count}} + {{subject.count2}}
										<ng-pluralize count="subject.count2" when="{
													'one' 	: 'занятие',
													'few'	: 'занятия',
													'many'	: 'занятий',
										}"></ng-pluralize>
									</span>
									<span ng-show="subject.score != '' && subject.score !== null">
										({{subject.score}} <ng-pluralize count="subject.score" when="{
											'one'	: 'балл',
											'few'	: 'балла',
											'many'	: 'баллов'
										}"></ng-pluralize>)
									</span>
								</div>
							</div>
							<div class="col-sm-3" style="padding: 0; font-size: 12px; width: 18.5%">
								<div class="form-group link-like link-reverse" style="margin-bottom: 5px" ng-click="editContract(contract)">
									создать новую версию
								</div>
								<div class="form-group link-like link-reverse" style="margin-bottom: 5px" ng-click="editContractWithoutVersionControl(contract)">
									изменить без проводки
								</div>
								<div class="form-group link-like link-reverse" style="margin-bottom: 5px" ng-click="printContract(contract.id)">
									печать договора
									<?= partial("contract_print", ["Request" => $Request]) ?>
								</div>
								<div class="form-group link-like link-reverse" style="margin-bottom: 5px" ng-click="deleteContract(contract)">
									удалить
								</div>
							</div>
							<div class="col-sm-4" style="font-size: 12px !important; white-space: nowrap">
								<div>
									<b>прикрепленные файлы</b>

									<span class="btn-file link-like link-reverse small" ng-hide="contract.files.length >= 3">
										<span>добавить файл</span>
										<input name="contract_file" type="file" id="fileupload{{contract.id}}" data-url="upload/contract/">
									</span>

									<div ng-repeat="file in contract.files" class="loaded-file">
										<span style="color: black">файл {{$index + 1}}</span> <span ng-show="file.size && file.coords">({{file.size}}, {{file.coords}})</span>
										<a target="_blank" href="files/contracts/{{file.name}}" class="link-reverse small">скачать</a>
										<span class="link-like link-reverse small" ng-click="deleteContractFile(contract, $index)">удалить</span>
									</div>


								</div>
							</div>
						</div>
						<div class="row" style="margin-top: 25px">
							<div class="col-sm-12">
								<span class="save-coordinates" style="font-style: normal; font-size: 14px">
									договор создал {{contract.user_login}} {{formatDate(contract.date_changed) | date:'yyyy.MM.dd в HH:mm'}}
								</span>
							</div>
						</div>
					</div>

					<!-- / основной догавар -->

					<!-- история -->
					<div ng-repeat="contract_history in contract.History" id="contract_history_{{contract.id}}_{{contract_history.id}}" class="tab-pane">
						<div class="row">
							<div class="col-sm-5">
								<div style="margin-bottom: 3px">
									<span style="display: inline-block; width: 200px">договор №</span>
									<span>{{contract.id}}</span>
								</div>
								<div style="margin-bottom: 3px">
									<span style="display: inline-block; width: 200px">класс</span>
									<span>{{contract_history.grade}}</span>
								</div>
								<div style="margin-bottom: 3px">
									<span style="display: inline-block; width: 200px">дата заключения</span>
									<span>{{formatContractDate(contract_history.date)}}</span>
								</div>
								<div style="margin-bottom: 3px" ng-show="contract_history.cancelled">
									<span style="display: inline-block; width: 200px">дата расторжения</span>
									<span>{{formatContractDate(contract.cancelled_date)}}</span>
								</div>
								<div style="margin-bottom: 3px">
									<span style="display: inline-block; width: 200px">общая сумма</span>
									<span>{{contract_history.sum | number}} руб.</span>
								</div>
								<div style="margin-bottom: 3px">
									<span style="display: inline-block; width: 200px">сумма 1 семестра</span>
									<span>{{contractFirstPart(contract_history) | number}} руб.</span>
								</div>
								<div style="margin-bottom: 25px">
									<span style="display: inline-block; width: 200px">сумма 2 семестра</span>
									<span>{{contractSecondPart(contract_history) | number}} руб.</span>
								</div>
								<div ng-repeat="subject in contract_history.subjects" style="margin-bottom: 3px; white-space: nowrap">
									<span style="display: inline-block; width: 200px" ng-class="{
										'text-warning'	: subject.status == 1 && !contract.cancelled,	
										'text-danger'	: contract_history.cancelled,
									}">{{subject.name}}</span>
									<span ng-show="!subject.count2">{{subject.count}}
										<ng-pluralize count="subject.count" when="{
													'one' 	: 'занятие',
													'few'	: 'занятия',
													'many'	: 'занятий',
										}"></ng-pluralize>
									</span>
									<span ng-show="subject.count2">{{subject.count}} + {{subject.count2}}
										<ng-pluralize count="subject.count2" when="{
													'one' 	: 'занятие',
													'few'	: 'занятия',
													'many'	: 'занятий',
										}"></ng-pluralize>
									</span>
									<span ng-show="subject.score != '' && subject.score !== null">
										({{subject.score}} <ng-pluralize count="subject.score" when="{
											'one'	: 'балл',
											'few'	: 'балла',
											'many'	: 'баллов'
										}"></ng-pluralize>)
									</span>
								</div>
							</div>
							<div class="col-sm-2"  style="padding: 0; font-size: 12px">
								<div class="form-group link-like link-reverse" style="margin-bottom: 5px" ng-click="editHistoryContract(contract_history)">
									изменить без проводки
								</div>
								<div class="form-group link-like link-reverse" style="margin-bottom: 5px" ng-click="printContract(contract.id)">
									печать договора
								</div>
								<div class="form-group link-like link-reverse" style="margin-bottom: 5px" ng-click="deleteContractHistory(contract, contract_history, $index)">
									удалить
								</div>
							</div>
						</div>
						<div class="row" style="margin-top: 25px">
							<div class="col-sm-12">
								<span class="save-coordinates" style="font-style: normal; font-size: 14px">
									договор создал {{contract_history.user_login}} {{formatDate(contract_history.date_changed) | date:'yyyy.MM.dd в HH:mm'}}
								</span>
							</div>
						</div>
					</div>
					<!-- /история -->
				</div> <!-- /tab-content -->

			</div>
			<!-- /ДАГАВАРА -->
	    </div>
    </div>
    <div class="row">
	    <div class="col-sm-12">
		    <h4 class="row-header" ng-show="Groups.length">УЧЕНИК ПРИКРЕПЛЕН К ГРУППАМ</h4>
		    <div style="margin: 15px 0">
				<?= globalPartial("groups_list", ["filter" => false]) ?>
		    </div>
		    <h4 class="row-header" ng-show="Groups.length == 0">НЕТ ГРУПП</h4>
	    </div>
    </div>
    <div class="row" ng-hide="student.minimized">
	    <div class="col-sm-12">
		    <h4 class="row-header">ПЛАТЕЖИ
			    <a class="link-like link-reverse link-in-h" ng-click="addPaymentDialog()">добавить</a>
		    </h4>
		    <div class="form-group payment-line">
				<div ng-repeat="payment in payments | reverse" style="margin-bottom: 5px">
					<span class="label label-success" ng-class="{'label-danger' : payment.id_status == <?= Payment::NOT_PAID_BILL ?>}">
					{{payment_statuses[payment.id_status]}}<span ng-show="payment.id_status == <?= Payment::PAID_CARD ?>">{{payment.card_number ? " *" + payment.card_number.trim() : ""}}</span></span>
					
					<span class="capitalize">{{payment_types[payment.id_type]}}</span>
					на сумму {{payment.sum}} <ng-pluralize count="payment.sum" when="{
						'one' : 'рубль',
						'few' : 'рубля',
						'many': 'рублей',
					}"></ng-pluralize> от {{payment.date}}
						<span class="save-coordinates">({{payment.user_login}} {{formatDate(payment.first_save_date) | date:'yyyy.MM.dd в HH:mm'}})
						</span>
						<a class="link-like link-reverse small" ng-click="confirmPayment(payment)" ng-show="!payment.confirmed">подтвердить</a>
						<span class="label pointer label-success" ng-show="payment.confirmed" ng-click="confirmPayment(payment)">подтвержден</span>
						<a class="link-like link-reverse small" ng-click="printBill(payment)" ng-show="payment.id_status == <?= Payment::PAID_BILL ?>">печать счета</a>
						<a class="link-like link-reverse small" ng-click="editPayment(payment)">редактировать</a>
						<a class="link-like link-reverse small" ng-click="deletePayment($index, payment)">удалить</a>
				</div>
		    </div>
	    </div>
    </div>
    
	<?= partial("visits") ?>
    <?= partial("teacher_likes") ?>
    
    <?= partial("save_button", ["Request" => $Request]) ?>
	<?= partial("bill_print") ?>
</div></div>

</form>