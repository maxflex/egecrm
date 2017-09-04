<div ng-show="current_menu == 0">
	<div class="row">
		<?= globalPartial('loading', ['model' => 'student']) ?>
	    <div class="col-sm-12 ng-hide" ng-show="student !== undefined">
		    <div class="row">
			    <div class="col-sm-3">
				    <h4 class="row-header" style="margin-top: 0">Ученик</h4>
				    <div class="form-group">
		                <input type="text" placeholder="имя" class="form-control" name="Student[first_name]" ng-model="student.first_name">
		            </div>
		            <div class="form-group">
		                <input type="text" placeholder="фамилия" class="form-control" name="Student[last_name]" ng-model="student.last_name">
		            </div>
		            <div class="form-group">
		                <input type="text" placeholder="отчество" class="form-control" name="Student[middle_name]" ng-model="student.middle_name">
		            </div>
		            <div class="form-group">
						<?=
						   Html::date([
						   	"id" 			=> "student-passport-birthday",
			               	"class"			=> "form-control",
			               	"name"			=> "StudentPassport[date_birthday]",
			               	"placeholder"	=> "дата рождения",
			               	"value"			=> $Request->Student->Passport->date_birthday,
			               ]);
			            ?>
		            </div>
		            <div class="form-group">
		                <?= Grades::buildSelector($Request->Student->grade, "Student[grade]", ["ng-model" => "student.grade"]) ?>
		            </div>
		            <div class="form-group">
			            <div class="input-group" ng-class="{'input-group-with-hidden-span': !emailFull(student.email)}">
			                <input type="text"  placeholder="e-mail" class="form-control email" name="Student[email]" ng-model="student.email">
			                <div class="input-group-btn">
				                <button class="btn btn-default" ng-show="emailFull(student.email)" ng-click="emailDialog(student.email)">
				                	<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
				                </button>
			                </div>
			            </div>
		            </div>

					<div>
						<phones entity="student" entity-type="Student"></phones>
					</div>

					 <div class="form-group">
			            <input class="form-control" ng-model="student.school" name="Student[school]" placeholder="№ школы">
		            </div>

					<div class="form-group">
						<input placeholder="серия" class="form-control half-field passport-number" id="student-passport-series"
							name="StudentPassport[series]" value="<?= $Request->Student->Passport->series ?>">
						<input placeholder="номер" class="form-control half-field pull-right passport-number" id="student-passport-number"
							name="StudentPassport[number]" value="<?= $Request->Student->Passport->number ?>">
		            </div>
			    </div>
			    <div class="col-sm-3">
				    <h4 style="margin-top: 0" class="row-header">Представитель</h4>
				    <div class="form-group">
		                <input type="text" placeholder="имя" class="form-control" name="Representative[first_name]" ng-model="representative.first_name">
		            </div>
		            <div class="form-group">
		                <input type="text" placeholder="фамилия" class="form-control" name="Representative[last_name]"
											ng-model="representative.last_name">
		            </div>
		            <div class="form-group">
		                <input type="text" placeholder="отчество" class="form-control" name="Representative[middle_name]" ng-model="representative.middle_name">
		            </div>
		            <div class="form-group">
		                <input type="text" placeholder="статус" class="form-control" name="Representative[status]" ng-model="representative.status">
		            </div>
		            <div class="form-group">
			            <div class="input-group" ng-class="{'input-group-with-hidden-span': !emailFull(representative.email)}">
			                <input type="text" placeholder="e-mail" class="form-control email" name="Representative[email]" ng-model="representative.email">
								<div class="input-group-btn">
					                <button class="btn btn-default" ng-show="emailFull(representative.email)" ng-click="emailDialog(representative.email)">
					                	<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
					                </button>
								</div>
						</div>
					</div>

					<div>
						<phones entity="representative" entity-type="Representative"></phones>
					</div>
					<div class="form-group">
						 <textarea placeholder="адрес фактического проживания"  style="height: 123px"
						 	class="form-control" name="Representative[address]" ng-model="representative.address">
						 </textarea>
					</div>
				</div>
			    <div class="col-sm-3">
				    <h4 style="margin-top: 0" class="row-header">Паспорт</h4>
				    <div class="form-group">
						<input placeholder="серия" class="form-control half-field passport-number" id="passport-series"
							name="Passport[series]" ng-model="representative.Passport.series">

						<input placeholder="номер" class="form-control half-field pull-right passport-number" id="passport-number"
							name="Passport[number]" ng-model="representative.Passport.number">
		            </div>
		            <div class="form-group">
						<?=
						   Html::date([
						   	"id" 			=> "passport-issue-date",
			               	"class"			=> "form-control",
			               	"name"			=> "Passport[date_issued]",
			               	"placeholder"	=> "когда",
							"ng-model"		=> "representative.Passport.date_issued",
			               ]);
			            ?>
		            </div>
		            <div class="form-group">
		                <textarea style="height: 79px" placeholder="кем выдан" class="form-control" name="Passport[issued_by]"><?= $Request->Student->Representative->Passport->issued_by ?></textarea>
		            </div>
		            <div class="form-group">
			            <input class="form-control" ng-model="representative.Passport.code" name="Passport[code]" placeholder="код подразделения" id="code-podr">
		            </div>
		            <div class="form-group">
						<?=
						   Html::date([
						   	"id" 			=> "passport-birthday",
			               	"class"			=> "form-control",
			               	"name"			=> "Passport[date_birthday]",
			               	"placeholder"	=> "дата рождения",
							"ng-model"		=> "representative.Passport.date_birthday",
			               ]);
			            ?>
		            </div>
		            <div class="form-group">
		                <textarea style="height: 123px" placeholder="адрес" class="form-control" name="Passport[address]" ng-model="representative.Passport.address">
		                </textarea>
		            </div>
			    </div>
				<div class="col-sm-3">
		            <?php if (!empty($Request->Student->login)) :?>
		            <h4 style="margin-top: 0" class="row-header">Данные для входа</h4>
		            <div>
			            <span style="width: 75px; display: inline-block">Логин: </span><i><?= $Request->Student->login ?></i>
		            </div>
		            <div>
			            <span style="width: 75px; display: inline-block">Пароль:</span><i><?= $Request->Student->password ?></i>
		            </div>
		            <div style="margin-bottom: 20px">
			           <span style="width: 75px; display: inline-block">Входов:</span><?= User::getLoginCount($Request->Student->id, Student::USER_TYPE) ?>
		            </div>

		            <div class="form-group">
			            <?= Branches::buildSvgSelector($Request->Student->branches, [
				            "name" => "Student[branches][]",
				            "ng-model" => "student.branches",
				            "id" => "student-branches",
				        ], true) ?>
		            </div>
		            <?php endif ?>
		            <div class="form-group" style="white-space: nowrap">
			            <span class="link-like" ng-click="showMap()"><span class="glyphicon glyphicon-map-marker"></span>Метки</span>
			            <span class="text-primary">({{markers.length}})</span>
		            </div>
			    </div>
		    </div>
	    </div>
	</div>

	<?= partial('contracts', compact('Request')) ?>
	<?= partial('groups', compact('Request')) ?>

</div>
