    <h4 class="page-title">Данные по заявке с сайта</h4>

	<form id="request-edit" ng-app="Request" ng-controller="EditCtrl"
		ng-init="<?= 
			 angInit("contract_loaded", $Request->contractLoaded())
			.angInit("subjects", ContractSubject::getContractSubjects($Request->Contract->id))
			.angInit("freetime", Freetime::getStudentFreeTime($Request->Student->id))
			.angInit("payment_statuses", Payment::$all)
			.angInit("payments", $Request->getPayments())
			.angInit("user", $User->dbData())
		?>"
	>
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
	           <input type="hidden" name="id_request" value="<?= $Request->id ?>">
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
                        <?= Branches::buildSvgSelector($Request->id_branch, "Request[id_branch]") ?>
                        </div>
                </div>

<!--
                <div class="col-sm-3">
                    <div class="form-group">
                        <?= NotificationTypes::buildSelector() ?>
                    </div>
                    <div class="form-group">
                        <input 
                    </div>
                    <div class="form-group">
                        <?= NotificationTypes::buildSelector() ?>
                    </div>
                </div>
-->
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
                <input type="text" placeholder="имя" class="form-control" name="Student[first_name]" value="<?= $Request->Student->first_name ?>">
            </div>
            <div class="form-group">
                <input type="text" placeholder="фамилия" class="form-control" name="Student[last_name]" value="<?= $Request->Student->last_name ?>">
            </div>
            <div class="form-group">
                <input type="text" placeholder="отчество" class="form-control" name="Student[middle_name]" value="<?= $Request->Student->middle_name ?>">
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
	            <a href="#"><span class="glyphicon glyphicon-map-marker"></span>Школа местонахождение</a>
            </div>
            <div class="form-group">
	            <a href="#"><span class="glyphicon glyphicon-map-marker"></span>Факт местонахождение</a>
            </div>
	    </div>
	    <div class="col-sm-3">
		    <h4>Представитель</h4>
		    <div class="form-group">
                <input type="text" placeholder="имя" class="form-control" name="Representative[first_name]" value="<?= $Request->Representative->first_name ?>">
            </div>
            <div class="form-group">
                <input type="text" placeholder="фамилия" class="form-control" name="Representative[last_name]" value="<?= $Request->Representative->last_name ?>">
            </div>
            <div class="form-group">
                <input type="text" placeholder="отчество" class="form-control" name="Representative[middle_name]" value="<?= $Request->Representative->middle_name ?>">
            </div>
            <div class="form-group">
                <input type="text" placeholder="e-mail" class="form-control" name="Representative[email]" value="<?= $Request->Representative->email ?>">
            </div>
            <div class="form-group">
                <input type="text" placeholder="сотовый 1" class="form-control phone-masked" name="Representative[phone_main]" value="<?= $Request->Representative->phone_main ?>">
            </div>
            <div class="form-group">
                <input type="text" placeholder="сотовый 2" class="form-control phone-masked" name="Representative[phone_additional]" value="<?= $Request->Representative->phone_additional ?>">
            </div>
            <div class="form-group">
                <input type="text" placeholder="домашний" class="form-control phone-masked" name="Representative[phone_home]" value="<?= $Request->Representative->phone_home ?>">
            </div>
            <div class="form-group">
                <input type="text" placeholder="рабочий" class="form-control phone-masked" name="Representative[phone_work]" value="<?= $Request->Representative->phone_work ?>">
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
					   "value"			=> $Request->Representative->Passport->series,
				    ], "9999");
				    
					// Номер
				    Html::digitMask([
					   "placeholder"	=> "номер",
					   "class"			=> "form-control half-field pull-right",
					   "id"				=> "passport-number",
					   "name"			=> "Passport[number]",
					   "value"			=> $Request->Representative->Passport->number,
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
			               	"value"			=> $Request->Representative->Passport->date_birthday,
			               ]); 
			            ?>
            </div>
            <div class="form-group">
                <textarea rows="5" placeholder="кем выдан" class="form-control" name="Passport[issued_by]"><?= $Request->Representative->Passport->issued_by ?></textarea>
            </div>
            <div class="form-group">
						<?= 
						   Html::date([
						   	"id" 			=> "passport-issue-date",
			               	"class"			=> "form-control",
			               	"name"			=> "Passport[date_issued]",
			               	"placeholder"	=> "когда",
			               	"value"			=> $Request->Representative->Passport->date_issued
			               ]); 
			            ?>
            </div>
            <div class="form-group">
                <textarea rows="5" placeholder="адрес" class="form-control" name="Passport[address]"><?= $Request->Representative->Passport->address ?></textarea>
            </div>
	    </div>
		<div class="col-sm-3" style="text-align: center">
		    <h4>Свободное время</h4>
		     <div class="form-group">
			    <div class="btn-group btn-group-xs btn-group-freetime">
					<button type="button" class="btn" ng-click="chooseDay(1)" 
						ng-class="{'day-chosen' : adding_day == 1, 'btn-success' : hasFreetime(1), 'btn-default' : !hasFreetime(1)}">ПН</button>
					<button type="button" class="btn" ng-click="chooseDay(2)" 
						ng-class="{'day-chosen' : adding_day == 2, 'btn-success' : hasFreetime(2), 'btn-default' : !hasFreetime(2)}">ВТ</button>
					<button type="button" class="btn" ng-click="chooseDay(3)" 
						ng-class="{'day-chosen' : adding_day == 3, 'btn-success' : hasFreetime(3), 'btn-default' : !hasFreetime(3)}">СР</button>
					<button type="button" class="btn" ng-click="chooseDay(4)" 
						ng-class="{'day-chosen' : adding_day == 4, 'btn-success' : hasFreetime(4), 'btn-default' : !hasFreetime(4)}">ЧТ</button>
					<button type="button" class="btn" ng-click="chooseDay(5)" 
						ng-class="{'day-chosen' : adding_day == 5, 'btn-success' : hasFreetime(5), 'btn-default' : !hasFreetime(5)}">ПТ</button>
					<button type="button" class="btn" ng-click="chooseDay(6)" 
						ng-class="{'day-chosen' : adding_day == 6, 'btn-success' : hasFreetime(6), 'btn-default' : !hasFreetime(6)}">СБ</button>
					
			    </div>
            </div>
            
            <div ng-show="adding_day">
	            <div id="free-time-list" ng-repeat="ft in freetime | filter:{day : adding_day}">
		             <span class="label label-success">{{ft.start}}</span> — <span class="label label-success">{{ft.end}}</span>
	            </div>
            </div>
            
            <input type="hidden" id="freetime_json" name="freetime_json">
            
            
            <div ng-show="adding_day" class="add-freetime-block">
	            <div id="timepair" class="timepair">
		            <input type="text" class="time start" ng-model="free_time_start" id="free_time_start"> 
		             до 
		            <input type="text" class="time end" ng-model="free_time_end" id="free_time_end">
	            </div>
	            <button class="btn btn-primary" style="margin-top: 10px; width: 148px" ng-click="addFreetime()"><span class="glyphicon glyphicon-plus"></span>Добавить</button>
            </div>
	    </div>
    </div>
    <div class="row">
	    <div class="col-sm-9">
		    <div class="form-group">
		    	<textarea placeholder="любая другая информация в произвольной форме" class="form-control"></textarea>
		    </div>
	    </div>
    </div>
    <div class="row">

    </div>
    <div class="row">
	    <div class="col-sm-12">
		    <h4>Договоры</h4>
		    <div class="row">
			    <div class="col-sm-4" ng-class="{'o3' : contract_cancelled}">
				    <div class="form-group">
					    <input ng-model="contract_cancelled" ng-value="contract_cancelled" 
					    	ng-init="<?= angInit("contract_cancelled", $Request->Contract->cancelled) ?>" name="Contract[cancelled]" type="hidden">
					    <table class="table">
							<thead>
								<tr>
									<td>предмет</td>
									<td colspan="2">занятий</td>
								</tr>
							</thead>
							<tbody>
								<tr ng-repeat="subject in subjects">
									<td>{{subject.name}}</td>
									<td class="center" width="70">{{subject.count}}</td>
									<td class="pull-right">
										<span class="glyphicon glyphicon-remove text-danger opacity-pointer" ng-click="removeSubject($index)"></span>
									</td>
								</tr>
								<tr><td colspan="3"></td></tr>
								<tr class="add-subject-group">
									<td style="padding: 1px; border-top: 0"><?= Subjects::buildSelector() ?></td>
									<td style="padding: 1px; border-top: 0" class="center" width="70">
										<center>
											<input id="add-subject-count" class="form-control" type="text" style="width: 50px; text-align: center" ng-keydown="watchEnter($event)">
										</center>
									</td>
									<td class="pull-right" style="border-top: 0">
										<span class="glyphicon glyphicon-plus text-success opacity-pointer" ng-click="addSubject()"></span>
									</td>
								</tr>
							</tbody>
						</table>
						<input type="hidden" id="subjects_json" name="subjects_json">
