<div class="center text-gray" style="margin: 50px 0" ng-show="<?= $model ?> === undefined<?= ($message ? " || {$model} === false" : "") ?>">
	<span ng-show="<?= $model ?> === undefined">загрузка...</span>
	<span ng-show="<?= $model ?> === false"><?= $message ?></span>
</div>