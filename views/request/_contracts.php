<div class="row" ng-show="student !== undefined">
    <div class="col-sm-12">
	    <div style="margin-bottom: 20px; display: block">
			<a ng-click="addContractDialog()" class="link-like link-reverse">добавить</a>
	    </div>
		<!--договора-->
		<style>
				caption {
					color: #000;
					font-size: 1.2em;
				}
				/*.no-last-item-border tr:last td {*/
					/*border-bottom: none!important;*/
				/*}*/

		</style>
		<table class="table table-hover border-reverse no-last-item-border"
			   ng-repeat="id_contract in getContractIds(contracts)"
		>
				<caption>
					Договор №{{ id_contract }}
					на {{ parentContractById(id_contract).info.year + '-' + (parentContractById(id_contract).info.year + 1) }}
					учебный год ({{ parentContractById(id_contract).info.grade }} класс)
				</caption>
				<tr ng-repeat="contract in contracts | group_by_id_contract:id_contract | orderBy:'date_changed' as filtered_contracts">
					<td width="15%">версия {{ $index + 1 }} от {{ formatContractDate(contract.date) }}</td>
					<td width="10%">{{ contract.sum | number }} <ng-pluralize count="contract.sum" when="{
						'one': 'рубль',
						'few': 'рубя',
						'many': 'рублей'
					}"></ng-pluralize>
					</td>
					<td width="35%">
						<span
							ng-repeat-start="subject in contract.subjects | orderBy:'id_subject' "
							ng-class="{
										'text-warning'	: subject.status == 2,
										'text-danger'	: subject.status == 1,
									  }">
							{{ Subjects[subject.id_subject] }}</span> ({{ subject.count }}+{{ subject.count2 }})<span ng-repeat-end>{{ $last ? '' : ', '}}
						</span>
					</td>
					<td width="15%">
						создал {{contract.user_login}} {{formatDate(contract.date_changed) | date:'dd.MM.yyyy'}}
					</td>
					<td align="right">
						<span class="link-like" ng-click="contract.show_actions = !contract.show_actions" ng-class="{'fadeInUp': contract.show_actions, 'fadeOutDown': !contract.show_actions}">действия</span>

						<div ng-show="contract.show_actions" style="position: absolute;" class="contex-menu">
							<!-- ДАГАВАРА -->
							<div>
								<div class="col-sm-5" style="padding: 0">
									<div class="form-group link-like link-reverse" style="margin-bottom: 3px" ng-click="createNewContract(contract)">
										создать новую версию
									</div>
									<div class="form-group link-like link-reverse" style="margin-bottom: 3px" ng-click="editContract(contract)">
										изменить без проводки
									</div>
									<span class='link-like' ng-click="printContract(contract.id)">печать договора ИП</span>
									<span class='link-like' ng-click="printContractLicenced(contract.id)">печать договора ООО</span>
									<span class='link-like' ng-click="printContractAdditional(contract)">печать доп.соглашения ИП</span>
									<span class='link-like' ng-click="printContractAdditionalOoo(contract)">печать доп.соглашения ООО</span>
									<span class='link-like' ng-click="printAct(contract)">акта сдачи-приемки ИП</span>
									<div class='link-like' ng-show='contract.id == contract.id_contract && filtered_contracts.length > 1' ng-click='deleteContract(contract)'>
										удалить
									</div>
								</div>
							</div>
							<!-- /ДАГАВАРА -->
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
    </div>
</div>
