<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ДОГОВОРА -->
<div class="lightbox-new lightbox-addcontract">
	<h4 style="margin-bottom: 20px">ПАРАМЕТРЫ ДОГОВОРА</h4>
	<div class="row">
		<div class="col-sm-6">
			<div class="contract-subject-list subject-line transition-control no-transition" ng-repeat="(id_subject, subject_name) in Subjects">
				    <input class="triple-switch" id="checkbox-subject-{{id_subject}}"
				    	ng-model="current_contract.subjects[id_subject].status"
				    	ng-change="subjectHandle(id_subject)"
					    data-slider-min="0" data-slider-max="2" data-slider-step="1" 
					    data-slider-value="{{current_contract.subjects[id_subject].status}}"
				    >
				    <span class="subject-name" ng-class="{'no-opacity' : subjectChecked(id_subject)}">{{subject_name}}</span>
				<div class="pull-right" style="top: -5px; position: relative">
					
					<span class="dogavar-label zero" ng-show="subjectChecked(id_subject)">
						<ng-pluralize ng-show="current_contract.subjects[id_subject].count" count="current_contract.subjects[id_subject].count" when="{
							'one' 	: 'урок',
							'few'	: 'урока',
							'many'	: 'уроков',
						}"></ng-pluralize>
					</span>
					
					<span class="dogavar-label first" ng-show="subjectChecked(id_subject)">
						<ng-pluralize ng-show="current_contract.subjects[id_subject].count2" count="current_contract.subjects[id_subject].count2" when="{
							'one' 	: 'урок',
							'few'	: 'урока',
							'many'	: 'уроков',
						}"></ng-pluralize>
					</span>
					
					<span class="dogavar-label second" ng-show="subjectChecked(id_subject)">
						<ng-pluralize ng-show="current_contract.subjects[id_subject].score" count="current_contract.subjects[id_subject].score" when="{
							'one' 	: 'балл',
							'few'	: 'балла',
							'many'	: 'баллов',
						}"></ng-pluralize>
					</span>
					
					<input type="text" class="form-control contract-score" style="margin-left: 5px" placeholder="балл"
						ng-show="subjectChecked(id_subject)" 
						ng-model="current_contract.subjects[id_subject].score">
					
					<input type="text" class="form-control contract-lessons" style="margin-left: 5px" placeholder="2й семестр"
						ng-show="subjectChecked(id_subject)" 
						ng-model="current_contract.subjects[id_subject].count2">
					
					<input type="text" class="form-control contract-lessons" placeholder="1й семестр"
						ng-show="subjectChecked(id_subject)" 
						ng-model="current_contract.subjects[id_subject].count">
				</div>
			</div>
		</div>
		
		<div class="col-sm-6">
			<div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12">
					<span class="input-label">общая сумма по договору</span>
					<span class="half-black contract-recommended-price" ng-show="recommendedPrice(current_contract) && current_contract.grade >= 9">
						рекомендуемая цена: {{recommendedPrice(current_contract) | number}}
					</span>
					<div class="input-group">
					    <input id="contract-sum" type="text" placeholder="сумма" class="form-control digits-only" ng-model="current_contract.sum" ng-value="current_contract.sum">
					    <span class="input-group-addon rubble-addon">₽</span>
					</div>
				</div>
			</div>
			<div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12">
					<span class="input-label">дата заключения</span>
					<div class="input-group date bs-date">
						<input id="contract-date" class="form-control" data-date-format='yyyy.mm.dd'  placeholder="дата" type="text" ng-model="current_contract.date" ng-value="current_contract.date">
						<span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
					</div>
				</div>
			</div>
			<div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12">
					<span class="input-label">класс</span>
					    <?= Grades::buildSelector(false, false, ["ng-model" => "current_contract.grade"]) ?>
				</div>
			</div>
			
			<h4 style="margin: 24px 0 20px">РАСТОРЖЕНИЕ ДОГОВОРА
				<label class="ios7-switch transition-control no-transition" style="font-size: 24px; top: 1px">
				    <input type="checkbox" ng-model="current_contract.cancelled" ng-true-value="1" ng-change="toggleCancelled(current_contract)">
				    <span class="switch"></span>
				</label>
			</h4>

			<div ng-show="current_contract.cancelled">
				<div class="row" style="margin-bottom: 10px">
					<div class="col-sm-12">
						<span class="input-label">дата расторжения</span>
						<div class="input-group date bs-date">
							<input id="contract-cancelled-date" class="form-control" data-date-format='yyyy.mm.dd'  placeholder="дата" type="text"
								ng-model="current_contract.cancelled_date" ng-value="current_contract.cancelled_date">
							<span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<span class="input-label">причина</span>
						<textarea class="form-control" placeholder="причина" rows="7"
							ng-model="current_contract.cancelled_reason" id="contract-cancelled-reason"
							style="float: right; width: 230px; margin-top: 7px; display: inline-block"
						></textarea>
					</div>
				</div>
			</div>

		</div>
		
	</div>
	<center>
		<button class="btn btn-primary ajax-contract-button" ng-click="addContractNew()">Сохранить</button>
	</center>
</div>
<!-- /ЛАЙТБОКС ДОБАВЛЕНИЕ ДОГОВОРА -->