<!-- 	<img src="img/svg/loading-bars.svg" alt="Загрузка страницы..." id="svg-loading"> -->
	<div id="panel-loading">Загрузка...</div>
	<form id="request-edit" ng-app="Request" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>" style="opacity: 0.05" autocomplete='off'>
		
		<!-- КАРТА И ЛАЙТБОКС -->
		<div class="lightbox"></div>
		<div class="lightbox-element lightbox-map">
			<map zoom="10" disable-default-u-i="true" scale-control="true" zoom-control="true" zoom-control-options="{style:'SMALL'}">
				<transit-layer></transit-layer>
<!--
				<custom-control position="TOP_RIGHT" index="1">
		          <input type="text" id="map-search">
		        </custom-control>
-->
			</map>
			<button class="btn btn-default map-save-button" onclick="lightBoxHide()">Сохранить</button>
		</div>
		<!-- КОНЕЦ /КАРТА И ЛАЙТБОКС -->
		
		
		<!-- СКЛЕЙКА КЛИЕНТОВ -->
		<div class="lightbox-element lightbox-glue panel panel-primary">
		  <div class="panel-heading">Склейка клиентов</div>
		  <div class="panel-body">
		   <div class="input-group">
		      <input id="id-student-glue" type="text" class="form-control" placeholder="ID ученика" ng-model="id_student_glue" ng-change="findStudent()">
		      <span class="input-group-btn">
		        <button class="btn btn-primary" type="button" ng-disabled="!GlueStudent" ng-click="glue()">Склеить</button>
		      </span>
		    </div><!-- /input-group -->
		    <h6 ng-show="GlueStudent" style="text-align: center">
		    Заявка будет присвоена ученику №{{GlueStudent.id}} 
		    <span ng-show="GlueStudent.last_name || GlueStudent.first_name || GlueStudent.middle_name">
			    ({{GlueStudent.last_name}} {{GlueStudent.first_name}} {{GlueStudent.middle_name}})
		    </span>
		    </h6>
		  </div>
		</div>
		<!-- /СКЛЕЙКА КЛИЕНТОВ -->
	
	<!-- Скрытые поля -->
	<input type="hidden" name="id_request" value="<?= $Request->id ?>">
	<input type="hidden" name="id_request" value="<?= $Request->id ?>">
	
	<!-- если нажата сохранить, то всегда обнулять  adding -->
	<input type="hidden" name="Request[adding]" value="0">
	
	<input type="hidden" id="freetime_json" name="freetime_json">
	<input type="hidden" id="subjects_json" name="subjects_json">
	<input type="hidden" id="payments_json" name="payments_json">
	
	<input type="hidden" ng-value="markerData() | json"  name="marker_data">
	<!-- Конец /скрытые поля -->
		
	
	<!-- ВКЛАДКИ ЗАЯВОК -->	
	<?php if (!$Request->adding) { ?>
	<div class="row" style="margin-bottom: 20px">
		<div class="col-sm-12">
			<ul class="nav nav-tabs">
			<li ng-repeat="request_duplicate in request_duplicates" ng-class="{'active' : request_duplicate == <?= $Request->id ?>}">
				<a href="requests/edit/{{request_duplicate}}">
					Заявка #{{request_duplicate}}
				</a>
			</li>
		</ul>
		</div>
	</div>
	<?php } ?>
	<!-- /ВКЛАДКИ ЗАЯВОК -->
		
	<!-- ДАННЫЕ ПО ЗАЯВКЕ С САЙТА И УВЕДОМЛЕНИЯ -->
	<div class="row page-title">
		<div class="col-sm-9">
			<h4>Данные по заявке с сайта 
				<span class="hint--right" data-hint="Время создания: <?= dateFormat($Request->date) ?>">
					<span class="glyphicon glyphicon-info-sign opacity-pointer" style="font-size: 14px; cursor: default"></span>
				</span>
				<span style="font-size: 14px; font-weight: normal" class="pull-right link-like" onclick="lightBoxShow('glue')">
					<span class="glyphicon glyphicon-resize-small"></span>склеить
				</span>
			</h4>
		</div>
		<div class="col-sm-3">
			<h4>Напоминание</h4>
		</div>
	</div>
	
	
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
               <?= Subjects::buildColSelector($Request->subjects, "Request[subjects]") ?>
                <div class="col-sm-3">
                    <div class="form-group">
                        <?= RequestStatuses::buildSelector($Request->id_status, "Request[id_status]") ?>
                        </div>

                    <div class="form-group">
						<?= User::buildSelector($Request->id_user, "Request[id_user]") ?>
                    </div>

                    <div class="form-group">
                        <?= Grades::buildSelector($Request->grade, "Request[grade]") ?>
                    </div>

                    <div class="form-group">
                        <input placeholder="имя" class="form-control" name="Request[name]" value="<?= $Request->name ?>">
                    </div>

                    <div class="form-group">
                        <input placeholder="телефон" class="form-control phone-masked" name="Request[phone]" value="<?= $Request->phone ?>">
                    </div>

                    <div class="form-group">
                        <?= Branches::buildSvgSelector($Request->id_branch, ["id" => "request-branch", "name" => "Request[id_branch]"]) ?>
                        </div>
                </div>

                <div class="col-sm-3">
                    <div class="form-group">
                        <?= NotificationTypes::buildSelector($Request->Notification->id_type, "Notification[id_type]") ?>
                    </div>
                    <div class="form-group">
						<?=
						   Html::date([
								"id" 			=> "notification-date",
								"class"			=> "form-control",
								"name"			=> "Notification[date]",
								"placeholder"	=> "дата",
								"value"			=> $Request->Notification->date,
			               ], "now"); 
			            ?>
                    </div>
					<div class="form-group">
						<?=
						   Html::time([
								"id" 			=> "notification-time",
								"class"			=> "form-control",
								"name"			=> "Notification[time]",
								"placeholder"	=> "время",
								"value"			=> $Request->Notification->time,
			               ]); 
			            ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row" style="margin-bottom: 30px">
        <div class="col-sm-9">
            <textarea class="form-control" placeholder="комментарий" name="Request[comment]"><?= $Request->comment ?></textarea>
        </div>
    </div>
    <!-- /ДАННЫЕ ПО ЗАЯВКЕ С САЙТА И УВЕДОМЛЕНИЯ -->
    
    <div class="row">
	    <div class="col-sm-3">
		    <h4 style="margin-top: 0">Ученик</h4>
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
                <input type="text" placeholder="e-mail" class="form-control" name="Student[email]" value="<?= $Request->Student->email ?>">
            </div>
			
			<div>
	            <div class="form-group">
		            <div class="input-group" ng-class="{'input-group-with-hidden-span' : student_phone_level >= 2 || !phoneCorrect('student-phone') }">
	                	<input ng-keyup id="student-phone" type="text" 
	                		placeholder="сотовый" class="form-control phone-masked"  name="Student[phone]" value="<?= $Request->Student->phone ?>">
	                	<span class="input-group-btn" ng-hide="student_phone_level >= 2 || !phoneCorrect('student-phone')">
				        	<button class="btn btn-default" type="button" ng-click="student_phone_level = student_phone_level + 1">
				        		<span class="glyphicon glyphicon-plus no-margin-right" style="font-size: 12px"></span>
				        	</button>
						</span>
		            </div>
	            </div>
				<div class="form-group" ng-show="student_phone_level >= 2">
		            <div class="input-group" ng-class="{'input-group-with-hidden-span' : student_phone_level >= 3 || !phoneCorrect('student-phone-2') }">
	                	<input ng-keyup id="student-phone-2" type="text" 
	                		placeholder="сотовый 2" class="form-control phone-masked"  name="Student[phone2]" value="<?= $Request->Student->phone2 ?>">
	                	<span class="input-group-btn" ng-hide="student_phone_level >= 3 || !phoneCorrect('student-phone-2')">
				        	<button class="btn btn-default" type="button" ng-click="student_phone_level = student_phone_level + 1">
				        		<span class="glyphicon glyphicon-plus no-margin-right" style="font-size: 12px"></span>
				        	</button>
						</span>
		            </div>
	            </div>
				<div class="form-group" ng-show="student_phone_level >= 3">
	                <input type="text" placeholder="сотовый 3" class="form-control phone-masked"  name="Student[phone3]" value="<?= $Request->Student->phone3 ?>">
	            </div>
			</div>
