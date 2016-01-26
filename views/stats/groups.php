<div ng-app="Stats" ng-controller="GroupsCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="row" ng-repeat="Teacher in Teachers" style="margin-bottom: 15px">
		<div class="col-sm-12">
			<div class="row">
				<div class="col-sm-12"><b>{{Teacher.last_name}} {{Teacher.first_name}} {{Teacher.middle_name}}</b></div>
			</div>
			<div class="row" ng-repeat="Group in getTeacherGroups(Teacher.id)">
				<div class="col-sm-12">
					<span style="display: inline-block; width: 80px">
						<a href="groups/edit/{{Group.id}}">группа {{Group.id}}</a>
					</span>
					<span style="display: inline-block; width: 100px">
						{{Subjects[Group.id_subject]}}{{Group.grade ? '-' + Group.grade : ''}}
					</span>
					<span class="half-black" style="display: inline-block; width: 25px" ng-repeat="(date, count) in Group.visits">
						{{count}}
					</span>
					<span style="display: inline-block">
						<span class="text-success">{{Group.green_count}}</span>+<span class="text-warning">{{Group.yellow_count}}</span>+<span class="text-danger">{{Group.red_count}}</span>
					</span>
				</div>
			</div>
		</div>		
	</div>
</div>