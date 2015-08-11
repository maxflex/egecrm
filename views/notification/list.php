<div ng-app="Notification" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="row">
		<div class="col-sm-12">
			<div class="notification-line" ng-repeat="notification in notifications" ng-show="notification.id_request">
				<a href="requests/edit/{{notification.id_request}}">Заявка #{{notification.id_request}}</a>,	
				{{noitfication_types[notification.id_type]}}, 
				{{notification.date}} в {{notification.time}} 
				({{fromNow(notification.timestamp)}})
<!--
				<span class="label label-success hint--right" data-hint="СМС доставлено" ng-show="notification.noted">
					<span class="glyphicon glyphicon-envelope no-margin-right"></span>
				</span>
-->
			</div>
		</div>
	</div>	
</div>
