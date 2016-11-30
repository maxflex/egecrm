<div ng-repeat="request in requests | filter:{adding : 0}" class="request-main-list" data-id="{{request.id}}">
	<div class="row">
		<div class="col-sm-10">
			<div>
				<span ng-show="request.branches_data || request.has_contract" style="margin-right: 10px">
					<span ng-class="{'mr3' : !$last}" ng-repeat="branch in request.branches_data"><span class="label label-metro-short" style="background: {{branch.color}}; top: -2px; position: relative">{{branch.short}}</span></span>
					<span ng-show="request.has_contract" class="label label-success" style="position: relative; top: -2px">договор заключен</span>
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
					
					<span ng-show="request.phone">
						<span ng-class="{'label-red': request.phone_duplicate}" class="underline-hover inline-block" ng-click="PhoneService.call(request.phone_formatted)">{{request.phone_formatted}}</span>
						<span class="glyphicon glyphicon-envelope sms-in-list" ng-click="PhoneService.sms(request.phone_formatted)" ng-show="PhoneService.isMobile(request.phone_formatted)"></span>
					</span>
					<span ng-show="request.phone2">,
						<span ng-class="{'label-red': request.phone2_duplicate}" class="underline-hover inline-block" ng-click="PhoneService.call(request.phone2_formatted)">{{request.phone2_formatted}}</span>
						<span class="glyphicon glyphicon-envelope sms-in-list" ng-click="PhoneService.sms(request.phone2_formatted)" ng-show="PhoneService.isMobile(request.phone2_formatted)"></span>
					</span>
					<span ng-show="request.phone3">,
						<span ng-class="{'label-red': request.phone3_duplicate}" class="underline-hover inline-block" ng-click="PhoneService.call(request.phone3_formatted)">{{request.phone3_formatted}}</span>
						<span class="glyphicon glyphicon-envelope sms-in-list" ng-click="PhoneService.sms(request.phone3_formatted)" ng-show="PhoneService.isMobile(request.phone3_formatted)"></span>
					</span>

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
				<comments entity-type='REQUEST' entity-id='request.id' user='user' track-loading="1"></comments>
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
				Заявка №{{ request.id }} создана {{ UserService.getLogin(request.id_user_created) }}
				{{request.date_timestamp | date:'dd.MM.yy'}} в {{request.date_timestamp | date:'HH:mm'}}
				<a class="link-reverse" style="margin-left: 5px" href="requests/edit/{{request.id}}">редактировать</a>
			</div>
		</div>
		<div class="col-sm-6">
			ответственный:
            <span id="request-user-display-{{ request.id }}"
                  class="user-pick"
                  ng-click="pickUser(request, <?= User::fromSession()->id ?>)" style="color: {{ UserService.getColor(request.id_user, 'rgba(0, 0, 0, 0.5)') }}"
            >
                {{ UserService.getLogin(request.id_user) }}
            </span>
		</div>
	</div>
	<hr ng-hide="$last">
</div>

<script>
	$(window).on("mouseup", function() {
		$("[id^='request-user-select-']").hide()
		$("[id^='request-user-display-']").show()
	})
</script>