<div ng-app="Search" ng-controller="ResultsCtrl" ng-init="<?= $ang_init_data ?>">
	<h4>Ученики</h4>
	<?php if (!count($Students)) :?>
	<div class="text-gray">
		результатов не найдено
	</div>
	<?php endif ?>
	<?php foreach($Students as $id=> $Student): ?>
		<div>
			<?= ($id + 1) ?>.
			<a href="student/<?= $Student->id ?>">
				<?= Student::getName($Student->last_name, $Student->first_name, $Student->middle_name) ?>
			</a>
		</div>
	<?php endforeach; ?>
	
	<h4 style="margin-top: 30px">Преподаватели</h4>
	<?php if (!count($Teachers)) :?>
	<div class="text-gray">
		результатов не найдено
	</div>
	<?php endif ?>
	<?php foreach($Teachers as $id=> $Teacher): ?>
		<div>
			<?= ($id + 1) ?>.
			<a href="teachers/edit/<?= $Teacher->id ?>">
				<?= getName($Teacher->last_name, $Teacher->first_name, $Teacher->middle_name) ?>
			</a>
		</div>
	<?php endforeach; ?>
</div>
