<div class="row" ng-show="current_menu == 5">
    <div class="col-sm-12">
	    <?= globalPartial('loading', ['model' => 'student_comments']) ?>
	    <div class="form-group" ng-show="student_comments !== undefined">
		    <comments entity-id="student.id" entity-type="STUDENT" user="user"></comments>
	    </div>

    </div>
</div>