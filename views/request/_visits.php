<div class="row" ng-show="getStudentGroups().length > 0">
    <div class="col-sm-12">
	     <h4 class="row-header">ПОСЕЩАЕМОСТЬ</h4>
			 <div ng-repeat="id_group in getStudentGroups()" class="visit-div">
				 <div class="visit-div-group">
					<a href="groups/edit/{{id_group}}">Группа №{{id_group}}</a>
				</div>
				 <div ng-repeat="Visit in getVisitsByGroup(id_group)" ng-click="toggleMissingNote(Visit)" 
					 class="visit-div-circle">
					<span class="circle-default" title="{{formatVisitDate(Visit.lesson_date)}}{{(Visit.presence == 1 && Visit.late > 0) ? ', опоздание ' + Visit.late + ' мин.' : ''}}"
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
				 <span style="margin-left: 5px">{{getGroup(id_group).Schedule.length + getVisitsByGroup(id_group).length}}</span>
			 </div>
    </div>
</div>