<div id="panel-loading">Загрузка...</div>
<form id="request-edit" ng-app="Request" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>" autocomplete='off'>
	
	
	<?= partial('lightboxes', compact('Request')) ?>
	<?= partial('request', compact('Request')) ?>
	<?= partial('student', compact('Request')) ?>

</form>


<style>
table tr td {
  font-size: 14px !important;
}
</style>
