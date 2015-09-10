<div ng-app="Search" ng-controller="ResultsCtrl" ng-init="<?= $ang_init_data ?>">
	<h4>Ученики</h4>
	<?php foreach($Students as $id=> $Student): ?>
		<div>
			<?= ($id + 1) ?>.
			<a href="student/<?= $Student->id ?>">
				<?= Student::getName($Student->last_name, $Student->first_name, $Student->middle_name) ?>
			</a>
		</div>
	<?php endforeach; ?>
</div>
