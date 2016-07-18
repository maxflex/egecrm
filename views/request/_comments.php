<div class="row" ng-show="current_menu == 5">
    <div class="col-sm-12">
	    <?= globalPartial('loading', ['model' => 'student_comments']) ?>
	    <div class="form-group" ng-show="student_comments !== undefined">
		    <div class="comment-block">
				<div id="existing-comments-{{student.id}}">
					<div ng-repeat="comment in student_comments">
						<div id="comment-block-{{comment.id}}">
							<span style="color: {{comment.User.color}}" class="comment-login">{{comment.User.login}}: </span>
							<div style="display: initial" id="comment-{{comment.id}}" commentid="{{comment.id}}" onclick="editComment(this)">{{comment.comment}}</div>
							<span class="save-coordinates">({{comment.coordinates}})</span>
							<span ng-attr-data-id="{{comment.id}}"
								class="glyphicon opacity-pointer text-danger glyphicon-remove glyphicon-2px" onclick="deleteComment(this)"></span>
						</div>
					</div>
				</div>
				<div style="height: 25px">
					<span class="pointer no-margin-right comment-add" id="comment-add-{{student.id}}"
						place="<?= Comment::PLACE_STUDENT ?>" id_place="{{student.id}}">комментировать</span>
					<span class="comment-add-hidden">
						<span class="comment-add-login comment-login" id="comment-add-login-{{student.id}}" style="color: <?= User::fromSession()->color ?>"><?= User::fromSession()->login ?>: </span>
						<input class="comment-add-field" id="comment-add-field-{{student.id}}" type="text"
							placeholder="введите комментарий..." request="{{student.id}}" data-place='REQUEST_EDIT_STUDENT' >
					</span>
				</div>
		    </div>

	    </div>

    </div>
</div>