<!--
			<div ng-hide="student_phone_level >= 3 || true">
				<span class="link-like pull-right" style="top: -10px; position: relative; font-size: 10px" ng-click="student_phone_level = student_phone_level + 1">
					<span class="glyphicon glyphicon-plus" style="margin-right: 2px"></span>добавить номер
				</span>
			</div>
-->
            <div class="form-group">
                <?= Grades::buildSelector($Request->Student->grade, "Student[grade]") ?>
            </div>
			<div class="form-group">
			    <?=
				    // Серия
				    Html::digitMask([
					   "placeholder"	=> "серия",
					   "class"			=> "form-control half-field",
					   "id"				=> "student-passport-series",
					   "name"			=> "StudentPassport[series]",
					   "value"			=> $Request->Student->Passport->series,
				    ], "9999");
				    
					// Номер
				    Html::digitMask([
					   "placeholder"	=> "номер",
					   "class"			=> "form-control half-field pull-right",
					   "id"				=> "student-passport-number",
					   "name"			=> "StudentPassport[number]",
					   "value"			=> $Request->Student->Passport->number,
				    ], "999999");
				?>
            </div>
            <div class="form-group" style="white-space: nowrap">
	            <span class="link-like" ng-click="showMap('school')"><span class="glyphicon glyphicon-map-marker"></span>Школа местонахождение</span> 
	            <span class="text-primary">({{marker_school_count}})</span>
            </div>
            <div class="form-group" style="white-space: nowrap">
	            <span class="link-like" ng-click="showMap('home')"><span class="glyphicon glyphicon-map-marker"></span>Факт местонахождение</span>
	            <span class="text-primary">({{marker_home_count}})</span>
            </div>
	    </div>
	    <div class="col-sm-3">
		    <h4 style="margin-top: 0">Представитель</h4>
		    <div class="form-group">
                <input type="text" placeholder="имя" class="form-control" name="Representative[first_name]" ng-model="representative.first_name">
            </div>
            <div class="form-group">
                <input type="text" placeholder="фамилия" class="form-control" name="Representative[last_name]" ng-model="representative.last_name">
            </div>
            <div class="form-group">
                <input type="text" placeholder="отчество" class="form-control" name="Representative[middle_name]" ng-model="representative.middle_name">
            </div>
            <div class="form-group">
                <input type="text" placeholder="e-mail" class="form-control" name="Representative[email]" ng-model="representative.email">
            </div>
            <div class="form-group">
	            <div class="input-group" ng-class="{'input-group-with-hidden-span' : representative_phone_level >= 2 || !phoneCorrect('representative-phone') }">
                	<input ng-keyup id="representative-phone" type="text" 
                		placeholder="сотовый" class="form-control phone-masked"  name="Representative[phone]" value="<?= $Request->Student->Representative->phone ?>">
                	<span class="input-group-btn" ng-hide="representative_phone_level >= 2 || !phoneCorrect('representative-phone')">
			        	<button class="btn btn-default" type="button" ng-click="representative_phone_level = representative_phone_level + 1">
			        		<span class="glyphicon glyphicon-plus no-margin-right" style="font-size: 12px"></span>
			        	</button>
					</span>
	            </div>
            </div>
            <div class="form-group" ng-show="representative_phone_level >= 2">
	            <div class="input-group" ng-class="{'input-group-with-hidden-span' : representative_phone_level >= 3 || !phoneCorrect('representative-phone-2') }">
                	<input ng-keyup id="representative-phone-2" type="text" 
                		placeholder="сотовый" class="form-control phone-masked"  name="Representative[phone]" value="<?= $Request->Student->Representative->phone ?>">
                	<span class="input-group-btn" ng-hide="representative_phone_level >= 3 || !phoneCorrect('representative-phone-2')">
			        	<button class="btn btn-default" type="button" ng-click="representative_phone_level = representative_phone_level + 1">
			        		<span class="glyphicon glyphicon-plus no-margin-right" style="font-size: 12px"></span>
			        	</button>
					</span>
	            </div>
            </div>
            <div class="form-group" ng-show="representative_phone_level >= 3">
                <input type="text" placeholder="сотовый 3" class="form-control phone-masked" name="Representative[phone3]" value="<?= $Request->Student->Representative->phone3 ?>">
            </div>
