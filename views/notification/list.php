<div ng-app="Notification" ng-controller="ListCtrl" 
	ng-init="<?= 
			 angInit("notifications", $Notifications)
			.angInit("noitfication_types", NotificationTypes::$all)
		?>"
>
	<div class="row">
		<div class="col-sm-12">
			<div class="notification-line" ng-repeat="notification in notifications">
				<a href="requests/edit/{{notification.id_request}}">Заявка #{{notification.id_request}}</a>,	
				{{noitfication_types[notification.id_type]}}, 
				{{notification.date}} в {{notification.time}} 
				({{fromNow(notification.timestamp)}})
			</div>
		</div>
	</div>	
</div>
