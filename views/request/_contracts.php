<div class="row" ng-show="student !== undefined">
    <div class="col-sm-12">
	    <div style="margin-bottom: 20px; display: block">
			<a ng-click="addContractDialog()" class="link-like link-reverse">добавить</a>
	    </div>
		<!--договора-->
		<table class="table table-hover border-reverse last-item-no-border"
			   ng-repeat="id_contract in getContractIds()"
		>
			<tr class="no-hover">
				<td colspan="8" class="no-border-bottom">
					<h4 class="row-header default-case"> Договор №{{ id_contract }} на {{ firstContractInChainById(id_contract).info.year + '-' + (firstContractInChainById(id_contract).info.year + 1) }} учебный год ({{ Grades[firstContractInChainById(id_contract).info.grade] }})</h4>
				</td>
			</tr>
				<tr ng-repeat="contract in contracts | group_by_id_contract:id_contract | orderBy:'date_changed'">
					<td width="20%">версия {{ $index + 1 }} от {{ formatContractDate(contract.date) }}</td>
					<td width="25%">{{ getContractSum(contract) | number }} <ng-pluralize count="getContractSum(contract)" when="{
						'one': 'рубль',
						'few': 'рубя',
						'many': 'рублей'
					}"></ng-pluralize><span class='text-gray' ng-show='contract.discount > 0'> (с учетом скидки {{ contract.discount }}%)</>
					</td>
					<td width="32%">
						<span
							ng-repeat-start="subject in contract.subjects | orderBy:'id_subject' "
							ng-class="{
										'text-warning'	: subject.status == 2,
										'text-danger'	: subject.status == 1,
									  }">
							{{ Subjects[subject.id_subject] }}</span> ({{ subject.count }})<span ng-repeat-end>{{ $last ? '' : ', '}}
						</span>
					</td>
					<td width="20%">
						создал {{UserService.getLogin(contract.id_user)}} {{formatDate(contract.date_changed) | date:'dd.MM.yyyy'}}
					</td>
					<td align="right" width="3%" class="no-padding-right">
						<span class="link-like" ng-click="contract.show_actions = !contract.show_actions">действия</span>
                        <div ng-show="contract.show_actions" class="emptyClickHandler" ng-click="closeContexMenu()"></div>
						<div ng-show="contract.show_actions" class="contex-menu fadeInUp fadeOutDown">
                            <ul>
                                <li class='link-like' ng-click="createNewContract(contract)">создать новую версию</li>
                                <li class='link-like' ng-click="editContract(contract)">изменить без проводки</li>
                                <li class='link-like' ng-click="printContractLicenced(contract)" ng-show='isFirstContractInChain(contract)'>печать договора ООО</li>
                                <li class='link-like' ng-click="printContractAdditionalOoo(contract)" ng-show='!isFirstContractInChain(contract)'>печать доп.соглашения ООО</li>
								<li class='link-like' ng-click="printServiceActOoo(contract)" ng-show='isLastContractInChain(contract)'>печать акта оказанных услуг</li>
								<li class='link-like' ng-click="printTerminationOoo(contract)" ng-show='isLastContractInChain(contract)'>печать соглашения о расторжении ООО</li>
								<li class='link-like' ng-show='contract.id != contract.id_contract || isLastContractInChain(contract)' ng-click='deleteContract(contract)'>удалить</li>
							</ul>
                        </div>
					</td>
				</tr>
		</table>
		<!--/договора-->
		<?= partial("contract_print", ["Request" => $Request]) ?>
		<?= partial("contract_licenced_print", ["Request" => $Request]) ?>
		<?= partial("additional_agreement_print") ?>
		<?= partial("additional_agreement_ooo_print") ?>
		<?= partial("act") ?>
		<?= partial("service_ooo_print") ?>
		<?= partial("service_ip_print") ?>
		<?= partial("agreement_termination_ooo") ?>
	</div>
</div>


<!-- ДОГОВОР ТЕСТИРОВАНИЯ -->
<div class="row" ng-show="student !== undefined">
    <div class="col-sm-12">
	    <div style="margin-bottom: 20px; display: block">
			<a ng-click="addContractDialogTest()" class="link-like link-reverse">добавить договор тестирования</a>
	    </div>
		<!--договора-->
		<table class="table table-hover border-reverse last-item-no-border"
			   ng-repeat="id_contract in getContractIdsTest()"
		>
			<tr class="no-hover">
				<td colspan="8" class="no-border-bottom">
					<h4 class="row-header default-case"> Договор №{{ id_contract }}T на {{ firstContractInChainByIdTest(id_contract).info.year + '-' + (firstContractInChainByIdTest(id_contract).info.year + 1) }} учебный год ({{ firstContractInChainByIdTest(id_contract).info.grade_label }})</h4>
				</td>
			</tr>
				<tr ng-repeat="contract in contracts_test | group_by_id_contract:id_contract | orderBy:'date_changed'">
					<td width="20%">версия {{ $index + 1 }} от {{ formatContractDate(contract.date) }}</td>
					<td width="15%">{{ contract.sum | number }} <ng-pluralize count="contract.sum" when="{
						'one': 'рубль',
						'few': 'рубя',
						'many': 'рублей'
					}"></ng-pluralize>
					</td>
					<td width="42%">
						<span
							ng-repeat-start="subject in contract.subjects | orderBy:'id_subject' "
							ng-class="{
										'text-warning'	: subject.status == 2,
										'text-danger'	: subject.status == 1,
									  }">
							{{ Subjects[subject.id_subject] }}</span> ({{ subject.count }})<span ng-repeat-end>{{ $last ? '' : ', '}}
						</span>
					</td>
					<td width="20%">
						создал {{UserService.getLogin(contract.id_user)}} {{formatDate(contract.date_changed) | date:'dd.MM.yyyy'}}
					</td>
					<td align="right" width="3%" class="no-padding-right">
						<span class="link-like" ng-click="contract.show_actions = !contract.show_actions">действия</span>
                        <div ng-show="contract.show_actions" class="emptyClickHandler" ng-click="closeContexMenu()"></div>
						<div ng-show="contract.show_actions" class="contex-menu fadeInUp fadeOutDown">
                            <ul>
                                <li class='link-like' ng-click="createNewContractTest(contract)">создать новую версию</li>
                                <li class='link-like' ng-click="editContractTest(contract)">изменить без проводки</li>
								<li class='link-like' ng-click='printTestArgreement(contract)'>печать договора</li>
								<li class='link-like' ng-click='printTestAct(contract)'>печать акта оказанных услуг</li>
								<li class='link-like' ng-show='contract.id != contract.id_contract || isLastContractInChain(contract)' ng-click='deleteContractTest(contract)'>удалить</li>
							</ul>
                        </div>
					</td>
				</tr>
		</table>
		<!--/договора-->
		<?= printPartial("testing"); ?>
	</div>
</div>