<!--
			<div ng-hide="representative_phone_level >= 3">
				<span class="link-like pull-right" style="top: -10px; position: relative; font-size: 10px" ng-click="representative_phone_level = representative_phone_level + 1">
					<span class="glyphicon glyphicon-plus" style="margin-right: 2px"></span>добавить номер
				</span>
			</div>
-->
	    </div>
	    <div class="col-sm-3">
		    <h4 style="margin-top: 0">Паспорт</h4>
		    <div class="form-group">
			    <?=
				    // Серия
				    Html::digitMask([
					   "placeholder"	=> "серия",
					   "class"			=> "form-control half-field",
					   "id"				=> "passport-series",
					   "name"			=> "Passport[series]",
					   "value"			=> $Request->Student->Representative->Passport->series,
				    ], "9999");
				    
					// Номер
				    Html::digitMask([
					   "placeholder"	=> "номер",
					   "class"			=> "form-control half-field pull-right",
					   "id"				=> "passport-number",
					   "name"			=> "Passport[number]",
					   "value"			=> $Request->Student->Representative->Passport->number,
				    ], "999999");
				?>
            </div>
            <div class="form-group">
						<?= 
						   Html::date([
						   	"id" 			=> "passport-birthday",
			               	"class"			=> "form-control",
			               	"name"			=> "Passport[date_birthday]",
			               	"placeholder"	=> "дата рождения",
			               	"value"			=> $Request->Student->Representative->Passport->date_birthday,
			               ]); 
			            ?>
            </div>
            <div class="form-group">
                <textarea rows="5" placeholder="кем выдан" class="form-control" name="Passport[issued_by]"><?= $Request->Student->Representative->Passport->issued_by ?></textarea>
            </div>
            <div class="form-group">
						<?= 
						   Html::date([
						   	"id" 			=> "passport-issue-date",
			               	"class"			=> "form-control",
			               	"name"			=> "Passport[date_issued]",
			               	"placeholder"	=> "когда",
			               	"value"			=> $Request->Student->Representative->Passport->date_issued
			               ]); 
			            ?>
            </div>
            <div class="form-group">
                <textarea rows="5" placeholder="адрес" class="form-control" name="Passport[address]"><?= $Request->Student->Representative->Passport->address ?></textarea>
            </div>
	    </div>
		<div class="col-sm-3">
		    <h4 style="margin-top: 0">Свободное время</h4>
