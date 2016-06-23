<script src='https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.3/ace.js'></script>

<div ng-app="Teacher" ng-controller="FaqCtrl" ng-init="<?= $ang_init_data ?>">
	<div id='editor' style="height: 300px">{{html}}</div>
	<center style="margin-top: 10px">
		<button class="btn btn-primary" ng-click='save()'>Сохранить</button>
	</center>
</div>