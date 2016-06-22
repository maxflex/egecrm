<div ng-app="Reports" ng-controller="UserListCtrl" ng-init="<?= $ang_init_data ?>" >
	<div class="panel panel-primary">
	<div class="panel-heading">
		Отчёты
		<span class="pull-right glyphicon glyphicon-refresh opacity-pointer" ng-click='updateHelperTable()' ng-class="{
	        'spinning': helper_updating
	    }" style="top: 3px; margin-right: 0"></span>
	</div>
	<div class="panel-body">
		<div class="row flex-list" style="margin-bottom: 15px">
			<div>
				<select class="watch-select single-select form-control" ng-model="search.year" ng-change='filter()'>
					<option value="">все годы</option>
					<option ng-repeat="year in ['2015', '2016']" value="{{year}}">{{ yearLabel(year) }}</option>
				</select>
			</div>
			<div>
				<select class="watch-select single-select form-control" ng-model="search.mode" ng-change='filter()'>
					<option value="">все типы представления отчетов</option>
					<option value="1">созданные отчеты</option>
					<option value="2">отчеты, требующие создания</option>
					<option value="3">отчеты, не требующие создания</option>
				</select>
	        </div>
	        <div>
				<select id='subjects-select' class="watch-select form-control" ng-model="search.subjects" multiple ng-change='filter()'>
					<option ng-repeat="(subject_id, name) in three_letters" value="{{subject_id}}">{{ name }}</option>
				</select>
			</div>
			<div>
				<select class="watch-select single-select form-control" ng-model="search.id_teacher"  ng-change='filter()'>
					<option value="">все преподаватели</option>
					<option ng-repeat="Teacher in Teachers" value="{{Teacher.id}}">{{ Teacher.last_name }} {{ Teacher.first_name }} {{ Teacher.middle_name }}</option>
				</select>
			</div>
			<div>
				<select class="watch-select single-select form-control" ng-model="search.available_for_parents"  ng-change='filter()'>
					<option value="">доступность в ЛК ученика</option>
					<option value="1">доступные в ЛК ученика</option>
					<option value="0">не доступные в ЛК ученика</option>
				</select>
			</div>
			<div>
				<select class="watch-select single-select form-control" ng-model="search.available_for_parents"  ng-change='filter()'>
					<option value="">статус отправки на email</option>
					<option value="1">отправленные на email</option>
					<option value="0">не отправленные на email</option>
				</select>
			</div>
		</div>
		<div style='position: relative'>
			<div id="frontend-loading"></div>
			<table class="table table-hover">
				<tr ng-repeat="d in data">
					<td width="100">
						<a ng-if='d.id' href="reports/edit/{{d.id}}">Отчёт №{{d.id}}</a>
					</td>
					<td>
						<a href="teachers/edit/{{d.Teacher.id}}">{{d.Teacher.last_name}} {{d.Teacher.first_name}} {{d.Teacher.middle_name}}</a>
					</td>
					<td>
						{{three_letters[d.id_subject]}}
					</td>
					<td>
						<a href="student/{{d.Student.id}}">
							<span ng-show='d.Student.last_name'>{{d.Student.last_name}} {{d.Student.first_name}}</span>
							<span ng-hide='d.Student.last_name'>имя не указано</span>
						</a>
					</td>
					<td>
						{{d.lesson_count}} <ng-pluralize count='d.lesson_count' when="{
							'one': 'занятие',
							'few': 'занятия',
							'many': 'занятий',
						}"></ng-pluralize>
					</td>
					<td>
						<span class="pointer label {{d.force_noreport ? 'label-default' : 'label-danger-red'}}" 
							ng-show="d.lesson_count >= 8 && !d.id"
							ng-click="forceNoreport(d)"
						>
							{{d.force_noreport ? 'отчет не требуется' : 'требуется создание отчета' }}
						</span>
					</td>
				</tr>
			</table>
		</div>
		
		<pagination
			ng-show='(data && data.length) && (count > <?= Report::PER_PAGE ?>)'
			ng-model="current_page"
			ng-change="pageChanged()"
			total-items="count"
			max-size="10"
			items-per-page="<?= Report::PER_PAGE ?>"
			first-text="«"
			last-text="»"
			previous-text="«"
			next-text="»"
	    >
	    </pagination>
	
		<div ng-show="data === undefined" style="padding: 100px" class="small half-black center">
			загрузка отчетов...
		</div>
		<div ng-show="data === null" style="padding: 100px" class="small half-black center">
			нет отчетов
		</div>
	</div>
</div>