<!--
		     <div class="form-group">
			    <div class="btn-group btn-group-xs btn-group-freetime">
					<button ng-repeat="weekday in weekdays" type="button" class="btn" ng-click="chooseDay($index + 1)" 
						ng-class="{'day-chosen' : adding_day == ($index + 1), 'btn-success' : hasFreetime($index + 1), 'btn-default' : !hasFreetime($index + 1)}">
						{{weekday.short}}
					</button>				
			    </div>
            </div>
-->
            
            <div ng-show="adding_day && false">
	            <h5 style="text-align: center">{{weekdays[adding_day - 1].full}}:</h5>
	            <div class="free-time-list" ng-repeat="ft in freetime | filter:{day : adding_day}" ng-hide="ft.deleted">
		             <span class="label label-success">{{ft.start}}</span> — <span class="label label-success">{{ft.end}}</span>
		             <span class="glyphicon glyphicon-remove glyphicon-middle text-danger opacity-pointer" ng-click="removeFreetime(ft)"></span>
	            </div>
            </div>       
            
            <div>
	            <div class="row vertical-align border-bottom-separator" ng-repeat="(day_number, weekday) in weekdays" 
		            ng-show="freetimeControl(day_number)">
		            <div class="col-sm-2">
		            	{{weekday.short}}
		            </div>
		            <div class="col-sm-10" style="display: block">
						<div ng-repeat="ft in freetime | filter:{day : (day_number + 1)}"  ng-hide="ft.deleted" class="freetime-line">
							<span class="label label-success">{{ft.start}}</span> — <span class="label label-success">{{ft.end}}</span>
							<span class="glyphicon glyphicon-remove glyphicon-middle text-danger opacity-pointer pull-right" ng-click="removeFreetime(ft)"></span>
						</div>
		            </div>
	            </div>
            </div>     
            
            <div class="add-freetime-block">
	            <div id="timepair" class="timepair">
		            
		            <select class="form-control" ng-model="adding_day">
			            <option selected value=''>день</option>
						<option disabled value=''>──────────────</option>
						<option ng-repeat="(day_number, weekday) in weekdays" ng-value="(day_number + 1)">{{weekday.short}}</option>
		            </select>
		            <span>c</span>
		            <input type="text" class="form-control time start" ng-model="free_time_start" id="free_time_start">
		            <span>по</span>
		            <input type="text" class="form-control time end" ng-model="free_time_end" id="free_time_end" style="float: right">
	            </div>
	            <button class="btn btn-default" style="margin-top: 10px; width: 100%" ng-click="addFreetime()"><span class="glyphicon glyphicon-plus"></span>Добавить</button>
            </div>
	    </div>
    </div>
    
    <div class="row">
	    <div class="col-sm-9">
			<div class="form-group">
	            <?= Branches::buildSvgSelector($Request->Student->branches, ["name" => "Student[branches][]", "id" => "student-branches"], true) ?>
            </div>
		    
		    <div class="form-group">
			    <?= Comment::display(Comment::PLACE_REQUEST_EDIT, $Request->Student->id) ?>
		    </div>
		    
	    </div>
    </div>
    
    <div class="row">
	    <div class="col-sm-12">
		    <h4>Договоры 
			    <button class="btn btn-default btn-xs" ng-click="addContract()"><span class="glyphicon glyphicon-plus no-margin-right"></span></button>
			</h4>
			
			<!-- ДАГАВАРА -->
			<div ng-repeat="contract in contracts | reverse" ng-hide="contract.deleted">
				<div class="panel panel-default">
					<div class="panel-heading">
						Договор #{{contracts.length - $index}}
						
						<div class="pull-right" ng-show="emptyContract(contract)">
							<span class="glyphicon opacity-pointer glyphicon-remove text-danger no-margin-right glyphicon-remove text-danger" ng-click="deleteContract(contract)">
							</span>
						</div>
						
					</div>
					<div class="panel-body" ng-class="{'o3' : contract.deleted}">
		    	<div class="row">
			    <input type="hidden" ng-value="contract.cancelled"	name="Contract[{{contract.id}}][cancelled]">
			    <input type="hidden" ng-value="contract.deleted"	name="Contract[{{contract.id}}][deleted]">
				<div class="col-sm-4" ng-class="{'o3' : contract.cancelled}">
					<div class="form-group">
										    <div class="form-group">
					    <table class="table bb" ng-show="contract.subjects">
