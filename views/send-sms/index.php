<div ng-app="Sms" ng-controller="Main" ng-init="<?= $ang_init_data ?>">
	<div class="form-group" style='width: 300px'>
		<phones entity="{}" entity-type="Request"></phones>
	</div>
	<!-- СМС -->
	<sms number='sms_number' templates="full"></sms>
	<!-- /СМС -->
</div>