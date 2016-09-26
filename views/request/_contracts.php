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
			   ng-repeat="id_contract in getContractIds()"
		>
				<caption>
					Договор №{{ id_contract }}
					на {{ firstContractInChainById(id_contract).info.year + '-' + (firstContractInChainById(id_contract).info.year + 1) }}
					учебный год ({{ firstContractInChainById(id_contract).info.grade }} класс)
				</caption>
				<tr ng-repeat="contract in contracts | group_by_id_contract:id_contract | orderBy:'date_changed'">
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
						<span class="link-like" ng-click="contract.show_actions = !contract.show_actions">действия</span>
                        <div ng-show="contract.show_actions" class="emptyClickHandler" ng-click="closeContexMenu()"></div>
						<div ng-show="contract.show_actions" class="contex-menu fadeInUp fadeOutDown">
                            <ul>
                                <li class='link-like' ng-click="createNewContract(contract)">создать новую версию</li>
                                <li class='link-like' ng-click="editContract(contract)">изменить без проводки</li>
                                <li class='link-like' ng-click="printContract(contract)">печать договора ИП</li>
                                <li class='link-like' ng-click="printContractLicenced(contract)">печать договора ООО</li>
                                <li class='link-like' ng-click="printContractAdditional(contract)">печать доп.соглашения ИП</li>
                                <li class='link-like' ng-click="printContractAdditionalOoo(contract)">печать доп.соглашения ООО</li>
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
    </div>
</div>
<style>
    .contex-menu {
        padding: 5px;
		z-index:5;
        position: absolute;
        /*width: 200px;*/
        right: 20px;
        background: white;
        box-shadow: 0px 1px 6px #cdcdcd;
        border-radius: 3px;
        border: 1px solid #aaa;
        text-align: left;
        -vendor-animation-duration: 0.5s;
        -vendor-animation-delay: 0.5s;
    }
    .contex-menu ul {
        padding: 0;
        z-index:10;
    }
    .contex-menu li {
        list-style: none;
        padding: 5px;
	}
	.emptyClickHandler {
		position:fixed;
		top: 0;
		left: 0;
		width:100%;
		height: 100%;
		z-index: 4;
	}
</style>