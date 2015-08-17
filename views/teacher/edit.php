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
				<input placeholder="пароль" type="password" ng-model="Teacher.password" class="form-control">
			</div>
			<div class="form-group">
				<input placeholder="email" ng-model="Teacher.email" class="form-control">
			</div>
		</div>
		<div class="col-sm-3">
			<?= Html::phones('teacher') ?>
		</div>
		<div class="col-sm-3">
			<?= Subjects::buildSelector(false, false, ["ng-model" => "Teacher.id_subject"]) ?>
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