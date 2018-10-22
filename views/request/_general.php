<div class="custom-dropdown__close" ng-click="grade_year_dropdown = false" ng-show="grade_year_dropdown"></div>
<div ng-show="current_menu == 0">
	<div class="row">
		<?= globalPartial('loading', ['model' => 'student']) ?>
	    <div class="col-sm-12 ng-hide" ng-show="student !== undefined">
			<?php if (User::isTeacher()) :?>
			<div class="div-blocker"></div>
			<?php endif ?>
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
		            <div class="form-group" style="position: relative">
						<input type="hidden" name="Student[grade]" value="{{ student.grade }}">
						<input type="hidden" name="Student[year]" value="{{ student.year }}">

						<select class="form-control" ng-click="grade_year_dropdown = true"></select>
			            <span class="custom-dropdown__label">
			                <span ng-show="!student.grade" class="placeholder-gray">класс и год</span>
			                <span ng-show="student.grade">
			                    {{ getRealGrade() ? Grades[getRealGrade()] : 'класс не указан' }}
			                </span>
			            </span>
			            <div class="custom-dropdown" ng-show="grade_year_dropdown">
			                <div ng-repeat="grade in getGradeIds()" class="custom-dropdown__item" ng-click="selectGrade(grade)">
			                    {{ Grades[grade] }}
			                    <span class="glyphicon glyphicon-ok check-mark" ng-show="grade == student.grade"></span>
			                </div>
			                <div class="custom-dropdown__separator"></div>
			                <div ng-repeat="year in Years" class="custom-dropdown__item" ng-click="selectYear(year)">
			                    {{ yearLabel(year) }}
			                    <span class="glyphicon glyphicon-ok check-mark" ng-show="year == student.year"></span>
			                </div>
						</div>
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
					                <button class="btn btn-default" ng-show="emailFull(representative.email)" ng-click="emailDialog(representative.email)" style='z-index: 100'>
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
		            <?php if (User::byType($Request->Student->id, Student::USER_TYPE, 'count')) :?>
		            <h4 style="margin-top: 0" class="row-header">Прочее</h4>
					<?= partial('photo') ?>
		            <div>
			           <span style="width: 75px; display: inline-block">Входов:</span><?= $Request->Student->login_count ?>
		            </div>
					<div ng-hide="is_teacher">
						<div>
							<span style="width: 73px; display: inline-block">Статус:</span>
							<span ng-show="!student.is_banned">активен</span>
							<span class="text-danger" ng-show="student.is_banned">заблокирован</span>
						</div>
						<div class="form-group" style='margin-top: 20px'>
							<select class="form-control" ng-model="student.id_head_teacher" name="Student[id_head_teacher]">
								<option selected value="0">классный руководитель</option>
								<option disabled>──────────────</option>
								<option ng-repeat="Teacher in Teachers" value="{{Teacher.id}}" ng-selected="Teacher.id == student.id_head_teacher">
									{{Teacher.last_name}} {{Teacher.first_name[0]}}. {{Teacher.middle_name[0]}}.
								</option>
							</select>
						</div>
						<div class="form-group">
							<?= Branches::buildSvgSelector($Request->Student->branches, [
								"name" => "Student[branches][]",
								"ng-model" => "student.branches",
								"id" => "student-branches",
							], true) ?>
						</div>
						<div class="form-group" style="white-space: nowrap">
							<span class="link-like" ng-click="showMap()"><span class="glyphicon glyphicon-map-marker"></span>Метки</span>
							<span class="text-primary">({{markers.length}})</span>
						</div>
					</div>
					<?php endif ?>
			    </div>
		    </div>
	    </div>
	</div>

	<?php if (! User::isTeacher()) :?>
		<?= partial('contracts', compact('Request')) ?>
	<?php endif ?>

	<?= partial('groups', compact('Request')) ?>
</div>
