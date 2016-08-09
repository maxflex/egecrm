<div class="row" ng-show="current_menu == 5">
    <div class="col-sm-12">
	    <?= globalPartial('loading', ['model' => 'Stats', 'message' => 'нет статистики']) ?>
	    <div ng-show='Stats'>
			<div>Количество клиентов: {{ Stats.clients_count }}</div>
		    <div ng-show="Teacher.margin">Группа маржинальности: М{{ Teacher.margin }}</div>
		    <div>Cредняя оценка: {{ Stats.er_review_avg | number : 1 }} (на основе {{ Stats.er_review_count }} <ng-pluralize count='Stats.er_review_count' when="{
		        'one': 'оценки',
		        'few': 'оценок',
		        'many': 'оценок'
		    }"></ng-pluralize> от учеников и нашей оценки)</div>
		    <div ng-show="Stats.ec_lesson_count > 0">Количество занятий, проведенных в ЕГЭ-Центре: {{ Stats.ec_lesson_count }}</div>
		    <div ng-show="Stats.ec_review_count > 0">Cредняя оценка в ЕГЭ-Центре: {{ Stats.ec_review_avg | number : 1 }} (на основе {{ Stats.ec_review_count }} <ng-pluralize count='Stats.ec_review_count' when="{
		        'one': 'оценки',
		        'few': 'оценок',
		        'many': 'оценок'
		    }"></ng-pluralize>)</div>
		    <div ng-show='Stats.hold_coeff_by_grade[9] > 0'>Коэффициент удержания для 9 класса: {{ Stats.hold_coeff_by_grade[9] }}% из {{ Stats.total_lessons_by_grade[9] }} <ng-pluralize count='Stats.ec_lesson_count' when="{
		        'one': 'занятия',
		        'few': 'занятий',
		        'many': 'занятий'
		    }"></ng-pluralize> ({{ Stats.coeff_total[9] }}%)</div>
		    <div ng-show='Stats.hold_coeff_by_grade[10] > 0'>Коэффициент удержания для 10 класса: {{ Stats.hold_coeff_by_grade[10] }}%  из {{ Stats.total_lessons_by_grade[10] }} <ng-pluralize count='Stats.ec_lesson_count' when="{
		        'one': 'занятия',
		        'few': 'занятий',
		        'many': 'занятий'
		    }"></ng-pluralize> ({{ Stats.coeff_total[10] }}%)</div>
		    <div ng-show='Stats.hold_coeff_by_grade[11] > 0'>Коэффициент удержания для 11 класса: {{ Stats.hold_coeff_by_grade[11] }}%  из {{ Stats.total_lessons_by_grade[11] }} <ng-pluralize count='Stats.ec_lesson_count' when="{
		        'one': 'занятия',
		        'few': 'занятий',
		        'many': 'занятий'
		    }"></ng-pluralize> ({{ Stats.coeff_total[11] }}%)</div>
		    <div ng-show='Stats.hold_coeff > 0'>Общий коэффициент удержания: {{ Stats.hold_coeff }}%  из {{ Stats.ec_lesson_count }} <ng-pluralize count='Stats.ec_lesson_count' when="{
		        'one': 'занятия',
		        'few': 'занятий',
		        'many': 'занятий'
		    }"></ng-pluralize> ({{ Stats.coeff_total[0] }}%)</div>
	    </div>
    </div>
</div>
