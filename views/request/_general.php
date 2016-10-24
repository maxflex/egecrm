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
			        	<div class="form-group">
				            <div class="input-group"
					            ng-class="{'input-group-with-hidden-span' : !phoneCorrect('student-phone')  || (!isMobilePhone('student-phone') && student_phone_level >= 2) }">
			                	<input ng-keyup id="student-phone" type="text"
			                		placeholder="телефон" class="form-control phone-masked"  name="Student[phone]" ng-model="student.phone">
			                	<div class="input-group-btn">
					                	<button class="btn btn-default" ng-show="phoneCorrect('student-phone') && isMobilePhone('student-phone')" ng-click="callSip('student-phone')">
						                	<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
		                				</button>
										<button ng-show="phoneCorrect('student-phone') && isMobilePhone('student-phone')" ng-class="{
												'addon-bordered' : student_phone_level >= 2 || !phoneCorrect('student-phone')
											}" class="btn btn-default" type="button" onclick="smsDialog('student-phone')">
												<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
										</button>
							        	<button ng-hide="student_phone_level >= 2 || !phoneCorrect('student-phone')" class="btn btn-default" type="button" ng-click="student_phone_level = student_phone_level + 1">
							        		<span class="glyphicon glyphicon-plus no-margin-right small"></span>
							        	</button>
						            </div>
							</div>
						</div>

						<div class="form-group" ng-show="student_phone_level >= 2">
				            <div class="input-group"
					            ng-class="{'input-group-with-hidden-span' : !phoneCorrect('student-phone-2')  || (!isMobilePhone('student-phone-2') && student_phone_level >= 3) }">
			                	<input ng-keyup id="student-phone-2" type="text"
			                		placeholder="телефон 2" class="form-control phone-masked"  name="Student[phone2]" ng-model="student.phone2">
			                	<div class="input-group-btn">
				                	<button class="btn btn-default" ng-show="phoneCorrect('student-phone-2') && isMobilePhone('student-phone-2')" ng-click="callSip('student-phone-2')">
					                	<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
	                				</button>
									<button ng-show="phoneCorrect('student-phone-2') && isMobilePhone('student-phone-2')" ng-class="{
											'addon-bordered' : student_phone_level >= 3 || !phoneCorrect('student-phone-2')
										}" class="btn btn-default" type="button"  onclick="smsDialog('student-phone-2')">
											<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
									</button>
						        	<button ng-hide="student_phone_level >= 3 || !phoneCorrect('student-phone-2')" class="btn btn-default" type="button" ng-click="student_phone_level = student_phone_level + 1">
						        		<span class="glyphicon glyphicon-plus no-margin-right small"></span>
						        	</button>
					            </div>
							</div>
						</div>


						<div class="form-group" ng-show="student_phone_level >= 3">
							<div class="input-group" ng-class="{'input-group-with-hidden-span' : !phoneCorrect('student-phone-3') || !isMobilePhone('student-phone-3') }">
				                <input type="text" id="student-phone-3" placeholder="телефон 3"
				                	class="form-control phone-masked"  name="Student[phone3]" ng-model="student.phone3">
				                	<div class="input-group-btn">
					                	<button class="btn btn-default" ng-show="phoneCorrect('student-phone-3') && isMobilePhone('student-phone-3')" ng-click="callSip('student-phone-3')">
						                	<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
		                				</button>
										<button ng-show="phoneCorrect('student-phone-3') && isMobilePhone('student-phone-3')" ng-class="{
												!phoneCorrect('student-phone-3')
											}" class="btn btn-default" type="button"  onclick="smsDialog('student-phone-3')">
												<span class="glyphicon glyphicon-envelope no-margin-right" style="font-size: 12px"></span>
										</button>
						            </div>
							</div>
			            </div>
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
			        	<div class="form-group">
				            <div class="input-group"
				ng-class="{'input-group-with-hidden-span' : !phoneCorrect('representative-phone')  || (!isMobilePhone('representative-phone') && representative_phone_level >= 2)  }">
			                	<input ng-keyup id="representative-phone" type="text"
			                		placeholder="телефон" class="form-control phone-masked"  name="Representative[phone]" ng-model="representative.phone">
			                	<div class="input-group-btn">
				                	<button class="btn btn-default" ng-show="phoneCorrect('representative-phone') && isMobilePhone('representative-phone')"
				                		ng-click="callSip('representative-phone')">
					                	<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
	                				</button>
									<button ng-show="phoneCorrect('representative-phone') && isMobilePhone('representative-phone')" ng-class="{
											'addon-bordered' : representative_phone_level >= 2 || !phoneCorrect('representative-phone')
										}" class="btn btn-default" type="button" onclick="smsDialog('representative-phone')">
											<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
									</button>
						        	<button ng-hide="representative_phone_level >= 2 || !phoneCorrect('representative-phone')" class="btn btn-default" type="button" ng-click="representative_phone_level = representative_phone_level + 1">
						        		<span class="glyphicon glyphicon-plus no-margin-right small"></span>
						        	</button>
						            </div>
							</div>
						</div>

						<div class="form-group" ng-show="representative_phone_level >= 2">
				            <div class="input-group"
				ng-class="{'input-group-with-hidden-span' : !phoneCorrect('representative-phone-2')  || (!isMobilePhone('representative-phone-2') && representative_phone_level >= 3)  }">
			                	<input ng-keyup id="representative-phone-2" type="text"
			                		placeholder="телефон 2" class="form-control phone-masked"  name="Representative[phone2]" ng-model="representative.phone2">
			                	<div class="input-group-btn">
				                	<button class="btn btn-default" ng-show="phoneCorrect('representative-phone-2') && isMobilePhone('representative-phone-2')"
				                		ng-click="callSip('representative-phone-2')">
					                	<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
	                				</button>
									<button ng-show="phoneCorrect('representative-phone-2') && isMobilePhone('representative-phone-2')" ng-class="{
											'addon-bordered' : representative_phone_level >= 3 || !phoneCorrect('representative-phone-2')
										}" class="btn btn-default" type="button"  onclick="smsDialog('representative-phone-2')">
											<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
									</button>
						        	<button ng-hide="representative_phone_level >= 3 || !phoneCorrect('representative-phone-2')" class="btn btn-default" type="button" ng-click="representative_phone_level = representative_phone_level + 1">
						        		<span class="glyphicon glyphicon-plus no-margin-right small"></span>
						        	</button>
					            </div>
							</div>
						</div>


						<div class="form-group" ng-show="representative_phone_level >= 3">
							<div class="input-group"
				ng-class="{'input-group-with-hidden-span' : !phoneCorrect('representative-phone-3')  || !isMobilePhone('representative-phone-3')  }">
				                <input type="text" id="representative-phone-3" placeholder="телефон 3"
				                	class="form-control phone-masked"  name="Representative[phone3]" ng-model="representative.phone3">
				                	<div class="input-group-btn">
					                	<button class="btn btn-default" ng-show="phoneCorrect('representative-phone-3') && isMobilePhone('representative-phone-3')"
					                		ng-click="callSip('representative-phone-3')">
						                	<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
		                				</button>
										<button ng-show="phoneCorrect('representative-phone-3') && isMobilePhone('representative-phone-3')" ng-class="{
												!phoneCorrect('representative-phone-3')
											}" class="btn btn-default" type="button"  onclick="smsDialog('representative-phone-3')">
												<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
										</button>
						            </div>
							</div>
			            </div>
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

		            <h4 style="margin-top: 78px" class="row-header">График</h4>
			            <div class="row">
				            <div class="col-sm-12">
					            свободно:<br>
					            <span ng-repeat="(day, data) in FreetimeBar" class="group-freetime-block">
									<span ng-repeat="(id_time, bar) in data track by $index" ng-click="toggleStudentFreetime(day, id_time)" class="pointer bar {{bar}}"></span>
								</span>
				            </div>
			            </div>
						<div class="row" style="margin-top: 10px">
				            <div class="col-sm-4" style="white-space: nowrap">
					            занято в группах:<br>
					            <span ng-repeat="(day, data) in GroupsBar" class="group-freetime-block">
									<span ng-repeat="bar in data | toArray track by $index" class="bar {{bar}}"></span>
								</span>
				            </div>
			            </div>

			    </div>
		    </div>
	    </div>
	</div>

	<?= partial('contracts', compact('Request')) ?>
	<?= partial('groups', compact('Request')) ?>

</div>
