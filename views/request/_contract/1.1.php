По настоящему Договору Исполнитель обязуется зачислить Обучающегося на обучение и оказать Обучающемуся образовательные услуги по программе дополнительного обучения по курсу (курсам): <span ng-repeat="subject in getSubjects(contract)">«{{SubjectsFull2[subject.id_subject]}}» ({{ subject.count }} <ng-pluralize count="subject.count" when="{
    'one' 	: 'занятие',
    'few'	: 'занятия',
    'many'	: 'занятий',
}"></ng-pluralize>){{!$last ? ", " : ""}}</span> (далее – «Услуги»), а Заказчик обязуется оплатить указанные Услуги в порядке и на условиях, которые установлены настоящим Договором.