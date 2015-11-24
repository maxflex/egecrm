<div ng-app="Testing" ng-controller="AddCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="row">
		<div class="col-sm-3">
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
		<div class="col-sm-9">
			<div ng-repeat="Cabinet in Cabinets">
				<div ng-show="cabinet_load[Cabinet.id]">
					<span style="margin-right: 20px">{{Cabinet.number}}</span>
					<span style="margin-right: 20px" ng-repeat="data in cabinet_load[Cabinet.id]"
						ng-show='data.start_time && data.end_time'>{{data.start_time}} – {{data.end_time}}</span>
				</div>
			</div>
		</div>
	</div>
	
	<div class="row" style="margin-top: 10px">
		<div class="col-sm-3">
			<div ng-repeat="(id_subject, name) in Subjects">
				<div class="inline-block" style="margin-right: 10px">
					<label class="ios7-switch transition-control" style="font-size: 24px; top: 1px">
					    <input type="checkbox" ng-true-value="1" ng-model="Testing.subjects_9[id_subject]" 
					    	ng-disabled="notEnoughTime(minutes_9[id_subject])" 
					    	<?php if ($Testing) :?>ng-checked="subjectChecked(9, id_subject)"<?php endif ?>>
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
		<div class="col-sm-6">
			<div ng-repeat="(id_subject, name) in Subjects">
				<div class="inline-block" style="margin-right: 10px">
					<label class="ios7-switch transition-control" style="font-size: 24px; top: 1px">
					    <input type="checkbox"  ng-model="Testing.subjects_11[id_subject]" ng-true-value="1" 
					    	ng-disabled="notEnoughTime(minutes_11[id_subject])"
					    	<?php if ($Testing) :?>ng-checked="subjectChecked(11, id_subject)"<?php endif ?>>
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
	</div>
	
	<div class="row">
		<div class="col-sm-12 center">
			<?php if ($Testing) :?>
				<button class="btn btn-primary" ng-click="saveTesting()">сохранить</button>
			<?php else :?>
				<button class="btn btn-primary" ng-click="addTesting()">добавить</button>
			<?php endif ?>
		</div>
	</div>
</div>