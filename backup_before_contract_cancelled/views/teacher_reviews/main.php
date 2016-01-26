<div ng-app="TeacherReview" ng-controller="Main" ng-init="<?= $ang_init_data ?>">
	<div ng-show="Teachers.length !== 0">
		<div class="row" style="margin-bottom: 20px">
			<div class="col-sm-12 center">
				<div class="label-red" style="color: black; display: inline-block">Отзывы доступны только администраторам. С помощью отзывов ЕГЭ-Центр отбирает лучших преподавателей. Пишите развернуто!</div>
			</div>
		</div>
		<div ng-repeat="Teacher in Teachers">
			<div class="row">
				<div class="col-sm-4">
					<h4>{{Teacher.last_name}} {{Teacher.first_name}} {{Teacher.middle_name}}</h4>
				</div>
				<div class="col-sm-4" style="padding-top: 15px">
					{{Teacher.lessons_count}} <ng-pluralize count="Teacher.lessons_count" when="{
						'one': 'занятие',
						'few': 'занятия',
						'many': 'занятий',
					}"></ng-pluralize> по {{Subjects[Teacher.id_subject]}}
				</div>
				<div class="col-sm-4">
					<div class="pull-right">
						<span style="margin-right: 10px">Поставьте оценку:</span>
						<span ng-repeat="n in []| range:5">
							<span class="teacher-rating" ng-click="RatingInfo[Teacher.id][Teacher.id_subject].rating = n" ng-class="{'active': RatingInfo[Teacher.id][Teacher.id_subject].rating == n}">{{n}}</span>
						</span>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<textarea maxlength="1024" class="teacher-review-textarea form-control" placeholder="Понравилось: 1) ... 2) ... 3) ...&#10;Не понравилось: 1) ... 2) ... 3) ..." rows="5" ng-model="RatingInfo[Teacher.id][Teacher.id_subject].comment"></textarea>
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
	<div ng-show="Teachers.length === 0">
		<div class="half-black center" style="margin: 50px 0">отзыв можно будет оставить после первого занятия</div>
	</div>
</div>

<style>
	h4 {
	    margin: 15px 0 10px !important;
	}
</style>