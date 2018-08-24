Оплата Услуг по настоящему Договору <span ng-show='contract.discount > 0'>с учетом скидки {{ contract.discount }}%</span> производится Заказчиком следующим образом:
<ul style='margin: 0'>
    <li ng-repeat="payment in contract.payments">
        {{ contract.payments.length == 1 ? 'единовременный' : '' }} платеж в размере {{ oneSubjectPrice(contract) * payment.lesson_count | number }} руб.  ({{ payment.lesson_count }} <ng-pluralize count="payment.lesson_count" when="{
            'one' 	: 'занятие',
            'few'	: 'занятия',
            'many'	: 'занятий',
        }"></ng-pluralize>) производится
        <span ng-if='!$index'> при заключении Договора</span>
        <span ng-if='$index'>
             до {{ payment.date }}
        </span>
    </li>
</ul>
В случае нарушения Заказчиком обязанностей по оплате услуг Исполнителя, согласованных в графике платежей, Исполнитель вправе применить согласованные Сторонами в Договоре меры ответственности за просрочку оплаты.
