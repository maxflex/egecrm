<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ДОГОВОРА -->
<div class="lightbox-new lightbox-addcontract">
	<h4 style="margin-bottom: 20px">ПАРАМЕТРЫ ДОГОВОРА</h4>
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

		<div class="col-sm-8" style='width: 600px'>
			<div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12">
					<span class="input-label" style="max-width: 320px; top: -2px; position: absolute">общая сумма оказанных и планируемых услуг</span>
					<span class="half-black contract-recommended-price" ng-show="recommendedPrice(current_contract) && current_contract.info.
					grade >= 9">
                        рекомендуемая цена: {{recommendedPrice(current_contract) | number}}
					</span>
					<div class="input-group">
					    <input id="contract-sum" type="text" placeholder="сумма" class="form-control digits-only" ng-model="current_contract.sum" ng-value="current_contract.sum">
					    <span class="input-group-addon rubble-addon">₽, цена без скидки</span>
					</div>
				</div>
			</div>
            <div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12">
                    <span class="input-label">скидка</span>
					<select class="form-control" ng-model="current_contract.discount">
                        <option value='0'>не установлено</option>
                        <option disabled>──────────────</option>
						<option ng-repeat="discount in <?= Discount::json() ?>"
								value="{{discount}}">{{ discount + '%'}}</option>
						</select>
					 </select>
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
					    <?= Grades::buildSelector(false, false, [
                            "ng-model" => "current_contract.info.grade",
                            "ng-disabled" => 'isDisabledField(current_contract, "grade")'
                        ], true) ?>
				</div>
			</div>
			<!-- <div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12">
					<span class="input-label">организационный сбор</span>
					<div class="input-group">
					    <input id="contract-sum" type="text" placeholder="сбор" class="form-control digits-only" ng-model="current_contract.duty" ng-value="current_contract.duty">
					    <span class="input-group-addon rubble-addon">₽</span>
					</div>
				</div>
			</div> -->
			<div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12">
					<span class="input-label">учебный год</span>
						<select class="form-control"  ng-model="current_contract.info.year" ng-disabled="isDisabledField(current_contract, 'year')">
							<option ng-repeat="year in <?= Years::json() ?>"
								value="{{year}}">{{ year + '-' + ((1*year) + 1) + ' уч. г.' }}</option>
						</select>
					 </select>
				</div>
			</div>
            <div class="row" style="margin-bottom: 10px">
				<div class="col-sm-12">
					<span class="input-label">рекомендуемый график платежей</span>
						<select class="form-control"  ng-model="current_contract.payments_info">
                            <option value='0-0'>не установлено</option>
                            <option disabled>──────────────</option>
							<option ng-repeat="(value, dates) in splitPaymentsOptions(current_contract.info.year)" ng-selected="value == current_contract.payments_info"
								value="{{ value }}">{{ getPaymentLabel(dates) }}</option>
						</select>
					 </select>
				</div>
			</div>
            <div ng-show="current_contract.payments_info != '0-0'">
                <hr>
                <div style='margin-bottom: 20px'>
                    <div class='contract-details'>
                        <span>общая стоимость договора:</span>
                        <span>{{ current_contract.sum | number }} руб.</span>
                    </div>
                    <div  class='contract-details' ng-if='current_contract.discount == 0'>
                        <span>скидка:</span>
                        <span>отсутствует</span>
                    </div>
                    <div ng-if='current_contract.discount > 0'>
                        <div class='contract-details'>
                            <span>скидка:</span>
                            <span>{{ current_contract.discount * current_contract.sum / 100 | number }} руб.</span>
                        </div>
                        <div class='contract-details'>
                            <span>стоимость договора с учетом скидки:</span>
                            <span>{{ getContractSum(current_contract) | number}} руб.</span>
                        </div>
                    </div>
                </div>
                <div ng-repeat="n in [] | range:current_contract.payments_split" class='contract-details'>
                    <span>
                        <span ng-if='!n'>{{ n + 1 }} платеж при заключении договора:</span>
                        <span ng-if='n'>
                            {{ n + 1 }} платеж до {{ splitPaymentsOptions(current_contract.info.year)[current_contract.payments_info][n - 1] }}:
                        </span>
                    </span>
                    <span>
                        {{ getPaymentPrice(current_contract, n) | number }} руб. ({{ splitLessons(current_contract, n) }} <ng-pluralize count="splitLessons(current_contract, n)" when="{
                            'one' 	: 'занятие',
                            'few'	: 'занятия',
                            'many'	: 'занятий',
                        }"></ng-pluralize>)
                    </span>
                </div>
            </div>
		</div>

	</div>
	<center>
		<button class="btn btn-primary ajax-contract-button" ng-click="addContractNew()">Сохранить</button>
	</center>
</div>
<!-- /ЛАЙТБОКС ДОБАВЛЕНИЕ ДОГОВОРА -->
