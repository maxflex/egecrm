Оплата Услуг по настоящему Договору <span ng-show='contract.discount > 0'>с учетом скидки {{ contract.discount }}%</span> производится Заказчиком следующим образом:
<ul style='margin: 0'>
    <li ng-repeat="n in [] | range:contract.payments_split">
        {{ contract.payments_split == 1 ? 'единовременный' : '' }} платеж в размере {{ getPaymentPrice(contract, n) | number }} руб.  ({{ splitLessons(contract, n) }} <ng-pluralize count="splitLessons(contract, n)" when="{
            'one' 	: 'занятие',
            'few'	: 'занятия',
            'many'	: 'занятий',
        }"></ng-pluralize>) производится
        <span ng-if='!n'> при заключении договора</span>
        <span ng-if='n'>
             до {{ splitPaymentsOptions(contract.info.year)[contract.payments_info][n - 1] }}
        </span>
    </li>
</ul>
В случае нарушения Заказчиком обязанностей по оплате услуг Исполнителя, согласованных в графике платежей, Исполнитель вправе применить согласованные Сторонами в Договоре меры ответственности за просрочку оплаты.