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
	    </div>
    </div>
</div>
