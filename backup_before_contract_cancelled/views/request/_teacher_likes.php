<div class="row" ng-show="teacher_likes.length > 0">
    <div class="col-sm-12">
	     <h4 class="row-header">ПРЕПОДАВАТЕЛИ</h4>
			 <div ng-repeat="like in teacher_likes">
				<a href="teachers/edit/{{like.Teacher.id}}" style="display: inline-block; width: 300px">
					{{like.Teacher.last_name}} {{like.Teacher.first_name}} {{like.Teacher.middle_name}}
				</a>
				<span class="text-success"	ng-show="like.id_status == 1">нравится</span>
				<span class="text-warning" 	ng-show="like.id_status == 2">средне</span>
				<span class="text-danger"	ng-show="like.id_status == 3">не нравится</span>
			 </div>
    </div>
</div>
