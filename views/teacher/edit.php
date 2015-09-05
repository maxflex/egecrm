<form id="teacher-edit" ng-app="Teacher" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>" autocomplete='off'>
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
	        <?= Branches::buildMultiSelector($Teacher->branches, ["id" => "teacher-branches"]) ?>
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