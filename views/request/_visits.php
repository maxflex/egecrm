<div class="row" ng-show="Journal.length > 0">
    <div class="col-sm-12">
	     <h4 class="row-header">ПОСЕЩАЕМОСТЬ</h4>
			 <div ng-repeat="id_group in getJournalGroups()" class="visit-div" style="top: -{{6 * $index}}px">
				 <div class="visit-div-group" ng-class="{'gray-bg': !inActiveGroup(id_group)}">
					<a href="groups/edit/{{id_group}}">Группа №{{id_group}}</a>
				</div>
				 <div ng-repeat="Visit in getVisitsByGroup(id_group)" ng-click="toggleMissingNote(Visit)" 
					 class="visit-div-circle" ng-class="{'gray-bg': !inActiveGroup(id_group)}">
					<span class="circle-default" title="{{formatVisitDate(Visit.lesson_date)}}"
					ng-class="{
						'circle-red'	: Visit.presence == 2,
						'circle-orange'	: Visit.presence == 1 && Visit.late > 0
					}"></span>
					<span ng-show="Visit.missing_note"
					 	class="circle-default circle-future-missing" title="{{formatVisitDate(Visit.lesson_date)}}"></span>
				 </div>
				 <div ng-repeat="Visit in getGroup(id_group).Schedule" ng-click="toggleMissingNote(Visit)" class="visit-div-circle">
					 <span class="circle-default circle-future" title="{{formatVisitDate(Visit.date)}}"></span>
					 <span ng-show="Visit.missing_note"
					 	class="circle-default circle-future-missing" title="{{formatVisitDate(Visit.date)}}"></span>
				 </div>
				 <div ng-repeat="i in [] | range:(getMaxVisits() - getVisitsByGroup(id_group).length - getGroup(id_group).Schedule.length)" class="visit-div-circle" ng-class="{'gray-bg': !inActiveGroup(id_group)}"> 
					 <span class="circle-default invisible"></span>
				 </div>
			 </div>
    </div>
</div>