Продолжительность образовательной программы по программе курса
<span ng-repeat="subject in getSubjects(contract)">
		«{{SubjectsFull2[subject.id_subject]}}-{{ contract.info.grade == <?= Grades::EXTERNAL ?> ? 'Э' : contract.info.grade }}-{{ subject.count_program }}» ({{ ceil(subject.count_program * 3) }}
		аудиторных <ng-pluralize count="ceil(subject.count_program * 3)" when="{'one' : 'час', 'few' : 'часа', 'many' : 'часов'}"></ng-pluralize> и {{ ceil(subject.count_program * 1.5) }}
		 <ng-pluralize count="ceil(subject.count_program * 1.5)" when="{'one' : 'час', 'few' : 'часа', 'many' : 'часов'}"></ng-pluralize> на самостоятельную подготовку){{$last ? '.' : ','}}
</span>
 Форма обучения – очная.