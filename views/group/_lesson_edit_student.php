<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
<div class="lightbox-new lightbox-edit-student">
	<h4>{{EditStudent.last_name}} {{EditStudent.first_name}}</h4>
		<div ng-hide="Lesson.is_conducted && !isAdmin">
			<div class="form-group inline-block">
				<select class="form-control" ng-model="EditLessonData.presence" style="width: 150px; margin-right: 20px">
					<option ng-repeat="(id, status) in lesson_statuses" value="{{id}}">
						{{status}}
					</option>
				</select>
			</div>
			<div class="form-group inline-block">
				<input ng-model="EditLessonData.late" placeholder="опоздание" class="form-control digits-only"
					style="width: 150px">
			</div>
		</div>
		<div class="form-group" ng-show="isAdmin">
			<input ng-model="EditLessonData.price" placeholder="цена" class="form-control digits-only">
		</div>
		<div class="form-group">
			<textarea ng-model="EditLessonData.comment" placeholder="комментарий" class="form-control" rows="3" maxlength="1000"></textarea>
		</div>
	<center>
		<button class="btn btn-primary ajax-payment-button" ng-click="saveStudent()">Сохранить</button>
	</center>
</div>
<!-- /ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
