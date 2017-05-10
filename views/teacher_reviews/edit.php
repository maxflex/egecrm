<div ng-app="TeacherReview" ng-controller="Main" ng-init="<?= $ang_init_data ?>">
	<div class="panel panel-primary">
		<div class="panel-heading">
			Отзыв
			<?php if (User::fromSession()->isUser()) :?>
			 ученика {{Student.last_name}} {{Student.first_name}} {{Student.middle_name}}
			 <?php endif ?> по преподавателю {{Teacher.last_name}} {{Teacher.first_name}} {{Teacher.middle_name}}
			(проведено {{lesson_count}} <ng-pluralize count="lesson_count" when="{
						'one': 'занятие',
						'few': 'занятия',
						'many': 'занятий',
					}"></ng-pluralize> по {{subject_name}})
			<?php if (User::fromSession()->isUser()) :?>
			<div class="pull-right">
				<a href="student/{{Student.id}}#reviews">все отзывы ученика</a>
			</div>
			<?php endif ?>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-sm-8">
					<?php if (User::fromSession()->isUser()) :?>
					<b style="top: 14px; position: relative">Оценка и отзыв ученика (заполняется учеником из его личного кабинета)</b>
					<?php else :?>
					<span style="top: 14px; position: relative">Напишите что понравилось/не понравилось (текст отзыва доступен только для администрации)</span>
					<?php endif ?>
					
				</div>
				<div class="col-sm-4">
					<div class="pull-right">
						<span>оценка преподавателя:</span>
						<span ng-repeat="n in []| range:5">
							<span class="teacher-rating" ng-click="setRating('rating', n)" ng-class="{
								'active': RatingInfo.rating == n,
								'bg-red': RatingInfo.rating <= 3 && RatingInfo.rating == n,
								'bg-orange': RatingInfo.rating == 4 && RatingInfo.rating == n,
							}">{{n}}</span>
						</span>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<textarea maxlength="1024" class="teacher-review-textarea form-control" rows="5" ng-model="RatingInfo.comment"></textarea>
				</div>
			</div>
			
			<?php if (User::fromSession()->isUser()) :?>
				<div class="row">
					<div class="col-sm-8">
						<b style="top: 14px; position: relative">Предварительная оценка и отзыв ученика (заполняется администратором)</b>
					</div>
					<div class="col-sm-4">
						<div class="pull-right">
							<span class="teacher-rating" ng-click="setRating('admin_rating', 6)" ng-class="{
								'bg-gray': RatingInfo.admin_rating == 6
							}">0</span>
							<span ng-repeat="n in []| range:5">
								<span class="teacher-rating" ng-click="setRating('admin_rating', n)" ng-class="{
									'active': RatingInfo.admin_rating == n,
									'bg-red': RatingInfo.admin_rating <= 3 && RatingInfo.admin_rating == n,
									'bg-orange': RatingInfo.admin_rating == 4 && RatingInfo.admin_rating == n,
								}">{{n}}</span>
							</span>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<textarea maxlength="1024" class="teacher-review-textarea form-control" rows="5" ng-model="RatingInfo.admin_comment"></textarea>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-7">
						<b style="top: 14px; position: relative">Оценка и отзыв ученика по окончании занятий (заполняется администратором)</b>
					</div>
					<div class="col-sm-1">
						<span style="top: 14px; position: relative; white-space: nowrap" class="link-like-nocolor" ng-class="{
							'text-danger': RatingInfo.published == 0,
							'text-success': RatingInfo.published == 1
						}" ng-click="toggleEnum(RatingInfo, 'published', enum)">{{ enum[RatingInfo.published] }}</span>
					</div>
					<div class="col-sm-4">
						<div class="pull-right">
							<span class="teacher-rating" ng-click="setRating('admin_rating_final', 6)" ng-class="{
								'bg-gray': RatingInfo.admin_rating_final == 6
							}">0</span>
							<span ng-repeat="n in []| range:5">
								<span class="teacher-rating" ng-click="setRating('admin_rating_final', n)" ng-class="{
									'active': RatingInfo.admin_rating_final == n,
									'bg-red': RatingInfo.admin_rating_final <= 3 && RatingInfo.admin_rating_final == n,
									'bg-orange': RatingInfo.admin_rating_final == 4 && RatingInfo.admin_rating_final == n,
								}">{{n}}</span>
							</span>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<textarea maxlength="1024" class="teacher-review-textarea form-control" rows="5" ng-model="RatingInfo.admin_comment_final"></textarea>
					</div>
				</div>
				
				<div class="row" style="margin-top: 10px" ng-show='RatingInfo.id'>
					<div class="col-sm-4">
						<comments entity-id="RatingInfo.id" entity-type="REVIEW" user="user"></comments>
					</div>
					<div class="col-sm-2">
						ответственный: <span class="user-pick" ng-click="toggleReviewUser()" style="color: {{findUser(id_user_review).color || 'black' }}">{{ findUser(id_user_review).login || "system"}}</span>
					</div>
					<div class="col-sm-4">
						<input placeholder="подпись" class="watch-change form-control" maxlength="150" ng-model="RatingInfo.signature">
					</div>
					<div class="col-sm-2 inline-inputs">
						<input placeholder="балл" class="form-control digits-only watch-change" maxlength="3" ng-model="RatingInfo.score">
                        <span style="display:inline-block;margin:0 10px;">из</span>
					    <input placeholder="балл" class="form-control digits-only watch-change" maxlength="3" ng-model="RatingInfo.max_score">
					</div>
				</div>
			<?php endif ?>

            <style>
                .inline-inputs input {
                    display: inline-block;
                    width: 35%;
                }
            </style>
			
			<div class="row" style="margin-top: 30px">
				<div class="col-sm-12 center">
					<button class="btn btn-primary" ng-disabled="!form_changed" ng-click="saveReviews()">
						<span ng-show="!RatingInfo.id">Cоздать</span>
						<span ng-show="RatingInfo.id && form_changed">Cохранить</span>
						<span ng-show="RatingInfo.id && !form_changed">Cохранено</span>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
