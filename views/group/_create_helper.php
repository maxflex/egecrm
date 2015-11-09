<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ДОГОВОРА -->
<div class="lightbox-new lightbox-contract-stats" style="padding-bottom: 20px">
	<h4 style="margin-bottom: 20px">СТАТИСТИКА ЗАКЛЮЧЕНИЙ ДОГОВОРОВ</h4>
	<div class="row" ng-show="search.subjects.length || search.grade || search.id_branch" style="margin-bottom: 20px">
		<div class="col-sm-12">
			Статистика выведена по критериям: 
			<span ng-show="search.id_branch">
				{{Branches[search.id_branch]}}<span ng-show="search.subjects.length || search.grade">,</span>
			</span>
			<span ng-show="search.subjects.length">
				<span ng-repeat="id_subject in search.subjects">{{SubjectsFull[id_subject]}}{{$last ? "" : ", "}}</span><span ng-show="search.grade">,</span>
			</span>
			<span ng-show="search.grade">
				{{search.grade}} класс
			</span>
		</div>
	</div>
	<div class="row center small half-black" ng-show="create_helper_data === null" style="margin: 50px 0 30px">
		загрузка...
	</div>
<!--
	<div class="row" style="margin-bottom: 10px" ng-show="create_helper_data !== null">
		<div class="col-sm-3">
		</div>
		<div class="col-sm-3">
			заявок
		</div>
		<div class="col-sm-3">
			договоров
		</div>
	</div>
-->
	<div class="row" ng-repeat="data in create_helper_data">
		<div class="col-sm-3">
			{{getMonthByNumber(data.month)}}
		</div>
		<div class="col-sm-9">
			{{data.count}}
		</div>
<!--
		<div class="col-sm-3">
			{{data.request_count}}
		</div>
		<div class="col-sm-3">
			{{data.contract_count}}
		</div>
-->
	</div>
</div>
<!-- /ЛАЙТБОКС ДОБАВЛЕНИЕ ДОГОВОРА -->