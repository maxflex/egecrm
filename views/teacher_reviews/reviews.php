<style>
	#year-fix .dropdown-menu:last-child {
		left: -20px;
	}
	.row.flex-list > div {
		width: 10%;
	}
</style>
<div ng-app="TeacherReview" ng-controller="Reviews" ng-init="<?= $ang_init_data ?>" >
	<div class="panel panel-primary">
	<div class="panel-heading">
		Отзывы
	</div>
	<div class="panel-body">
		<?= globalPartial('reviews_module') ?>
	</div>
</div>
