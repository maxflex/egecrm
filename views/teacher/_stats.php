<div class="row" ng-show="current_menu == 5">
	<?= globalPartial('loading', ['model' => 'Stats', 'message' => 'нет статистики']) ?>
	<div ng-show='Stats'>
		<div class="col-sm-5">
			<h4 class="row-header default-case text-thin">Статистика в системе ЕГЭ-Репетитор</h4>
			<div ng-show="!Stats.clients_count">
				Данные отсутствуют
			</div>
			<div ng-show="Stats.clients_count">
				<div>Компания работает с репетитором: c {{ formatDateMonthName(Stats.er_first_attachment_date, true) }} года</div>
				<div>Клиентов всего: {{ Stats.clients_count }}</div>
				<div ng-show="!Stats.er_review_count">Отзывов нет</div>
				<div ng-show="Stats.er_review_count">
					Средняя оценка по отзывам: {{ Stats.er_review_avg | number : 1 }} (на основе {{ Stats.er_review_count }} <ng-pluralize count='Stats.er_review_count' when="{
						'one': 'отзыва',
						'few': 'отзывов',
						'many': 'отзывов'
					}"></ng-pluralize>)
				</div>
				<div ng-show="Teacher.margin">Группа маржинальности: М{{ Teacher.margin }}</div>
			</div>
		</div>
		<div class="col-sm-5">
			<h4 class="row-header default-case text-thin">Статистика в системе ЕГЭ-Центр</h4>
			<div ng-show="!Stats.ec_lesson_count">данные отсутствуют</div>
			<div ng-show="Stats.ec_lesson_count">
				<div>Проведено всего занятий: {{ Stats.ec_lesson_count }}</div>
				<div ng-show="!Stats.ec_review_count">Отзывов нет</div>
				<div ng-show="Stats.ec_review_count">
					Средняя оценка по отзывам: {{ Stats.ec_review_avg | number : 1 }} (на основе {{ Stats.ec_review_count }} <ng-pluralize count='Stats.ec_review_count' when="{
						'one':  'отзыва',
						'few':  'отзывов',
						'many': 'отзывов'
					}"></ng-pluralize>)
				</div>
				<div ng-show="Stats.abscent_percent !== undefined">Доля пропусков: {{ Stats.abscent_percent }}%</div>
				<div ng-show="Stats.ec_efficency">
					Расход: {{ Stats.ec_efficency }}%
				</div>
			</div>
		</div>
	</div>
</div>
