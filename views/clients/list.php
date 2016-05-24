<style>
	.table tr td {
//		padding-top: 20px !important;
	}
</style>
<div class="panel panel-primary" ng-app="Clients" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">Клиенты с договорами
		<div class="pull-right">
			<span class="link-like link-reverse link-white" ng-click="smsDialog3()">
					групповое SMS</span>
			<span style="margin: 0 7px; display: inline-block; opacity: .1">|</span>
			<span style="margin-right: 7px">сортировать по:</span>
			<span class="link-reverse pointer" ng-click="setOrder(1)" style="margin-right: 7px">имя</span>
			<span class="link-reverse pointer" ng-click="setOrder(2)" style="margin-right: 7px">номер договора</span>
			<span class="link-reverse pointer" ng-click="setOrder(3)">дата заключения</span>
		</div>
	</div>
	<div class="panel-body">
		<div class="top-links">
		    <span class="link-like" ng-click="filter_cancelled = 0" ng-class="{'active': filter_cancelled == 0}">договоры в работе</span>
		    <span class="link-like" ng-click="filter_cancelled = 3" ng-class="{'active': filter_cancelled == 3}">предварительно расторгнутые</span>
		    <span class="link-like" ng-click="filter_cancelled = 1" ng-class="{'active': filter_cancelled == 1}">частично расторгнутые</span>
		    <span class="link-like" ng-click="filter_cancelled = 2" ng-class="{'active': filter_cancelled == 2}">полностью расторгнутые</span>
	    </div>
		<table class="table table-divlike">
			<tr ng-repeat="Student in Students | orderBy:orderStudents():asc | filter:clientsFilter">
				<td style="width: 30%">
					{{$index + 1}}. <a href="student/{{Student.id}}">{{Student.last_name}} {{Student.first_name}} {{Student.middle_name}}</a>
				</td>
				<td  style="width: 15%">
<!-- 					<div ng-repeat="Contract in Student.Contracts"> -->
						<span class="inline-block" style="width: 30px">{{Student.Contract.id}}</span>
						<span ng-show="Student.red_circle">
							<span class="circle-default circle-red" style="top: -.5px"></span>
						</span>
<!-- 					</div> -->
				</td>
				<td  style="width: 15%">
<!-- 					<div ng-repeat="Contract in Student.Contracts"> -->
						{{Student.Contract.grade ? Student.Contract.grade + " класс" : "неизвестно"}}
<!-- 					</div> -->
				</td>
				<td  style="width: 15%">
<!-- 					<div ng-repeat="Contract in Student.Contracts"> -->
						{{Student.Contract.date ? Student.Contract.date : "неизвестно"}}
<!-- 					</div> -->
				</td>
				<td style="width: 15%">
					<span ng-repeat="info in Student.subject_count | orderBy:'status':true" ng-class="{
						'text-success': info.status == 3,
						'text-warning': info.status == 2,
						'text-danger': info.status == 1,
					}">{{info.count}}<span style="color: black !important">{{$last ? '' : '+'}}</span></span>
				</td>
				<td>
					<span ng-show="Student.Remainder.id">{{Student.Remainder.remainder | number}} <ng-pluralize count="Student.Remainder.remainder" when="{
						'one' : 'рубль',
						'few' : 'рубля',
						'many': 'рублей',
					}"></ng-pluralize></span>
				</td>
			</tr>
			<tr>
				<td colspan="5"></td>
				<td><b ng-show="Students">{{remainderSum() | number}} <ng-pluralize count="remainderSum()" when="{
						'one' : 'рубль',
						'few' : 'рубля',
						'many': 'рублей',
					}"></ng-pluralize></b></td>
			</tr>
		</table>

 		<div ng-show="!Students.length" style="padding: 100px" class="small half-black center">
			загрузка клиентов...
		</div>

		<div class="pull-right">
			<b class="text-success">+<?= $without_contract ?></b> <?= pluralize('ученик', 'ученика', 'учеников', $without_contract) ?> без договора
		</div>
	</div>
</div>
