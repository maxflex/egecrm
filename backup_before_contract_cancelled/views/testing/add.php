<div ng-app="Testing" ng-controller="AddCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="row">
		<div class="col-sm-3 form-change-control">
			<div class="form-group">
				<select class="form-control" ng-model="Testing.date" ng-change="changeDate()">
					<option selected value="">выберите день</option>
					<option disabled>──────────────</option>
					<option ng-repeat="date in future_dates" value="{{date}}" ng-selected="Testing.date == date">{{formatDay(date)}}</option>
				</select>
			</div>
			<div class="form-group">
				<select class="form-control" ng-model="Testing.cabinet">
					<option selected value="">выберите кабинет</option>
					<option disabled>──────────────</option>
					<option ng-repeat="Cabinet in Cabinets" value="{{Cabinet.id}}" ng-selected="Testing.cabinet == Cabinet.id">{{Cabinet.number}}</option>
				</select>
			</div>
			<div class="form-group">
				<select class="form-control" ng-model="Testing.max_students" id="group-cabinet">
					<option selected value="">максимально человек</option>
					<option disabled>──────────────</option>
					<option ng-repeat="n in [] | range:30" value="{{n}}" ng-selected="Testing.max_students == n">{{n}}</option>
				</select>
			</div>
			<div class="form-group">
				<input class="timemask form-control half-field" placeholder="время начала" ng-model="Testing.start_time">
				<input class="timemask form-control half-field pull-right" placeholder="время конца" ng-model="Testing.end_time">
			</div>
		</div>
		<div class="col-sm-5">
			<div ng-repeat="Cabinet in Cabinets">
<!-- 				<div ng-show="cabinet_load[Cabinet.id]"> -->
				<div ng-show="Testing.date">
					<span style="margin-right: 20px">{{Cabinet.number}}</span>
					<span style="margin-right: 20px" class="quater-black" ng-show='cabinet_load === undefined'>загрузка...</span>
					<span style="margin-right: 20px" ng-repeat="data in cabinet_load[Cabinet.id]"
						ng-show='data.start_time && data.end_time'>{{data.start_time}} – {{data.end_time}}</span>
				</div>
			</div>
		</div>
		<div class="col-sm-3">
			<div class="form-group">
				<angucomplete-alt
		          placeholder="ученик"
		          pause="100"
		          selected-object="selectedStudent"
		          local-data="Students"
		          search-fields="name"
		          title-field="name"
		          minlength="2"
		          input-class="form-control form-control-small"
		          text-searching="Поиск..."
		          text-no-results="Не найдено"
		          match-class="highlight"
		          auto-match="true"
		        />
			</div>
			<div class="form-group">
				<select class="form-control" id="subject-add-student" ng-model="selectedSubjectGrade">
					<option selected value="" ng-selected="!selectedSubjectGrade">предмет-класс</option>
					<option disabled>──────────────</option>
					<option ng-repeat="(id_subject, name) in Subjects" value="{{id_subject}}|9" ng-selected="selectedSubjectGrade == '{{id_subject}}|9'"
						ng-disabled="Testing.subjects_9[id_subject] != 1">{{name}}-9</option>
					<option ng-repeat="(id_subject, name) in Subjects" value="{{id_subject}}|11" ng-selected="selectedSubjectGrade == '{{id_subject}}|11'"
						ng-disabled="Testing.subjects_11[id_subject] != 1">{{name}}-11</option>
				</select>
			</div>
			<div class="form-group">
				<button class="btn btn-default" style="width: 100%" ng-disabled="!selectedStudent || !selectedSubjectGrade" ng-click="addStudent()">добавить</button>
			</div>
		</div>
	</div>
	
	<div class="row" style="margin-top: 10px">
		<div class="col-sm-3 form-change-control">
			<div ng-repeat="(id_subject, name) in Subjects">
				<div class="inline-block" style="margin-right: 10px">
					<label class="ios7-switch transition-control" style="font-size: 24px; top: 1px">
					    <input type="checkbox" ng-true-value="1" ng-false-value="0" ng-model="Testing.subjects_9[id_subject]" ng-change="refreshSelect()"
					    	ng-disabled="notEnoughTime(minutes_9[id_subject])" 
					    >
					    <span class="switch"></span>
					</label> 
				</div>
				<div class="inline-block" style="position: relative; top: -1px; width: 75px" ng-class="{
					'quater-black': notEnoughTime(minutes_9[id_subject])
				}">
					{{name}}-9
				</div>
				<div class="inline-block" ng-class="{
					'quater-black': notEnoughTime(minutes_9[id_subject])
				}">
					{{minutes_9[id_subject]}} минут
				</div>
			</div>
		</div>
		<div class="col-sm-3 form-change-control">
			<div ng-repeat="(id_subject, name) in Subjects">
				<div class="inline-block" style="margin-right: 10px">
					<label class="ios7-switch transition-control" style="font-size: 24px; top: 1px">
					    <input type="checkbox"  ng-model="Testing.subjects_11[id_subject]" ng-true-value="1" ng-false-value="0" ng-change="refreshSelect()"
					    	ng-disabled="notEnoughTime(minutes_11[id_subject])"
					    >
					    <span class="switch"></span>
					</label> 
				</div>
				<div class="inline-block" style="position: relative; top: -1px; width: 75px" ng-class="{
					'quater-black': notEnoughTime(minutes_11[id_subject])
				}">
					{{name}}-11
				</div>
				<div class="inline-block" ng-class="{
					'quater-black': notEnoughTime(minutes_11[id_subject])
				}">
					{{minutes_11[id_subject]}} минут
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group" ng-show="Testing.Students && Testing.Students.length">
				<p><b>Зарегистрированные участники:</b></p>
				<div ng-repeat="Student in Testing.Students">
					{{getStudent(Student.id_student).name}}, {{Subjects[Student.id_subject]}}-{{Student.grade}}<!-- {{SubjectsFull[Student.id_subject]}}, {{Student.grade}} класс -->
					<span class="text-danger opacity-pointer" style="margin-left: 5px" ng-click="deleteStudent(Student.id_student)">удалить</span>
				</div>
			</div>
		</div>
	</div>
	
	<?php if ($Testing) :?>
	<div class="row" style="margin-top: 20px">
		<div class="col-sm-12 form-change-control">
			<div class="inline-block" style="position: relative; top: -1px; width: 230px" ng-class="{
				'quater-black': notEnoughTime(minutes_11[id_subject])
			}">
				Запись на тестирование закрыта
			</div>
			<div class="inline-block" style="margin-right: 10px">
				<label class="ios7-switch red-switch transition-control" style="font-size: 24px; top: 1px">
				    <input type="checkbox"  ng-model="Testing.closed" ng-true-value="1" ng-false-value="0">
				    <span class="switch"></span>
				</label> 
			</div>
		</div>
	</div>
	
	<div class="row" style="margin-top: 10px">
		<div class="col-sm-12">
			<?= Html::comments('Testing', Testing::PLACE) ?>			
		</div>
	</div>

	<?php endif ?>
	
	<div class="row">
		<div class="col-sm-12 center">
			<?php if ($Testing) :?>
				<button class="btn btn-primary" ng-click="saveTesting()" ng-disabled="saving || !form_changed" style="width: 110px">
					<span ng-show="!saving && form_changed">сохранить</span>
					<span ng-show="!saving && !form_changed">сохранено</span>
					<span ng-show="saving">сохранение</span>
				</button>
			<?php else :?>
				<button class="btn btn-primary" ng-click="addTesting()" ng-disabled="adding">добавить</button>
			<?php endif ?>
		</div>
	</div>
</div>