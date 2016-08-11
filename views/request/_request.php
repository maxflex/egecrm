<div class="panel panel-primary <?= ($mode != 'request' ? 'ng-hide' : '') ?>" ng-show="mode == 'request'">
		<div class="panel-heading">

			<?php if ($Request->adding) :?>
			Добавление заявки
			<?php else :?>
			Редактирование заявки №<?= $Request->id ?>
			<?php endif ?>


			<div class="pull-right">
				<?php if (!$Request->adding) :?>
					<span class="link-reverse pointer" style="margin-left: 10px" onclick="lightBoxShow('glue')">перенести в другой профиль</span>

					<?php if ($Request->getDuplicates()): ?>
						<span class="link-reverse pointer" style="margin-left: 10px" onclick='deleteRequest(<?= $Request->id ?>)'>удалить заявку</span>
					<?php endif ?>
				<?php endif ?>
			</div>
		</div>
		<div class="panel-body">

	<!-- 	<img src="img/svg/loading-bars.svg" alt="Загрузка страницы..." id="svg-loading"> -->


	<!-- Скрытые поля -->
	<input type="hidden" name="id_request" value="<?= $Request->id ?>">

	<!-- если нажата сохранить, то всегда обнулять  adding -->
	<input type="hidden" name="Request[adding]" value="0">
	<input type="hidden" name="Request[id_student]" id="id_student_force" value="<?= $Request->id_student ?>">

	<input type="hidden" id="subjects_json" name="subjects_json">
	<input type="hidden" id="payments_json" name="payments_json">

	<input type="hidden" ng-value="markerData() | json"  name="marker_data">

	<input type="hidden" name="save_request" value="{{request_comments === undefined ? 0 : 1}}">
	<input type="hidden" name="save_student" value="{{id_student === undefined ? 0 : 1}}">

	<!-- Конец /скрытые поля -->

	<?= globalPartial('loading', ['model' => 'request_comments']) ?>

	<div class="ng-hide" ng-hide="request_comments === undefined">

		<!-- ВКЛАДКИ ЗАЯВОК -->
		<div class="row" style="margin-bottom: 20px" ng-hide="<?= ($Request->adding && !$_GET["id_student"]) ?>">
			<div class="col-sm-12">
				<span class="tab-link" ng-repeat="request_duplicate in request_duplicates" ng-class="{'active' : request_duplicate == <?= $Request->id ?>}">
					<a href="requests/edit/{{request_duplicate}}">Заявка №{{request_duplicate}}</a>
				</span>
				<span class="tab-link" ng-class="{'active' : <?= ($Request->adding && $Request->id_student) ?>}">
					<a href="requests/add?id_student=<?= $Request->id_student ?>">добавить заявку</a>
				</span>
				<div class="top-links pull-right">
				    <span class="link-like active">заявки</span>
				    <span class="link-like" ng-click="setMode('student')">клиент</span>
			    </div>
			</div>
		</div>
		<!-- /ВКЛАДКИ ЗАЯВОК -->

		<!-- ДАННЫЕ ПО ЗАЯВКЕ С САЙТА И УВЕДОМЛЕНИЯ -->
	    <div class="row">
	        <div class="col-sm-9">
	            <div class="row">
	                <div class="col-sm-4">

	                </div>
	            </div>
	            <div class="row" style="margin-bottom: 30px">
			        <div class="col-sm-12">
				        <div class="row">
					        <div class="col-sm-12">
								<textarea class="form-control" placeholder="комментарий" name="Request[comment]"><?= $Request->comment ?></textarea>
					        </div>
				        </div>
				        <div class="row" style="margin-top: 10px">
					        <div class="col-sm-12">
					               <div class="comment-block">
									<div id="existing-comments-{{id_request}}">
										<div ng-repeat="comment in request_comments">
											<div id="comment-block-{{comment.id}}">
												<span style="color: {{comment.User.color}}" class="comment-login">{{comment.User.login}}: </span>
												<div style="display: initial" id="comment-{{comment.id}}" onclick="editComment(this)" commentid="{{comment.id}}">
													{{comment.comment}}</div>
												<span class="save-coordinates">{{comment.coordinates}}</span>
												<span ng-attr-data-id="{{comment.id}}" class="glyphicon opacity-pointer text-danger glyphicon-remove glyphicon-2px"
													onclick="deleteComment(this)"></span>
											</div>
										</div>
									</div>
									<div style="height: 25px">
										<span class="pointer no-margin-right comment-add" id="comment-add-{{id_request}}"
									place="<?= Comment::PLACE_REQUEST ?>" id_place="{{id_request}}">комментировать</span>
										<span class="comment-add-hidden">
											<span class="comment-add-login comment-login" id="comment-add-login-{{id_request}}" style="color: <?= User::fromSession()->color ?>"><?= User::fromSession()->login ?>: </span>
											<input class="comment-add-field" id="comment-add-field-{{id_request}}" type="text"
												placeholder="введите комментарий..." request="{{id_request}}" data-place='REQUEST_EDIT_REQUEST' >
										</span>
									</div>
								</div>
					        </div>
				        </div>
			        </div>
			    </div>
	        </div>
			<div class="col-sm-3">
				<div class="form-group">
					<?= Subjects::buildMultiSelector($Request->subjects, ["id" => "request-subjects", "name" => "Request[subjects][]"], 'three_letters') ?>
				</div>
				<div class="form-group">
	                <?= Grades::buildSelector($Request->grade, "Request[grade]") ?>
	            </div>

	            <div class="form-group">
	                <input placeholder="имя" class="form-control" name="Request[name]" value="<?= $Request->name ?>">
	            </div>

	            <div class="form-group">

					<div class="form-group">
			            <div class="input-group"
				            ng-class="{'input-group-with-hidden-span' : !phoneCorrect('request-phone') || (!isMobilePhone('request-phone') && request_phone_level >= 2) }">
		                	<input ng-keyup id="request-phone" type="text"
		                		placeholder="телефон" class="form-control phone-masked"  name="Request[phone]" value="<?= $Request->phone ?>">
		                	<div class="input-group-btn">
		                				<button class="btn btn-default" ng-show="phoneCorrect('request-phone') && isMobilePhone('request-phone')" ng-click="callSip('request-phone')">
			                				<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
		                				</button>
										<button  ng-show="phoneCorrect('request-phone') && isMobilePhone('request-phone')" ng-class="{
												'addon-bordered' : request_phone_level >= 2 || !phoneCorrect('request-phone')
											}" class="btn btn-default" type="button" onclick="smsDialog('request-phone')">
												<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
										</button>
							        	<button ng-hide="request_phone_level >= 2 || !phoneCorrect('request-phone')" class="btn btn-default" type="button" ng-click="request_phone_level = request_phone_level + 1">
							        		<span class="glyphicon glyphicon-plus no-margin-right small"></span>
							        	</button>
					            </div>
						</div>
					</div>

					<div class="form-group" ng-show="request_phone_level >= 2">
			            <div class="input-group"
				            ng-class="{'input-group-with-hidden-span' : !phoneCorrect('request-phone-2')  || (!isMobilePhone('request-phone') && request_phone_level >= 3) }">
		                	<input ng-keyup id="request-phone-2" type="text"
		                		placeholder="телефон 2" class="form-control phone-masked"  name="Request[phone2]" value="<?= $Request->phone2 ?>">
		                	<div class="input-group-btn">
			                	<button class="btn btn-default" ng-show="phoneCorrect('request-phone-2') && isMobilePhone('request-phone-2')" ng-click="callSip('request-phone-2')">
	                				<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
	            				</button>
								<button ng-show="phoneCorrect('request-phone-2') && isMobilePhone('request-phone-2')" ng-class="{
										'addon-bordered' : request_phone_level >= 3 || !phoneCorrect('request-phone-2')
									}" class="btn btn-default" type="button"  onclick="smsDialog('request-phone-2')">
										<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
								</button>
					        	<button ng-hide="request_phone_level >= 3 || !phoneCorrect('request-phone-2')" class="btn btn-default" type="button" ng-click="request_phone_level = request_phone_level + 1">
					        		<span class="glyphicon glyphicon-plus no-margin-right small"></span>
					        	</button>
				            </div>
						</div>
					</div>


					<div class="form-group" ng-show="request_phone_level >= 3">
						<div class="input-group"
							ng-class="{'input-group-with-hidden-span' : !phoneCorrect('request-phone-3')  || !isMobilePhone('request-phone-3') }">
			                <input type="text" id="request-phone-3" placeholder="телефон 3"
			                	class="form-control phone-masked"  name="Request[phone3]" value="<?= $Request->phone3 ?>">
			                	<div class="input-group-btn">
				                	<button class="btn btn-default" ng-show="phoneCorrect('request-phone-3') && isMobilePhone('request-phone-3')" ng-click="callSip('request-phone-3')">
					                	<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
	                				</button>
									<button ng-show="phoneCorrect('request-phone-3') && isMobilePhone('request-phone-3')" ng-class="{
											!phoneCorrect('request-phone-3')
										}" class="btn btn-default" type="button"  onclick="smsDialog('request-phone-3')">
											<span class="glyphicon glyphicon-envelope no-margin-right" style="font-size: 12px"></span>
									</button>
					            </div>
						</div>
		            </div>



	            </div>

	            <div class="form-group">
	<!--                         <?= Branches::buildSvgSelector($Request->id_branch, ["id" => "request-branch", "name" => "Request[id_branch]"]) ?> -->
					<?= Branches::buildMultiSelector($Request->branches, [
						"id" 	=> "request-branches",
						"name"	=> "Request[branches][]",
					], "филиалы") ?>
	            </div>
				<div class="form-group">
	                <?= RequestStatuses::buildSelector($Request->id_status, "Request[id_status]") ?>
				</div>
	        </div>

	        <?= partial("save_button", ["Request" => $Request]) ?>
	    </div>
	    <!-- /ДАННЫЕ ПО ЗАЯВКЕ С САЙТА И УВЕДОМЛЕНИЯ -->

	</div>

	<!-- ЗАКАРЫВАЕМ СТАРЫЙ PANEL-BODY И ОТКРЫВАЕМ НОВЫЙ -->
	</div></div>