<style>
	.dropdown-menu > li > a {
		padding: 3px 45px 3px 20px;
	}
	.bootstrap-select.btn-group .dropdown-menu li small {
		right: 10px;
	}
</style>
<div ng-app="Reports" ng-controller="UserListCtrl" ng-init="<?= $ang_init_data ?>" >
	<div class="panel panel-primary">
	<div class="panel-heading">
		Отчёты
		<div class='pull-right'>
			обновлено {{ formatDateTime(reports_updated) }}
			<span class="glyphicon glyphicon-refresh opacity-pointer" ng-click='updateHelperTable()' ng-class="{
		        'spinning': helper_updating
		    }" style="margin: 0 0 0 5px"></span>
		</div>
	</div>
	<div class="panel-body">
		<div class="row flex-list" style="margin-bottom: 15px">
			<div>
				<select class="watch-select single-select form-control" ng-model="search.mode" ng-change='filter()'>
					<option value=""  data-subtext="{{ counts.mode[''] || '' }}">все типы</option>
					<option disabled>──────────────</option>
					<option value="1" data-subtext="{{ counts.mode[1] || '' }}">созданные отчеты</option>
					<option value="2" data-subtext="{{ counts.mode[2] || '' }}">требующие создания</option>
					<option value="3" data-subtext="{{ counts.mode[3] || '' }}">мало занятий</option>
					<option value="4" data-subtext="{{ counts.mode[4] || '' }}">не требующие создания</option>
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
				<select class="watch-select single-select form-control" ng-model="search.available_for_parents"  ng-change='filter()'>
					<option value="" data-subtext="{{ counts.available_for_parents[''] || '' }}">любая доступность</option>
					<option disabled>──────────────</option>
					<option value="1" data-subtext="{{ counts.available_for_parents[1] || '' }}">доступные в ЛК ученика</option>
					<option value="0" data-subtext="{{ counts.available_for_parents[0] || '' }}">не доступные в ЛК ученика</option>
				</select>
			</div>
			<div>
				<select class="watch-select single-select form-control" ng-model="search.email_sent"  ng-change='filter()'>
					<option value="" data-subtext="{{ counts.email_sent[''] || '' }}">все статусы</option>
					<option disabled>──────────────</option>
					<option value="1" data-subtext="{{ counts.email_sent[1] || '' }}">отправленные на e-mail</option>
					<option value="0" data-subtext="{{ counts.email_sent[0] || '' }}">не отправленные на e-mail</option>
				</select>
			</div>
			<div>
				<select class="watch-select single-select form-control" ng-model="search.year" ng-change='filter()'>
					<option value="" data-subtext="{{ counts.year[''] || '' }}">все годы</option>
					<option disabled>────────</option>
					<option ng-repeat="year in <?= Years::json() ?>" 
						data-subtext="{{ counts.year[year] || '' }}"
						value="{{year}}">{{ yearLabel(year) }}</option>
				</select>
			</div>
		</div>
		<div style='position: relative'>
			<div id="frontend-loading"></div>
			<?= globalPartial('reports') ?>
		</div>
		
		<pagination
			ng-show='(Reports && Reports.length) && (counts.all > <?= Report::PER_PAGE ?>)'
			ng-model="current_page"
			ng-change="pageChanged()"
			total-items="counts.all"
			max-size="10"
			items-per-page="<?= Report::PER_PAGE ?>"
			first-text="«"
			last-text="»"
			previous-text="«"
			next-text="»"
	    >
	    </pagination>
	
		<div ng-show="Reports === undefined" style="padding: 100px" class="small half-black center">
			загрузка отчетов...
		</div>
		<div ng-show="Reports === null" style="padding: 100px" class="small half-black center">
			нет отчетов
		</div>
	</div>
</div>
