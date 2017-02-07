<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ДОГОВОРА -->
<div class="lightbox-new lightbox-addcontracttest">
	<h4 style="margin-bottom: 20px">ПАРАМЕТРЫ ДОГОВОРА</h4>
	<div class="row">
		<div class="col-sm-5">
			<div class="contract-subject-list subject-line transition-control no-transition" ng-repeat="(id_subject, subject_name) in Subjects">
				    <input class="triple-switch" id="checkbox-subject-{{id_subject}}"
				    	ng-model="current_contract_test.subjects[id_subject].status"
				    	ng-change="subjectHandle(current_contract_test, id_subject)"
					    data-slider-min="0" data-slider-max="3" data-slider-step="1"
					    data-slider-value="{{current_contract_test.subjects[id_subject].status}}"
				    >
				    <span class="subject-name" ng-class="{'no-opacity' : subjectChecked(current_contract_test, id_subject)}">{{subject_name}}</span>
				<div class="pull-right" style="top: -5px; position: relative">

					<span class="dogavar-label first" ng-show="subjectChecked(current_contract_test, id_subject)">
						<ng-pluralize ng-show="current_contract_test.subjects[id_subject].count" count="current_contract_test.subjects[id_subject].count" when="{
							'one' 	: 'урок',
							'few'	: 'урока',
							'many'	: 'уроков',
						}"></ng-pluralize>
					</span>

					<input type="text" class="form-control contract-test-lessons" placeholder="1й семестр"
						ng-show="subjectChecked(current_contract_test, id_subject)"
						ng-model="current_contract_test.subjects[id_subject].count">
				</div>
			</div>
		</div>

		<div class="col-sm-7">
			<div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12">
					<span class="input-label" style="max-width: 180px; top: -9px; position: absolute">общая сумма оказанных и планируемых услуг</span>
					<span class="half-black contract-recommended-price" ng-show="recommendedPrice(current_contract_test) && current_contract_test.info.
					grade >= 9">
						рекомендуемая цена: {{recommendedPrice(current_contract_test) | number}}
					</span>
					<div class="input-group">
					    <input id="contract-test-sum" type="text" placeholder="сумма" class="form-control digits-only" ng-model="current_contract_test.sum" ng-value="current_contract_test.sum">
					    <span class="input-group-addon rubble-addon">₽</span>
					</div>
				</div>
			</div>
			<div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12">
					<span class="input-label">дата создания версии</span>
					<div class="input-group date bs-date">
						<input id="contract-test-date" class="form-control" data-date-format='yyyy.mm.dd'  placeholder="дата" type="text" ng-model="current_contract_test.date" ng-value="current_contract_test.date">
						<span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
					</div>
				</div>
			</div>
			<div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12">
					<span class="input-label">класс</span>
					    <?= Grades::buildSelector(false, false, ["ng-model" => "current_contract_test.info.grade", "ng-disabled" => 'isDisabledField(current_contract_test, "grade")']) ?>
				</div>
			</div>
			<div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12">
					<span class="input-label">организационный сбор</span>
					<div class="input-group">
					    <input id="contract-test-sum" type="text" placeholder="сбор" class="form-control digits-only" ng-model="current_contract_test.duty" ng-value="current_contract_test.duty">
					    <span class="input-group-addon rubble-addon">₽</span>
					</div>
				</div>
			</div>
			<div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12">
					<span class="input-label">учебный год</span>
						<select class="form-control"  ng-model="current_contract_test.info.year" ng-disabled="isDisabledField(current_contract_test, 'year')">
							<option ng-repeat="year in <?= Years::json() ?>"
								value="{{year}}">{{ year + '-' + ((1*year) + 1) + ' уч. г.' }}</option>
						</select>
					 </select>
				</div>
			</div>
			<div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12">
					<span class="input-label">экстернат</span>
					<div class="input-group">
						<label class="ios7-switch transition-control no-transition" style="font-size: 24px; top: 1px">
						    <input type="checkbox" ng-model="current_contract_test.external" ng-true-value="1">
						    <span class="switch"></span>
						</label>
					</div>
				</div>
			</div>
		</div>

	</div>
	<center>
		<button class="btn btn-primary ajax-contract-button" ng-click="addContractTest()">Сохранить</button>
	</center>
</div>
<!-- /ЛАЙТБОКС ДОБАВЛЕНИЕ ДОГОВОРА -->