<!--
							<thead>
								<tr>
									<td>предмет</td>
									<td colspan="2">занятий</td>
								</tr>
							</thead>
-->
							<tbody>
								<tr ng-repeat="subject in contract.subjects" style="border:0; ">
									<input type="hidden" name="Contract[{{contract.id}}][subjects][{{$index}}][id_subject]" ng-value="subject.id_subject">
									<input type="hidden" name="Contract[{{contract.id}}][subjects][{{$index}}][count]" 		ng-value="subject.count">
									<td>{{subject.name}}</td>
									<td class="center">{{subject.count}}
										<ng-pluralize count="subject.count" when="{
											'one' 	: 'занятие',
											'few'	: 'занятия',
											'many'	: 'занятий',
										}"></ng-pluralize>
									</td>
									<td style="text-align: right">
										<span class="glyphicon glyphicon-remove text-danger opacity-pointer" ng-click="removeSubject(contract, $index)"></span>
									</td>
								</tr>
							</tbody>
						</table>
						
								<div>
										<select style="width: 51%; display: inline-block" id="subjects-select{{contract.id}}" class="form-control">
										    <option selected value=''><?= Subjects::$title ?></option>
											<option disabled value=''>──────────────</option>
										    <option 
										    	ng-repeat='(id_subject, title) in <?= toJson(Subjects::$all) ?>' 
												ng-value="id_subject"
											>{{title}}</option>
										</select>
										
										<input id="add-subject-count{{contract.id}}"  item="{{contract.id}}" placeholder="занятий"
												class="form-control digits-only" type="text" style="width: 28%; text-align: center; display: inline-block" ng-keydown="watchEnter($event)">
										<span class="glyphicon glyphicon-plus text-success opacity-pointer pull-right" style="padding: 8px" ng-click="addSubject(contract)"></span>
								</div>

						
		            </div>		        
						<div class="input-group">
						    <input type="text" placeholder="сумма" class="form-control digits-only" name="Contract[{{contract.id}}][sum]" ng-model="contract.sum" ng-value="contract.sum">
						    <span class="input-group-addon rubble-addon">₽</span>
						</div>
					</div>
					<div class="form-group">
						<div class="input-group date bs-date">
							<input class="form-control" data-date-format='yyyy.mm.dd' 
								name="Contract[{{contract.id}}][date]" placeholder="когда" type="text" ng-model="contract.date" ng-value="contract.date">
							<span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
						</div>            
					</div>
				</div>
				<div class="col-sm-7">
				    <div class="form-group form-group-side-label link-like" ng-click="printContract(contract.id)">
					    <span class="glyphicon glyphicon-middle glyphicon-print"></span>печать договора
						<?= partial("contract_print", ["Request" => $Request]) ?>
				    </div>
					<div class="form-group form-group-side-label link-like" ng-show="!contract.cancelled" ng-click="contractCancelled(contract, 1)">
					    <span class="glyphicon glyphicon-middle glyphicon-remove"></span>расторгнуть договор
				    </div>
					<div class="form-group form-group-side-label link-like" ng-show="contract.cancelled" ng-click="contractCancelled(contract, 0)">
					    <span class="glyphicon glyphicon-repeat"></span>отменить расторжение договора
				    </div>
				    <div class="form-group form-group-side-label link-text">
	<!--
					<span ng-hide="contract_file">
							<span class="glyphicon glyphicon-middle glyphicon-paperclip"></span>прикрепить электронную версию договора
						</span>
