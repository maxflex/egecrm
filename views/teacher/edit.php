<form id="teacher-edit" ng-app="Teacher" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>" autocomplete='off'>
	<div class="row">
		<div class="col-sm-3">
			<div class="form-group">
				<input class="form-control" ng-model="Teacher.first_name" placeholder="фамилия">
			</div>
			<div class="form-group">
				<input class="form-control" ng-model="Teacher.last_name" placeholder="имя">
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
			<div class="form-group">
	            <div class="input-group" 
		            ng-class="{'input-group-with-hidden-span' : !phoneCorrect('teacher-phone') || (!isMobilePhone('teacher-phone') && teacher_phone_level >= 2) }">
                	<input ng-keyup id="teacher-phone" type="text"
                		placeholder="телефон" class="form-control phone-masked"  ng-model="Teacher.phone">
                	<div class="input-group-btn">
								<button ng-show="phoneCorrect('teacher-phone') && isMobilePhone('teacher-phone')" ng-class="{
										'addon-bordered' : teacher_phone_level >= 2 || !phoneCorrect('teacher-phone')
									}" class="btn btn-default" type="button" onclick="smsDialog('teacher-phone')">
										<span class="glyphicon glyphicon-envelope no-margin-right" style="font-size: 12px"></span>
								</button>
					        	<button ng-hide="teacher_phone_level >= 2 || !phoneCorrect('teacher-phone')" class="btn btn-default" type="button" ng-click="teacher_phone_level = teacher_phone_level + 1">
					        		<span class="glyphicon glyphicon-plus no-margin-right" style="font-size: 12px"></span>
					        	</button>
			            </div>
				</div>
			</div>
		</div>
	</div>
</form>