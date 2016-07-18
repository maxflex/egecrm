<div class="row" ng-show="student !== undefined">
    <div class="col-sm-12">
	    <div style="margin-bottom: 20px; display: block">
			<a ng-click="addContractDialog()" class="link-like link-reverse">добавить</a>
	    </div>
		<!-- ДАГАВАРА -->
		<div ng-repeat="contract in contracts | reverse" ng-hide="contract.deleted">

			<!-- вкладки догаваров -->
			<ul class="nav nav-tabs nav-tabs-links" ng-show="contract.History.length > 0">
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
								<span style="display: inline-block; width: 200px">год</span>
								<span>{{contract.year}}–{{contract.year + 1}}</span>
							</div>
							<div style="margin-bottom: 3px">
								<span style="display: inline-block; width: 200px">класс</span>
								<span>{{contract.grade}}</span>
							</div>
							<div style="margin-bottom: 3px">
								<span style="display: inline-block; width: 200px">дата создания версии</span>
								<span>{{formatContractDate(contract.date)}}</span>
							</div>
							<div style="margin-bottom: 3px" ng-show="contract.duty">
								<span style="display: inline-block; width: 200px">организационный сбор</span>
								<span>{{contract.duty | number}} руб.</span>
							</div>
							<div style="margin-bottom: 3px" ng-show="contract.sum">
								<span style="display: inline-block; width: 200px">общая сумма</span>
								<span>{{contract.sum | number}} руб.</span>
							</div>
							<div style="margin-bottom: 3px" ng-show="contractFirstPart(contract)">
								<span style="display: inline-block; width: 200px">сумма 1 семестра</span>
								<span>{{contractFirstPart(contract) | number}} руб.</span>
							</div>
							<div ng-show="contractSecondPart(contract)">
								<span style="display: inline-block; width: 200px">сумма 2 семестра</span>
								<span>{{contractSecondPart(contract) | number}} руб.</span>
							</div>

							<div style="margin-bottom: 25px"></div>

							<div ng-repeat="subject in contract.subjects | orderBy:'id_subject'" style="margin-bottom: 3px; white-space: nowrap">
								<span style="display: inline-block; width: 200px" ng-class="{
									'text-warning'	: subject.status == 2,
									'text-danger'	: subject.status == 1,
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
							<div class="form-group link-like link-reverse" style="margin-bottom: 5px" ng-click="printContractAdditional(contract)">
								печать доп. соглашения
								<?= partial("additional_agreement_print") ?>
							</div>
							<div class="form-group link-like link-reverse" style="margin-bottom: 5px" ng-click="printAct(contract)">
								печать акта сдачи-приемки
								<?= partial("act") ?>
							</div>
							<div class="form-group link-like link-reverse" style="margin-bottom: 5px" ng-click="deleteContract(contract)">
								удалить
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
								<span style="display: inline-block; width: 200px">год</span>
								<span>{{contract.year}}–{{contract.year + 1}}</span>
							</div>
							<div style="margin-bottom: 3px">
								<span style="display: inline-block; width: 200px">класс</span>
								<span>{{contract_history.grade}}</span>
							</div>
							<div style="margin-bottom: 3px">
								<span style="display: inline-block; width: 200px">дата создания версии</span>
								<span>{{formatContractDate(contract_history.date)}}</span>
							</div>
							<div style="margin-bottom: 3px" ng-show="contract_history.duty">
								<span style="display: inline-block; width: 200px">организационный сбор</span>
								<span>{{contract_history.duty | number}} руб.</span>
							</div>
							<div style="margin-bottom: 3px" ng-show="contract_history.sum">
								<span style="display: inline-block; width: 200px">общая сумма</span>
								<span>{{contract_history.sum | number}} руб.</span>
							</div>
							<div style="margin-bottom: 3px" ng-show="contractFirstPart(contract_history)">
								<span style="display: inline-block; width: 200px">сумма 1 семестра</span>
								<span>{{contractFirstPart(contract_history) | number}} руб.</span>
							</div>
							<div ng-show="contractSecondPart(contract_history)">
								<span style="display: inline-block; width: 200px">сумма 2 семестра</span>
								<span>{{contractSecondPart(contract_history) | number}} руб.</span>
							</div>

							<div style="margin-bottom: 25px"></div>

							<div ng-repeat="subject in contract_history.subjects" style="margin-bottom: 3px; white-space: nowrap">
								<span style="display: inline-block; width: 200px" ng-class="{
									'text-warning'	: subject.status == 2,
									'text-danger'	: subject.status == 1,
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