-->
<!--
						<span ng-show="contract.file && !contract.uploaded_file">
							<a href="files/contracts/{{contract.file}}">
								<span class="glyphicon glyphicon-file glyphicon-middle"></span>электронная версия договора
							</a>
						</span>
-->						
						<div class="form-group form-group-side-label">
							<span class="btn-file link-like" ng-hide="contract.files.length >= 3">
								<span class="glyphicon glyphicon-middle glyphicon-paperclip"></span><span>прикрепить электронную версию договора</span>
								<input name="contract_file" type="file" id="fileupload{{contract.id}}" data-url="upload/contract/">
							</span>
							
							<span class="btn-file link-like" ng-show="contract.files.length >= 3" onclick="notifyError('Нельзя прикреплять более трёх файлов')">
								<span class="glyphicon glyphicon-middle glyphicon-paperclip"></span><span>прикрепить электронную версию договора</span>
							</span>
							
							<div ng-repeat="file in contract.files" class="loaded-file">
								<input type="hidden" name="Contract[{{contract.id}}][files][]" ng-value="file | json">
								<span class="glyphicon glyphicon-file no-margin-right"></span>
								<a class="gray-link" href="files/contracts/{{file.name}}" target="_blank">Электронная версия #{{$index + 1}}</a>
								<span class="glyphicon glyphicon-remove text-danger opacity-pointer" style="top: 2px" ng-click="deleteContractFile(contract, $index)"></span>
								<i ng-show="file.size && file.coords">({{file.size}}, {{file.coords}})</i>
							</div>
						</div>
						
						<div class="form-group form-group-side-label link-like" ng-show="contract.History" ng-click="showHistory(contract)">
							<span class="glyphicon glyphicon-time"></span>история изменений ({{contract.History.length}})
						</div>
