	<form id="request-edit" ng-app="Request" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>" >
		
		<!-- КАРТА И ЛАЙТБОКС -->
		<div class="lightbox"></div>
		<div class="lightbox-element lightbox-map">
			<map zoom="10" disable-default-u-i="true" scale-control="true" zoom-control="true" zoom-control-options="{style:'SMALL'}">
				<transit-layer></transit-layer>
				<custom-control position="TOP_RIGHT" index="1">
		          <input type="text" id="map-search">
		        </custom-control>
			</map>
			<button class="btn btn-default map-save-button" onclick="lightBoxHide()">Сохранить</button>
		</div>
		<!-- КОНЕЦ /КАРТА И ЛАЙТБОКС -->
		
	
	<!-- Скрытые поля -->
	<input type="hidden" name="id_request" value="<?= $Request->id ?>">	
	<input type="hidden" id="freetime_json" name="freetime_json">
	<input type="hidden" id="subjects_json" name="subjects_json">
	<input type="hidden" id="payments_json" name="payments_json">
	
	<input type="hidden" ng-value="markerData() | json"  name="marker_data">
	<!-- Конец /скрытые поля -->
		
	<div class="row page-title">
		<div class="col-sm-9">
			<h4>Данные по заявке с сайта</h4>
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
    
    <div class="row">
        <div class="col-sm-9">
            <textarea class="form-control" placeholder="комментарий" name="Request[comment]"><?= $Request->comment ?></textarea>
        </div>
    </div>
    
    <div class="row" style="margin-top: 20px">
	    <div class="col-sm-3">
		    <h4>Ученик</h4>
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
            <div class="form-group">
                <input type="text" placeholder="сотовый" class="form-control phone-masked"  name="Student[phone]" value="<?= $Request->Student->phone ?>">
            </div>
            <div class="form-group">
                <?= Grades::buildSelector($Request->Student->grade, "Student[grade]") ?>
            </div>
            <div class="form-group">
	            <span class="link-like" ng-click="showMap('school')"><span class="glyphicon glyphicon-map-marker"></span>Школа местонахождение</span>
            </div>
            <div class="form-group">
	            <span class="link-like" ng-click="showMap('home')"><span class="glyphicon glyphicon-map-marker"></span>Факт местонахождение</span>
            </div>
	    </div>
	    <div class="col-sm-3">
		    <h4>Представитель</h4>
		    <div class="form-group">
                <input type="text" placeholder="имя" class="form-control" name="Representative[first_name]" value="<?= $Request->Student->Representative->first_name ?>">
            </div>
            <div class="form-group">
                <input type="text" placeholder="фамилия" class="form-control" name="Representative[last_name]" value="<?= $Request->Student->Representative->last_name ?>">
            </div>
            <div class="form-group">
                <input type="text" placeholder="отчество" class="form-control" name="Representative[middle_name]" value="<?= $Request->Student->Representative->middle_name ?>">
            </div>
            <div class="form-group">
                <input type="text" placeholder="e-mail" class="form-control" name="Representative[email]" value="<?= $Request->Student->Representative->email ?>">
            </div>
            <div class="form-group">
                <input type="text" placeholder="сотовый 1" class="form-control phone-masked" name="Representative[phone_main]" value="<?= $Request->Student->Representative->phone_main ?>">
            </div>
            <div class="form-group">
                <input type="text" placeholder="сотовый 2" class="form-control phone-masked" name="Representative[phone_additional]" value="<?= $Request->Student->Representative->phone_additional ?>">
            </div>
            <div class="form-group">
                <input type="text" placeholder="домашний" class="form-control phone-masked" name="Representative[phone_home]" value="<?= $Request->Student->Representative->phone_home ?>">
            </div>
            <div class="form-group">
                <input type="text" placeholder="рабочий" class="form-control phone-masked" name="Representative[phone_work]" value="<?= $Request->Student->Representative->phone_work ?>">
            </div>
	    </div>
	    <div class="col-sm-3">
		    <h4>Паспорт</h4>
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
		<div class="col-sm-3" style="text-align: center">
		    <h4>Свободное время</h4>
		     <div class="form-group">
			    <div class="btn-group btn-group-xs btn-group-freetime">
					<button ng-repeat="weekday in weekdays" type="button" class="btn" ng-click="chooseDay($index + 1)" 
						ng-class="{'day-chosen' : adding_day == ($index + 1), 'btn-success' : hasFreetime($index + 1), 'btn-default' : !hasFreetime($index + 1)}">
						{{weekday.short}}
					</button>				
			    </div>
            </div>
            
            <div ng-show="adding_day">
	            <h5 style="text-align: center">{{weekdays[adding_day - 1].full}}:</h5>
	            <div class="free-time-list" ng-repeat="ft in freetime | filter:{day : adding_day}" ng-hide="ft.deleted">
		             <span class="label label-success">{{ft.start}}</span> — <span class="label label-success">{{ft.end}}</span>
		             <span class="glyphicon glyphicon-remove glyphicon-middle text-danger opacity-pointer" ng-click="removeFreetime(ft)"></span>
	            </div>
            </div>            
            
            <div ng-show="adding_day" class="add-freetime-block">
	            <div id="timepair" class="timepair">
		            <input type="text" class="form-control time start" ng-model="free_time_start" id="free_time_start">
		             до 
		            <input type="text" class="form-control time end" ng-model="free_time_end" id="free_time_end">
	            </div>
	            <button class="btn btn-default" style="margin-top: 10px; width: 156px" ng-click="addFreetime()"><span class="glyphicon glyphicon-plus"></span>Добавить</button>
            </div>
	    </div>
    </div>
    
    <div class="row">
	    <div class="col-sm-9">
			<div class="form-group">
	            <?= Branches::buildSvgSelector($Request->Student->branches, ["name" => "Student[branches][]", "id" => "student-branches"], true) ?>
            </div>
			<div class="form-group">
		    	<textarea placeholder="любая другая информация в произвольной форме" class="form-control" name="Student[other_info]"><?= trim($Request->Student->other_info) ?></textarea>
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
		    <div class="row" ng-repeat="contract in contracts | reverse" ng-class="{'border-top-separator' : $index > 0, 'o3' : contract.deleted}">
			    <input type="hidden" ng-value="contract.cancelled"	name="Contract[{{contract.id}}][cancelled]">
			    <input type="hidden" ng-value="contract.deleted"	name="Contract[{{contract.id}}][deleted]">

				<div class="col-sm-4" ng-class="{'o3' : contract.cancelled}">
					<div class="form-group">
										    <div class="form-group">
					    <table class="table">
							<thead>
								<tr>
									<td>предмет</td>
									<td colspan="2">занятий</td>
								</tr>
							</thead>
							<tbody>
								<tr ng-repeat="subject in contract.subjects">
									<input type="hidden" name="Contract[{{contract.id}}][subjects][{{$index}}][id_subject]" ng-value="subject.id_subject">
									<input type="hidden" name="Contract[{{contract.id}}][subjects][{{$index}}][count]" 		ng-value="subject.count">
									<td>{{subject.name}}</td>
									<td class="center" width="70">{{subject.count}}</td>
									<td class="pull-right">
										<span class="glyphicon glyphicon-remove text-danger opacity-pointer" ng-click="removeSubject(contract, $index)"></span>
									</td>
								</tr>
								<tr><td colspan="3"></td></tr>
								<tr class="add-subject-group">
									<td style="padding: 1px; border-top: 0">
										<select id="subjects-select{{contract.id}}" class="form-control">
										    <option selected disabled><?= Subjects::$title ?></option>
											<option disabled>──────────────</option>
										    <option 
										    	ng-repeat='(id_subject, title) in <?= toJson(Subjects::$all) ?>' 
												ng-value="id_subject"
											>{{title}}</option>
										</select>
									</td>
									<td style="padding: 1px; border-top: 0" class="center" width="70">
										<center>
											<input id="add-subject-count{{contract.id}}"  item="{{contract.id}}"
												class="form-control digits-only" type="text" style="width: 50px; text-align: center" ng-keydown="watchEnter($event)">
										</center>
									</td>
									<td class="pull-right" style="border-top: 0">
										<span class="glyphicon glyphicon-plus text-success opacity-pointer" ng-click="addSubject(contract)"></span>
									</td>
								</tr>
							</tbody>
						</table>
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
						<?= partial("contract_print") ?>
				    </div>
					<div class="form-group form-group-side-label link-like" ng-show="!contract.cancelled" ng-click="contractCancelled(contract, 1)">
					    <span class="glyphicon glyphicon-middle glyphicon-remove"></span>расторгнуть договор
				    </div>
					<div class="form-group form-group-side-label link-like" ng-show="contract.cancelled" ng-click="contractCancelled(contract, 0)">
					    <span class="glyphicon glyphicon-middle glyphicon-ok"></span>отменить расторжение договора
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
							<span class="btn-file link-like">
								<span class="glyphicon glyphicon-middle glyphicon-paperclip"></span><span ng-hide="contract.file && !contract.uploaded_file">прикрепить электронную версию договора</span><span ng-show="contract.file && !contract.uploaded_file">прикрепить новую электронную версию</span>
								<input name="contract_file" type="file" id="fileupload{{contract.id}}" data-url="upload/contract/">
								<input type="hidden" ng-value="contract.file" name="Contract[{{contract.id}}][file]">
							</span>
							<div ng-show="contract.uploaded_file" class="loaded-file">
								<span class="glyphicon glyphicon-file"></span>{{contract.uploaded_file}}
							</div>
						</div>
						
						<div class="form-group form-group-side-label" ng-show="contract.file && !contract.uploaded_file">		
							<a href="files/contracts/{{contract.file}}" target="_blank">
								<span class="glyphicon glyphicon-file"></span>электронная версия договора
							</a>
						</div>

<!-- 						<input id="fileupload" type="file" name="contract_digital" data-url="upload/contract/{{contract.id}}"> -->
				    </div>
			    </div>
				<div class="col-sm-1">
					<div class="pull-right">
						<span class="glyphicon opacity-pointer" ng-click="deleteContract(contract)"
							ng-class="{
								'glyphicon-remove text-danger' : !contract.deleted,
								'glyphicon-ok text-success' : contract.deleted
							}">
						</span>
					</div>
				</div>
		    </div>
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
						    <option selected disabled><?= Payment::$title ?></option>
							<option disabled>──────────────</option>
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
	    	<button class="btn btn-primary" id="save-button">Сохранить</button>
    	</div>
    </div>
    </form>