<div ng-app="Journal" ng-controller="StudentsCtrl" ng-init="<?= $ang_init_data ?>">

<div class="row" style="margin-bottom: 15px">
	<div class="col-sm-12" style="white-space: nowrap">
		<div>
			<span class="day-explain circle-future default"></span> – планируемые занятия
		</div>
		<div>
			<span class="day-explain"></span> – был на занятии
		</div>
		<div>
			<span class="day-explain circle-orange"></span> – опоздал на занятие на 15 минут и более
		</div>
		<div>
			<span class="day-explain vocation"></span> – пропущенные занятия
		</div>
	</div>
</div>


<div class="row" ng-show="Journal.length > 0">
    <div class="col-sm-12">
		 <div ng-repeat="id_group in getJournalGroups()" class="visit-div" style="top: -{{6 * $index}}px">
			 <div class="visit-div-group" ng-class="{'gray-bg': !inActiveGroup(id_group)}">
				<a ng-show="inActiveGroup(id_group)" href="students/groups/edit/{{id_group}}/schedule">Группа №{{id_group}}</a>
				<span ng-show="!inActiveGroup(id_group)">Группа №{{id_group}}</span>
			</div>
			 <div ng-repeat="Visit in getVisitsByGroup(id_group)"
				 class="visit-div-circle default" ng-class="{'gray-bg': !inActiveGroup(id_group)}">
				<span class="circle-default" title="{{formatVisitDate(Visit.lesson_date)}}"
				ng-class="{
					'circle-red'	: Visit.presence == 2,
					'circle-orange'	: Visit.presence == 1 && Visit.late > 0
				}"></span>
			 </div>
			 <div ng-repeat="Visit in getGroup(id_group).Schedule" class="visit-div-circle default">
				 <span class="circle-default circle-future default" title="{{formatVisitDate(Visit.date)}}"></span>
			 </div>
			 <div ng-repeat="i in [] | range:(getMaxVisits() - getVisitsByGroup(id_group).length - getGroup(id_group).Schedule.length)" 
				 class="visit-div-circle default" ng-class="{'gray-bg': !inActiveGroup(id_group)}"> 
				 <span class="circle-default invisible"></span>
			 </div>
		 </div>
    </div>
</div>

<div class="row">
	<div class="col-sm-12">
		при наведении на кружок будет показана дата занятия
	</div>
</div>
</div>