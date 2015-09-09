<form id="teacher-edit" ng-app="Teacher" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>" autocomplete='off'>
	<?= partial("freetime") ?>
	<div class="row">
		<div class="col-sm-3">
			<div class="form-group">
				<input class="form-control" ng-model="Teacher.last_name" placeholder="фамилия">
			</div>
			<div class="form-group">
				<input class="form-control" ng-model="Teacher.first_name" placeholder="имя">
			</div>
			<div class="form-group">
				<input class="form-control" ng-model="Teacher.middle_name" placeholder="отчество">
			</div>
		</div>
		<div class="col-sm-3">
			<div class="form-group">
				<input placeholder="логин" ng-model="Teacher.login" class="form-control">
			</div>
			<div class="form-group">
				<input placeholder="пароль" type="text" ng-model="Teacher.password" class="form-control">
			</div>
			<div class="form-group">
				<input placeholder="email" ng-model="Teacher.email" class="form-control">
			</div>
		</div>
		<div class="col-sm-3">
			<?= Html::phones('teacher') ?>
		</div>
		<div class="col-sm-3">
			<div class="form-group">
<!--
				<select class="form-control" ng-model="Teacher.subjects" multiple id="subjects-select">
					<option selected>предметы</option>
					<option disabled>──────────────</option>
					<option ng-repeat="(id_subject, name) in Subjects" value="{{id_subject}}">
						{{id_subject}}. {{name}}
					</option>
				</select>
-->
	
				<?= Subjects::buildMultiSelector($Teacher->subjects, ["id" => "subjects-select"]) ?>

					
			</div>
			<div class="form-group">
				<input placeholder="оценка эксперта" ng-model="Teacher.expert_mark" class="form-control">
			</div>
			<div class="form-group">
				<div class="input-group">
					<input placeholder="ID в базе" ng-model="Teacher.id_a_pers" class="form-control digits-only">
					<span class="input-group-btn">
			        	<button class="btn btn-default" type="button" ng-disabled="!Teacher.id_a_pers" ng-click="goToTutor()">
			        		<span class="glyphicon glyphicon-user no-margin-right"></span>
			        	</button>
					</span>
				</div>
			</div>
		</div>
	</div>
	<div class="row" style="margin-bottom: 10px">
		<div class="col-sm-9">
			<?php if ($Teacher->id) :?>
	        	<?= Branches::buildMultiSelector($Teacher->branches, ["id" => "teacher-branches"]) ?>
			<?php else :?>
	            <?= Branches::buildSvgSelector($Teacher->branches, [
		            "ng-model" => "Teacher.branches",
		            "id" => "teacher-branches",
		        ], true) ?>
		    <?php endif ?>
		</div>
		<div class="col-sm-3">
			<input ng-model="Teacher.rubbles" placeholder="кол-во рублей за занятие" class="form-control">
		</div>
	</div>
	<div class="row" style="margin-bottom: 10px">
		<div class="col-sm-12">
			<textarea class="form-control" ng-model="Teacher.comment" rows="4"></textarea>
		</div>
	</div>
	<div class="row" style="margin-bottom: 10px" ng-hide="Teacher.branches.length == 0 || Teacher.branches[0] == ''">
		<div class="col-sm-3">
		    <h4 style="margin-top: 0" class="row-header">Свободное время</h4>
		    <div ng-repeat="id_branch in Teacher.branches">
			    <span ng-bind-html="branches_brick[id_branch] | to_trusted" style="width: 50px; display: inline-block"></span>
			    <span ng-repeat="weekday in weekdays" class="group-freetime-block">
					<span class="freetime-bar" ng-repeat="time in weekday.schedule track by $index" 
						ng-class="{
							'empty': !inFreetime2(time, freetime[id_branch][$parent.$index + 1])
						}" ng-hide="time == ''" style="position: relative; top: 3px">
					</span>
				</span>
		    </div>
		    
			<div ng-show="Teacher.schedule_date" class="small" style="margin-top: 13px">актуальность: {{Teacher.schedule_date}}</div>
	        <div style="margin-top: 5px">
	            <span class="link-like link-reverse small" onclick="lightBoxShow('freetime')" 
	            	style="margin-left: 0" ng-hide="!Teacher.branches[0]">редактировать</span>
	        </div>
	        
	    </div>
	</div>
	
	<div class="row" style="margin-top: 10px">
		<div class="col-sm-12 center">
	    	<button class="btn btn-primary save-button" ng-disabled="saving || !form_changed" ng-hide="!Teacher.id" style="width: 100px">
	    		<span ng-show="form_changed">Сохранить</span>
	    		<span ng-show="!form_changed && !saving">Сохранено</span>
	    	</button>
	    	
	    	<button class="btn btn-primary save-button" ng-hide="Teacher.id" style="width: 100px">
				добавить
	    	</button>
	    	
		</div>
	</div>
</form>