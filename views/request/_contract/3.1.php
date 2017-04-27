Общая стоимость Услуг Исполнителя по Договору складывается из стоимости {{ subjectCount(contract) }} <ng-pluralize count="subjectCount(contract)" when="{
   'one'	: 'занятия',
   'few'	: 'занятий',
   'many'	: 'занятий',
}"></ng-pluralize>, приобретаемых на момент заключения Договора, и <span ng-show='contract.discount > 0'>с учетом скидки {{ contract.discount }}%</span> составляет {{ getContractSum(contract) | number}} (<span class="m_title">{{numToText(getContractSum(contract))}}</span>) руб.