<!-- 		                <input type="text" placeholder="предметы" class="form-control" name="Contract[subjects]" value="<?= $Request->Contract->subjects ?>"> -->
		            </div>
		            <div class="form-group">
			            <div class="input-group">
			                <input type="text" placeholder="сумма" class="form-control" name="Contract[sum]" value="<?= $Request->Contract->sum ?>">
			                <span class="input-group-addon rubble-addon">₽</span>
			            </div>
		            </div>
		            <div class="form-group">
<!-- 						<input class="form-control bs-date-top" id="contract-date" placeholder="дата заключения" name="Contract[date]" value="<?= $Request->Contract->date ?>"> на  -->
		               <?= 
						   Html::date([
						   	"id" 			=> "contract-date",
			               	"class"			=> "form-control",
			               	"placeholder"	=> "дата заключения",
			               	"name"			=> "Contract[date]",
			               	"value"			=> $Request->Contract->date
			               ], true); 
			            ?>
		            </div>
<!--
		            <div class="form-group">
		                <input type="text" placeholder="количество занятий" class="form-control" name="Contract[lessons_count]"  value="<?= $Request->Contract->lessons_count ?>">
		            </div>
		            <div class="form-group">
		                <input type="text" placeholder="" class="form-control" name="Contract[additional]"  value="<?= $Request->Contract->additional ?>">
		            </div>
