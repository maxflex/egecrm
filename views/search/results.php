<div ng-app="Search" ng-controller="ResultsCtrl" ng-init="<?= $ang_init_data ?>">
	<h4>Ученики</h4>
	<?php foreach($Students as $id=> $Student): ?>
		<div>
			<?= ($id + 1) ?>.
			<a href="requests/edit/<?= $Student->getRequest()->id ?>">
				<?= empty(trim($Student->fio())) ? "Неизвестно" : $Student->fio() ?>
			</a>
		</div>
	<?php endforeach; ?>
</div>
