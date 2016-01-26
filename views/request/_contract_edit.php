<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ДОГОВОРА -->
<div class="lightbox-new lightbox-addcontract">
	<h4 style="margin-bottom: 20px">ПАРАМЕТРЫ ДОГОВОРА</h4>
	<div class="row">
		<div class="col-sm-6">
			<div class="contract-subject-list subject-line transition-control no-transition" ng-repeat="(id_subject, subject_name) in Subjects">
				    <input class="triple-switch" id="checkbox-subject-{{id_subject}}"
				    	ng-model="current_contract.subjects[id_subject].status"
				    	ng-change="subjectHandle(id_subject)"
					    data-slider-min="0" data-slider-max="3" data-slider-step="1" 
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
					<span class="input-label" style="max-width: 180px; top: -9px; position: absolute">общая сумма оказанных и планируемых услуг</span>
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
					<span class="input-label">дата создания версии</span>
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
			<div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12">
					<span class="input-label">организационный сбор</span>
					<div class="input-group">
					    <input id="contract-sum" type="text" placeholder="сбор" class="form-control digits-only" ng-model="current_contract.duty" ng-value="current_contract.duty">
					    <span class="input-group-addon rubble-addon">₽</span>
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