-->
			    </div>
			    <div class="col-sm-5">
				    <div class="form-group form-group-side-label">
					    <a href="#"><span class="glyphicon glyphicon-middle glyphicon-print"></span>печать договора</a>
				    </div>
					<div class="form-group form-group-side-label link-like" ng-show="!contract_cancelled" ng-click="contractCancelled(1)">
					    <span class="glyphicon glyphicon-middle glyphicon-remove"></span>расторгнуть договор
				    </div>
					<div class="form-group form-group-side-label link-like" ng-show="contract_cancelled" ng-click="contractCancelled(0)">
					    <span class="glyphicon glyphicon-middle glyphicon-ok"></span>отменить расторжение договора
				    </div>
				    <div class="form-group form-group-side-label link-text">
						<span ng-hide="contract_loaded">
							<span class="glyphicon glyphicon-middle glyphicon-paperclip"></span>прикрепить электронную версию договора
						</span>
						<span ng-show="contract_loaded">
							<a href="files/contracts/<?= $Request->id ?>.doc"><span class="glyphicon glyphicon-file glyphicon-middle"></span>электронная версия договора</a>
						</span>
						<input id="fileupload" type="file" name="contract_digital" data-url="upload/contract/<?= $Request->id ?>">
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
						    	ng-repeat="(id_status, title) in payment_statuses" 
								ng-selected="payment.id_status == id_status" 
								ng-value="id_status"
							>{{title}}</option>
					    </select> от
					    <input class="form-control bs-date-top" id="payment-date-{{$index}}" value="{{payment.date}}"  name="Payment[{{$index}}][date]"> на 
					    <input type="text" class="form-control" id="payment-sum-{{$index}}" value="{{payment.sum}}"  name="Payment[{{$index}}][sum]"> руб.
						<span class="save-coordinates-2">({{payment.user_login}} {{formatDate(payment.first_save_date) | date:'yyyy.MM.dd в HH:mm'}})
							<span class="glyphicon glyphicon-remove glyphicon-middle text-danger opacity-pointer" ng-click="removePayment($index)"></span>
						</span>
				  	</div>
			    </div>
				<?= Payment::buildSelector(false, false, ["ng-model" => "new_payment.id_status"]) ?> от 
				<input class="form-control bs-date-top" id="payment-date" ng-model="new_payment.date"> на 
				<input type="text" class="form-control" id="payment-sum" ng-model="new_payment.sum"  ng-keydown="watchEnter($event)"> руб.
				<a style="margin-left: 10px; cursor: pointer" ng-click="addPayment()">
					<span class="glyphicon glyphicon-plus"></span>добавить
				</a>
		    </div>
		    <input type="hidden" id="payments_json" name="payments_json">
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