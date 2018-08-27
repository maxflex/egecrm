<div class="row" ng-show="current_menu == 5">
	<?= globalPartial('loading', ['model' => 'Stats', 'message' => 'нет статистики']) ?>
	<div ng-show='Stats'>
		<div class="col-sm-12">
			<h4>Статистика в системе ЕГЭ-Репетитор</h4>
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
		<div class="col-sm-12" style='margin-top: 14px'>
			<h4>Статистика в системе ЕГЭ-Центр</h4>
			<div class="row flex-list" style='width: 500px; margin-bottom: 10px'>
				<div>
					<select class="watch-select form-control search-grades" ng-model="search_stats.grades" ng-change='filterStats()' multiple title='классы' data-multiple-separator=', '>
						<option ng-hide='grade < 8' ng-repeat="(grade, label) in Grades | toArray" value="{{(grade + 1)}}">{{label}}</option>
					</select>
				</div>
				<div>
					<select class="watch-select single-select form-control" ng-model="search_stats.years" ng-change='filterStats()' multiple title='годы' data-multiple-separator=', '>
						<option ng-repeat="year in <?= Years::json() ?>"
							value="{{year}}">{{ yearLabel(year) }}</option>
					</select>
				</div>
			</div>
			<div ng-show="!stats_ec">загрузка...</div>
			<div ng-show="stats_ec && !stats_ec.ec_lesson_count">данные отсутствуют</div>
			<div ng-show="stats_ec && stats_ec.ec_lesson_count" style='position: relative'>
				<div class="div-blocker loading" ng-if="stats_ec_loading"></div>
				<div>Проведено всего занятий: {{ stats_ec.ec_lesson_count }}</div>
				<div ng-show="!stats_ec.ec_review_count">Отзывов нет</div>
				<div ng-show="stats_ec.ec_review_count">
					Средняя оценка по отзывам: {{ stats_ec.ec_review_avg | number : 1 }} (на основе {{ stats_ec.ec_review_count }} <ng-pluralize count='stats_ec.ec_review_count' when="{
						'one':  'отзыва',
						'few':  'отзывов',
						'many': 'отзывов'
					}"></ng-pluralize>)
				</div>
				<div ng-show="stats_ec.abscent_percent !== undefined">Доля пропусков: {{ stats_ec.abscent_percent }}%</div>
				<div ng-show="stats_ec.ec_avg_price">Средняя сумма: {{ stats_ec.ec_avg_price | number}} руб.</div>
				<div ng-show="Stats.ec_efficency">
					Расход: {{ Stats.ec_efficency }}%
				</div>
			</div>
		</div>
	</div>
</div>
