Продолжительность образовательной программы по программе курса
<span ng-repeat="subject in getSubjects(contract)">
		«{{SubjectsFull2[subject.id_subject]}}-{{ contract.info.grade == <?= Grades::EXTERNAL ?> ? 'Э' : contract.info.grade }}-{{ subject.count }}» ({{ ceil(subject.count * 3) }}
		аудиторных <ng-pluralize count="ceil(subject.count * 3)" when="{'one' : 'час', 'few' : 'часа', 'many' : 'часов'}"></ng-pluralize> и {{ ceil(subject.count * 1.5) }}
		 <ng-pluralize count="ceil(subject.count * 1.5)" when="{'one' : 'час', 'few' : 'часа', 'many' : 'часов'}"></ng-pluralize> на самостоятельную подготовку){{$last ? '.' : ','}}
</span>
 Форма обучения – очная.