<!-- 						<input id="fileupload" type="file" name="contract_digital" data-url="upload/contract/{{contract.id}}"> -->
				    </div>
			    </div>
		    	</div>
		    	
		    	
		    	<!-- ИСТОРИЯ ИЗМЕНЕНИЙ ДОГОВОРА -->
		    	<div id="contract-history-{{contract.id}}" style="display: none; position: relative">
			    	<div class="row border-top-separator" ng-repeat="contract in contract.History | reverse" 
				    	style="position: relative">
				    	<span class="glyphicon glyphicon-chevron-down contract-history-arrow"></span>
				    	<div class="col-sm-4" ng-class="{'o3' : contract.cancelled}" style="position: relative">
					    <div class="blocker-div"></div>
						<div class="form-group">
											    <div class="form-group">
						    <table class="table bb" ng-show="contract.subjects">
								<tbody>
									<tr ng-repeat="subject in contract.subjects" style="border:0; ">
										<td>{{subject.name}}</td>
										<td class="center">{{subject.count}} занятий</td>
										<td style="text-align: right">
											<span class="glyphicon glyphicon-remove text-danger opacity-pointer"></span>
										</td>
									</tr>
								</tbody>
							</table>
	
							
			            </div>		        
							<div class="input-group">
							    <input type="text" placeholder="сумма" class="form-control digits-only" ng-model="contract.sum" ng-value="contract.sum">
							    <span class="input-group-addon rubble-addon">₽</span>
							</div>
						</div>
						<div class="form-group">
							<div class="input-group date bs-date">
								<input class="form-control" data-date-format='yyyy.mm.dd'  placeholder="когда" type="text" ng-model="contract.date" ng-value="contract.date">
								<span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
							</div>            
						</div>
					</div>
				    	<div class="col-sm-8">
							<div class="form-group form-group-side-label text-primary" style="margin-top: 5px">
								<div class="save-coordinates pull-right" style="padding-top: 7px">
							    	<span class="glyphicon glyphicon-floppy-disk"></span>Реквизиты изменения: 
							    	{{contract.user_login}} {{formatDate(contract.date_changed) | date:'yyyy.MM.dd в HH:mm'}}
						    	</div>
						    	<span ng-show="contract.cancelled">
							    	<span class="glyphicon glyphicon-middle glyphicon-remove"></span>договор расторгнут
						    	</span>
						    	<span ng-show="!contract.cancelled">
									<span class="glyphicon glyphicon-middle glyphicon-ok"></span>договор активен
						    	</span>
						    </div>
							
							<div class="form-group form-group-side-label" ng-show="contract.files.length">
							<span class="btn-file text-primary">
								<span class="glyphicon glyphicon-middle glyphicon-paperclip"></span>электронные версии договора
							</span>
							
							
							<div ng-repeat="file in contract.files" class="loaded-file">
								<span class="glyphicon glyphicon-file no-margin-right"></span>
								<a class="gray-link" href="files/contracts/{{file.name}}" target="_blank">Электронная версия #{{$index + 1}}</a>
							</div>
							
							<!--
