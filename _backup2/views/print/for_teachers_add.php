<div ng-app="Print" ng-controller="TeachersCtrl" ng-init="<?= $ang_init_data ?>">
	
	<div class="row" style="margin-bottom: 10px">
		<div class="col-sm-3">
			<select class="form-control" ng-model="PrintTask.id_group" id="id-group" ng-change="changeGroup()">
				<option selected value="">группа</option>
				<option disabled>──────────────</option>
				<option ng-repeat="Group in Groups" value="{{Group.id}}">
					Группа №{{Group.id}}
				</option>
			</select>
		</div>
		<div class="col-sm-3">
			<select class="form-control" ng-model="PrintTask.id_lesson" id="id-lesson">
				<option selected value="">занятие</option>
				<option disabled>──────────────</option>
				<option ng-repeat="Lesson in GroupLessons" value="{{Lesson.id}}">
					{{formatDate(Lesson.date)}} в {{Lesson.time}}
				</option>
			</select>
		</div>
	</div>
	<div class="row" style="position: relative">
		<div class="col-sm-12 center">
			<div class="form-group">
				<div class="form-group">
					<textarea class="form-control" placeholder="комментарий" ng-model="PrintTask.comment" id="comment"></textarea>
				</div>
				<div class="small" style="text-align: right">
					<span class="btn-file link-like link-reverse small" ng-hide="PrintTask.files.length >= 3">
						<span>добавить файл</span>
						<input name="print_file" id="fileupload" type="file" data-url="upload/print/" style="width: 85px; cursor: pointer">
					</span>
					
					
					<div ng-repeat="file in PrintTask.files" class="loaded-file">
						<span style="color: black">{{file.uploaded_name}}</span>
						<a target="_blank" href="files/print/{{file.name}}" class="link-reverse small">скачать</a>
						<span class="link-like link-reverse small" ng-click="deleteTaskFile(PrintTask, $index)">удалить</span>
					</div>
					
				</div>
			</div>
			<button class="btn btn-primary" ng-click="addPrintTask()" style="width: 220px" ng-disabled="adding">
				<span ng-show="!adding">добавить задание на печать</span>
				<span ng-show="adding">добавление...</span>
			</button>
		</div>
	</div>
</div>
