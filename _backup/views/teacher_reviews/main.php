<div ng-app="TeacherReview" ng-controller="Main" ng-init="<?= $ang_init_data ?>">
	<div ng-show="Teachers.length > 0">
		<div ng-repeat="Teacher in Teachers">
			<div class="row">
				<div class="col-sm-6">
					<h4 style="margin-top: 20px">{{Teacher.last_name}} {{Teacher.first_name}} {{Teacher.middle_name}}</h4>
				</div>
				<div class="col-sm-6">
					<div class="pull-right">
						<span style="margin-right: 10px">Поставьте оценку:</span>
						<span ng-repeat="n in []| range:5">
							<span class="teacher-rating" ng-click="RatingInfo[Teacher.id].rating = n" ng-class="{'active': RatingInfo[Teacher.id].rating == n}">{{n}}</span>
						</span>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<textarea class="teacher-review-textarea form-control" placeholder="Отзыв..." rows="5" ng-model="RatingInfo[Teacher.id].comment"></textarea>
				</div>
			</div>
		</div>
		<div class="row" style="margin-top: 30px">
			<div class="col-sm-12 center">
				<button class="btn btn-primary" ng-disabled="!form_changed" ng-click="saveReviews()">
					<span ng-show="form_changed">Сохранить</span>
					<span ng-show="!form_changed">Сохранено</span>
				</button>
			</div>
		</div>
	</div>
	<div ng-show="Teachers.length <= 0">
		<div class="half-black center" style="margin: 50px 0">отзыв можно будет оставить после первого занятия</div>
	</div>
</div>