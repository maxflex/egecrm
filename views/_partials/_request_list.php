<div ng-repeat="request in requests | filter:{adding : 0}" class="request-main-list" data-id="{{request.id}}">
	<div class="row">
		<div class="col-sm-10">

			<div>
				<span ng-show="request.id_branch > 0" style="margin-right: 10px">
					<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg-metro">
			    		<circle fill="{{request.Branch.color}}" r="6" cx="7" cy="7"></circle>
					</svg>{{request.Branch.name}}
				</span>

				<span ng-show="request.comment" style="margin-right: 10px">
					{{request.comment}}
				</span>

				<span class="half-black">
					<span ng-show="request.name">
						{{request.name}},
					</span>

					<span ng-show="request.grade > 0">
						{{request.grade}} класс,
					</span>
					
					<span ng-show="request.phone"><span ng-class="{'label-red': request.phone_duplicate}" class="underline-hover inline-block" ng-click="callSip(request.phone_formatted)">{{request.phone_formatted}}</span><span class="glyphicon glyphicon-envelope sms-in-list" ng-click="smsDialog(request.phone_formatted)" ng-show="isMobilePhone(request.phone_formatted)"></span></span><span ng-show="request.phone2">, <span ng-class="{'label-red': request.phone2_duplicate}" class="underline-hover inline-block" ng-click="callSip(request.phone2_formatted)">{{request.phone2_formatted}}</span><span class="glyphicon glyphicon-envelope sms-in-list" ng-click="smsDialog(request.phone2_formatted)" ng-show="isMobilePhone(request.phone2_formatted)"></span></span><span ng-show="request.phone3">, <span ng-class="{'label-red': request.phone3_duplicate}" class="underline-hover inline-block" ng-click="callSip(request.phone3_formatted)">{{request.phone3_formatted}}</span><span class="glyphicon glyphicon-envelope sms-in-list" ng-click="smsDialog(request.phone3_formatted)" ng-show="isMobilePhone(request.phone3_formatted)"></span></span>

				</span>

				<span ng-show="request.list_duplicates > 0" class="label-red" style="margin-left: 10px">
					{{request.total_count}} из них {{request.list_duplicates}}
					<ng-pluralize count="request.list_duplicates" when="{
						'one': 'дубль',
						'few': 'дубля',
						'many': 'дублей',
					}"></ng-pluralize>
				</span>

				<span ng-show="!request.list_duplicates && (request.total_count > 1)" style="margin-left: 10px" class="badge">
					{{request.total_count}}
				</span>

			</div>


			<div style="margin-top: 10px">
				<div class="comment-block">
					<div id="existing-comments-{{request.id}}">
						<div ng-repeat="comment in request.Comments">
							<div id="comment-block-{{comment.id}}">
								<span class="glyphicon glyphicon-stop" style="float: left"></span>
								<div style="display: initial" id="comment-{{comment.id}}" onclick="editComment(this)"  commentid="{{comment.id}}">
									{{comment.comment}}</div>
								<span class="save-coordinates">({{comment.coordinates}})</span>
<!-- 								<span ng-attr-data-id="{{comment.id}}" class="glyphicon opacity-pointer glyphicon-pencil no-margin-right" onclick="editComment(this)"></span> -->
								<span ng-attr-data-id="{{comment.id}}" class="glyphicon opacity-pointer text-danger glyphicon-remove glyphicon-2px" onclick="deleteComment(this)"></span>
							</div>
						</div>
					</div>
					<div style="height: 25px">
						<span class="glyphicon glyphicon-forward pointer no-margin-right comment-add" id="comment-add-{{request.id}}" place="<?= Comment::PLACE_REQUEST ?>" id_place="{{request.id}}"></span>
						<input class="comment-add-field" id="comment-add-field-{{request.id}}" type="text"
							placeholder="Введите комментарий..." request="{{request.id}}" data-place='REQUEST_LIST'>
					</div>
				</div>
			</div>

		</div>

		<div class="col-sm-2" style="text-align: right">
			<div ng-show="request.subjects.length > 0">
				<div ng-repeat="subject in request.subjects">
					{{subjects[subject]}}
				</div>
			</div>
		</div>
	</div>



	<div class="row" style="margin-top: 20px">
		<div class="col-sm-6">
			<div ng-show="request.id_notification > 0 && false">
				Напоминание:  {{notification_types[request.Notification.id_type]}} {{request.Notification.timestamp + "000" | date:'dd.MM.yy в HH:mm'}}
			</div>
			<div class="half-black">
				Заявка №{{request.id}} создана {{request.id_user_created ? users[request.id_user_created].login : "system"}}
				{{request.date_timestamp | date:'dd.MM.yy'}} в <span ng-class="getTimeClass(request)">{{request.date_timestamp | date:'HH:mm'}}</span>
				<a class="link-reverse" style="margin-left: 5px" href="requests/edit/{{request.id}}">редактировать</a>
			</div>
		</div>
		<div class="col-sm-3">
			<select class="user-list small" onchange="changeUserColor(this)" data-rid="{{request.id}}" style="background-color: {{users[request.id_user].color}}">
				<option selected="" value="">пользователь</option>
				<option disabled="" value="">──────────────</option>

				<option ng-repeat="user in users" ng-hide="!user.worktime" style="background-color: {{user.color}}" value="{{user.id}}" ng-selected="user.id == request.id_user">
					{{user.login}}
				</option>
			</select>
		</div>
		<div class="col-sm-3" style="text-align: right" ng-show="request.has_contract">
			<span class="label label-success">договор заключен</span>
		</div>
	</div>
	<hr ng-hide="$last">
</div>
