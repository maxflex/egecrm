<!-- новое модальное окно -->
<div class="modal-new" id="modal-contract">
	<div class="modal-new-header">
		<div class="header-title">
			Параметры версии договора
		</div>
		<div class="header-buttons">
			<div ng-click="addContractNew()">
				сохранить
			</div>
			<div onclick="closeModal()">
				закрыть
			</div>
		</div>
	</div>
	<div class="modal-new-content">
		<div class="row">
			<div class="col-sm-4" style='width: 490px'>
				<div class="contract-subject-list subject-line transition-control no-transition" ng-repeat="(id_subject, subject_name) in Subjects">
					<input class="triple-switch" id="checkbox-subject-{{id_subject}}"
					ng-model="current_contract.subjects[id_subject].status"
					ng-change="subjectHandle(current_contract, id_subject)"
					data-slider-min="0" data-slider-max="3" data-slider-step="1"
					data-slider-value="{{current_contract.subjects[id_subject].status}}"
					>
					<span class="subject-name" ng-class="{'no-opacity' : subjectChecked(current_contract, id_subject)}">{{subject_name}}</span>

					<div class="input-group" ng-show="subjectChecked(current_contract, id_subject)">
						<input type="text" class="form-control contract-lessons" id="subject-{{ id_subject }}"
						ng-model="current_contract.subjects[id_subject].count" style='border-right: 0'>
						<span class="input-group-addon rubble-addon">занятий. Программа</span>
						<input type="text" class="form-control contract-lessons" id="subject-program-{{ id_subject }}"
						ng-model="current_contract.subjects[id_subject].count_program" style='border-left: 0'>
						<span class="input-group-addon rubble-addon">занятий</span>
					</div>
				</div>
			</div>

			<div class="col-sm-8" style='width: 800px'>
				<div class="row" style="margin-bottom: 10px">
					<div class="col-sm-12">
						<span class="input-label">дата создания версии</span>
						<div class="input-group date bs-date">
							<input id="contract-date" class="form-control"
								style='width: 210px'
								placeholder="дата" type="text" ng-model="current_contract.date" ng-value="current_contract.date">
							<span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
						</div>
					</div>
				</div>
				<div class="row" style="margin-bottom: 10px">
					<div class="col-sm-12">
						<span class="input-label">класс</span>
						<?= Grades::buildSelector(false, false, [
						"ng-model" => "current_contract.info.grade",
						"ng-disabled" => 'isDisabledField(current_contract, "grade")'
						], true) ?>
					</div>
				</div>
				<div class="row" style="margin-bottom: 50px">
					<div class="col-sm-12">
						<span class="input-label">учебный год</span>
						<select id="contract-year" class="form-control"  ng-model="current_contract.info.year" ng-disabled="isDisabledField(current_contract, 'year')">
							<option ng-repeat="year in <?= Years::json() ?>"
								value="{{year}}">{{ year + '-' + ((1*year) + 1) + ' уч. г.' }}</option>
							</select>
						</select>
					</div>
				</div>
				<div class="row" style="margin-bottom: 10px">
					<div class="col-sm-12">
						<span class="input-label" style="max-width: 320px; top: -2px; position: absolute">общая сумма оказанных и планируемых услуг</span>
						<span class="half-black contract-recommended-price" ng-show="recommendedPrice(current_contract) && current_contract.info.
						grade >= 9">
						рекомендуемая цена: {{recommendedPrice(current_contract) | number}}
					</span>
					<div class="input-group">
						<input id="contract-sum" type="text" placeholder="сумма" style='padding-right: 4px; width: 89px'
							class="form-control digits-only" ng-model="current_contract.sum" ng-value="current_contract.sum">
						<span class="input-group-addon rubble-addon">₽, цена без скидки</span>
					</div>
				</div>
			</div>
			<div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12">
					<span class="input-label">скидка</span>
					<select class="form-control" ng-model="current_contract.discount">
						<option value='0'>отсутствует</option>
						<option disabled>──────────────</option>
						<option ng-repeat="discount in <?= Discount::json() ?>"
							value="{{discount}}">{{ discount + '%'}}</option>
						</select>
					</select>
				</div>
			</div>

			<div class="row" style="margin-bottom: 10px" ng-repeat="payment in current_contract.payments">
				<div class="col-sm-12">
					<span class="input-label">
						{{ $index + 1}} платеж
						<a ng-click="deleteContractPayment($index)" style='margin-left: 5px' class="show-on-hover text-danger">удалить</a>
					</span>
					<div class="input-group contract-payment-input-group">
						<input type="text" class="form-control" placeholder="сумма"
							ng-model="payment.sum" style='border-right: 0'>
						<span class="input-group-addon rubble-addon">₽</span>
						<input class="form-control first-contract-payment" ng-hide="$index > 0" placeholder="при заключении" >
						<input class="form-control bs-date" ng-show="$index > 0"
							placeholder="дата" type="text" ng-model="payment.date" ng-value="current_contract.date">
						<span ng-show="$index" class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
					</div>
				</div>
			</div>

			<div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12 add-contract-payment-controls">
					<a class='pointer' ng-click='addContractPayment()'>добавить платеж</a>
					<a class='pointer' ng-click='addContractPayment(2)'>2 платежа</a>
					<a class='pointer' ng-click='addContractPayment(3)'>3 платежа</a>
					<a class='pointer' ng-click='addContractPayment(8)'>8 платежей</a>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
