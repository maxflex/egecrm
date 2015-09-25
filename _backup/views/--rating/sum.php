<table class="table table-hover" ng-app="Rating" ng-controller="MainCtrl" ng-init="<?= $ang_init_data ?>">
		<thead>
			<tr>
				<td></td>
				<?php for ($i = 9; $i <= 11; $i++): ?>
					<td><?= $i ?> класс</td>
				<?php endfor ?>
			</tr>
		</thead>
		
		<tbody>
			<?php foreach (Subjects::$all as $id_subject => $name): ?>
				<tr>
					<td onclick="redirect('rating/subject/<?= $id_subject ?>')" class="pointer"><?= $name ?></td>
					<?php for ($i = 9; $i <= 11; $i++): ?>
						<td>
							<?php if ($result[$i][$id_subject]) :?>
								<?= $result[$i][$id_subject] ?>
							<?php endif ?>
						</td>
					<?php endfor ?>
				</tr>
			<?php endforeach ?>
			<tr>
				<td>всего</td>
				<?php for ($i = 9; $i <= 11; $i++): ?>
					<td><?= $sumRatingGroupedByGrade[$i] ?></td>
				<?php endfor ?>
			</tr>
		</tbody>
		
</table>