<div ng-show="contract.uploaded_file1" class="loaded-file">
								<span class="glyphicon glyphicon-file"></span>
								<a class="gray-link" href="files/contracts/tmp/{{contract.file1}}" target="_blank">{{contract.uploaded_file1}}</a>
								<span class="glyphicon glyphicon-remove text-danger opacity-pointer" style="top: 2px"></span>
							</div>
							<div ng-show="contract.uploaded_file2" class="loaded-file">
								<span class="glyphicon glyphicon-file"></span>
								<a class="gray-link" href="files/contracts/tmp/{{contract.file2}}" target="_blank">{{contract.uploaded_file2}}</a>
								<span class="glyphicon glyphicon-remove text-danger opacity-pointer" style="top: 2px"></span>
							</div>
							<div ng-show="contract.uploaded_file3" class="loaded-file">
								<span class="glyphicon glyphicon-file"></span>
								<a class="gray-link" href="files/contracts/tmp/{{contract.file3}}" target="_blank">{{contract.uploaded_file3}}</a>
								<span class="glyphicon glyphicon-remove text-danger opacity-pointer" style="top: 2px"></span>
							</div>
-->
						</div>
							
				    	</div>
			    	</div>
		    	</div>
		    	<!-- /ИСТОРИЯ ИЗМЕНЕНИЙ ДОГОВОРА -->
		    	
					</div>
				</div>
			</div>
			<!-- /ДАГАВАРА -->
	    </div>
    </div>
    <div class="row">
	    <div class="col-sm-12">
		    <h4>Платежи</h4>
		    <div class="form-group payment-line">
			    <div ng-repeat="payment in payments" ng-hide="payment.deleted">
			    	<input type="hidden" name="Payment[{{$index}}][id]" value="{{payment.id}}">
					<input type="hidden" name="Payment[{{$index}}][deleted]" value="{{payment.deleted}}">
				  	<div class="bottom-dashed">
					    <select class="form-control" name="Payment[{{$index}}][id_status]" ng-class="{'input-red-bg' : (payment.id_status == 2)}">
						    <option selected value=''><?= Payment::$title ?></option>
							<option disabled value=''>──────────────</option>
						    <option 
						    	ng-repeat='(id_status, title) in <?= toJson(Payment::$all) ?>' 
								ng-selected="payment.id_status == id_status" 
								ng-value="id_status"
							>{{title}}</option>
					    </select> от
					    <input class="form-control bs-date-top" id="payment-date-{{$index}}" value="{{payment.date}}"  name="Payment[{{$index}}][date]"> на 
					    <input type="text" class="form-control" id="payment-sum-{{$index}}" value="{{payment.sum}}"  name="Payment[{{$index}}][sum]"> руб.
						<span class="save-coordinates-big">({{payment.user_login}} {{formatDate(payment.first_save_date) | date:'yyyy.MM.dd в HH:mm'}})
							<span class="glyphicon glyphicon-remove glyphicon-middle text-danger opacity-pointer" ng-click="removePayment($index)"></span>
						</span>
				  	</div>
			    </div>
			    <div class="form-group inline-block">
					<?= Payment::buildSelector(false, false, ["ng-model" => "new_payment.id_status"]) ?> от
			    </div>
				<div class="form-group inline-block">
					<input class="form-control bs-date-top" id="payment-date" ng-model="new_payment.date"> на 
				</div>
				<div class="form-group inline-block">
					<input type="text" class="form-control digits-only" id="payment-sum" ng-model="new_payment.sum"  ng-keydown="watchEnter($event)"> руб.
				</div>
				<a style="margin-left: 10px; cursor: pointer" ng-click="addPayment()">
					<span class="glyphicon glyphicon-plus"></span>добавить
				</a>
		    </div>
	    </div>
    </div>
    <div class="row" ng-show="<?= ($Request->id_first_save_user ? "true" : "false") ?>">
	    <div class="col-sm-12 save-coordinates">
		    <span class="glyphicon glyphicon-floppy-disk"></span>Реквизиты первого сохранения клиента: 
		    <?= User::findById($Request->id_first_save_user)->login ?>
		    <?= date("d.m.Y в H:i", strtotime($Request->first_save_date)) ?>
	    </div>
    </div>
    <hr style="margin-top: 0">
    <div class="row">
    	<div class="col-sm-12 center">
	    	<button class="btn btn-primary" id="save-button" ng-disabled="saving">Сохранить</button>
    	</div>
    </div>
    </form>