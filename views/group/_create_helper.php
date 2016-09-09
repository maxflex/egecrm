<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ДОГОВОРА -->
<div class="lightbox-new lightbox-contract-stats" style="padding-bottom: 10px">
	<h4 style="margin-bottom: 10px">СТАТИСТИКА ЗАКЛЮЧЕНИЙ ДОГОВОРОВ</h4>
	<div class="row" ng-show="search.id_subject.length || search.grade || search.year" style="margin-bottom: 20px">
		<div class="col-sm-12">
			Статистика выведена по критериям: 
			<span ng-show="search.year">
				{{ yearLabel(search.year) }}<span ng-show="search.id_subject.length || search.grade">,</span>
			</span>
			<span ng-show="search.id_subject.length">
				<span ng-repeat="id_subject in search.id_subject">{{SubjectsFull[id_subject]}}{{$last ? "" : ", "}}</span><span ng-show="search.grade">,</span>
			</span>
			<span ng-show="search.grade">
				{{search.grade}} класс
			</span>
		</div>
	</div>
	<div class="row center small half-black" ng-show="create_helper_data === null" style="margin: 50px 0 30px">
		загрузка...
	</div>
	<div class="row" style="margin-bottom: 10px" ng-show="create_helper_data !== null">
		<div class="col-sm-12">
			<canvas class="chart chart-bar" chart-data="data" chart-labels="labels" chart-options="options"
				chart-series="series" chart-dataset-override="datasetOverride"></canvas> 
		</div>
	</div>
</div>
<!-- /ЛАЙТБОКС ДОБАВЛЕНИЕ ДОГОВОРА -->