<div class="row" ng-show="teacher_likes.length > 0">
    <div class="col-sm-12">
	     <h4 class="row-header">ПРЕПОДАВАТЕЛИ</h4>
			 <div ng-repeat="like in teacher_likes" style="margin-bottom: 3px" ng-show='like.admin_rating > 0'>
                <div style="width: 250px" class="inline-block">
                    <a href="teachers/edit/{{like.Teacher.id}}" style="display: inline-block">
                        {{like.Teacher.last_name}} {{like.Teacher.first_name}} {{like.Teacher.middle_name}}
                    </a>
                </div>
                <div style="width: 120px" class="inline-block">
                    {{SubjectsFull[like.id_subject]}}
                </div>
                <span class="review-small" ng-class="{
                    'bg-red': like.admin_rating <= 3,
                    'bg-orange': like.admin_rating == 4
                }">{{like.admin_rating}}</span>
				<!-- <span class="text-success"	ng-show="like.rating == 1">нравится</span>
				<span class="text-warning" 	ng-show="like.id_status == 2">средне</span>
				<span class="text-danger"	ng-show="like.id_status == 3">не нравится</span> -->
			 </div>
    </div>
</div>
