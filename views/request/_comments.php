<div class="row" ng-show="current_menu == 5">
    <div class="col-sm-12">
	    <?= globalPartial('loading', ['model' => 'student_comments_loaded']) ?>
	    <div class="form-group" ng-show="student_comments_loaded !== undefined">
		    <comments entity-id="student.id" entity-type="STUDENT" user="user"></comments>
	    </div>

    </div>
</div>