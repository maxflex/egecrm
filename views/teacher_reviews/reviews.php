<style>
	.dropdown-menu > li > a {
		padding: 3px 45px 3px 20px;
	}
	.bootstrap-select.btn-group .dropdown-menu li small {
		right: 10px;
	}
	#year-fix .dropdown-menu:last-child {
		left: -15px;
	}
</style>
<div ng-app="TeacherReview" ng-controller="Reviews" ng-init="<?= $ang_init_data ?>" >
	<div class="panel panel-primary">
	<div class="panel-heading">
		Отзывы
	</div>
	<div class="panel-body">
		<div class="row flex-list" style="margin-bottom: 15px">
			<div>
				<select class="watch-select single-select form-control" ng-model="search.published" ng-change='filter()'>
					<option value=""  data-subtext="{{ counts.published[''] || '' }}">все типы</option>
					<option disabled>──────────────</option>
					<option value="1"  data-subtext="{{ counts.published[1] || '' }}">опубликованные</option>
					<option value="0"  data-subtext="{{ counts.published[0] || '' }}">не опубликованные</option>
					<option value="2"  data-subtext="{{ counts.published[2] || '' }}">не требующие сбора</option>
				</select>
	        </div>
			<div>
				<select class="watch-select single-select form-control" ng-model="search.id_teacher"  ng-change='filter()'>
					<option value="" data-subtext="{{ counts.teacher[''] || '' }}">все преподаватели</option>
					<option disabled>──────────────</option>
					<option ng-repeat="Teacher in Teachers"
						data-subtext="{{ counts.teacher[Teacher.id] || '' }}"
						value="{{Teacher.id}}">{{ Teacher.last_name }} {{ Teacher.first_name }} {{ Teacher.middle_name }}</option>
				</select>
			</div>
	        <div>
				<select id='subjects-select' class="watch-select form-control single-select" ng-model="search.id_subject" ng-change='filter()'>
					<option value="" data-subtext="{{ counts.subject[''] || '' }}">все предметы</option>
					<option disabled>──────────────</option>
					<option 
						data-subtext="{{ counts.subject[id_subject] || '' }}"
						ng-repeat="(id_subject, name) in three_letters" 
						value="{{id_subject}}">{{ name }}</option>
				</select>
			</div>
			<div>
				<select class="watch-select single-select form-control" ng-model="search.rating" ng-change='filter()'>
					<option value=""  data-subtext="{{ counts.rating[''] || '' }}">оценка ученика</option>
					<option disabled>──────────────</option>
					<option ng-repeat="rating in [1, 2, 3, 4, 5, 0]" value="{{rating}}" data-subtext="{{ counts.rating[rating] || '' }}">{{rating || 'пусто'}}</option>
				</select>
	        </div>
	        <div>
				<select class="watch-select single-select form-control" ng-model="search.admin_rating" ng-change='filter()'>
					<option value=""  data-subtext="{{ counts.admin_rating[''] || '' }}">предварительная оценка</option>
					<option disabled>──────────────</option>
					<option ng-repeat="admin_rating in [1, 2, 3, 4, 5, 0]" value="{{admin_rating}}" data-subtext="{{ counts.admin_rating[admin_rating] || '' }}">{{admin_rating || 'пусто'}}</option>
				</select>
	        </div>
			<div>
				<select class="watch-select single-select form-control" ng-model="search.admin_rating_final" ng-change='filter()'>
					<option value=""  data-subtext="{{ counts.admin_rating_final[''] || '' }}">оценка по окончании курса</option>
					<option disabled>──────────────</option>
					<option ng-repeat="admin_rating_final in [1, 2, 3, 4, 5, 0]" value="{{admin_rating_final}}" data-subtext="{{ counts.admin_rating_final[admin_rating_final] || '' }}">{{admin_rating_final || 'пусто'}}</option>
				</select>
	        </div>
			<div>
				<select class="watch-select single-select form-control" ng-model="search.mode" ng-change='filter()'>
					<option value=""  data-subtext="{{ counts.mode[''] || '' }}">все</option>
					<option disabled>───────</option>
					<option value="1"  data-subtext="{{ counts.mode[1] || '' }}">созданные</option>
					<option value="0"  data-subtext="{{ counts.mode[0] || '' }}">требуется создать</option>
				</select>
	        </div>
	        <div id='year-fix'>
				<select class="watch-select single-select form-control" ng-model="search.year" ng-change='filter()'>
					<option value="" data-subtext="{{ counts.year[''] || '' }}">все годы</option>
					<option disabled>────────</option>
					<option ng-repeat="year in <?= Years::json() ?>" 
						data-subtext="{{ counts.year[year] || '' }}"
						value="{{year}}">{{ yearLabel(year) }}</option>
				</select>
			</div>
		</div>
		<?= partial('module') ?>
	</div>
</div>
