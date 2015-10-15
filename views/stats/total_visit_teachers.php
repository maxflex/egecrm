<style>
	table tr td:not(:first-child) {
		text-align: center;
	}
</style>

<div ng-app="Stats" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="top-links pull-right">
		<a href="stats/visits/students">общая посещаемость</a>
		<span class="link-like active">по преподавателям</span>
	</div>
	
	<table class="table table-hover">
		<thead style="font-weight: bold">
			<tr>
				<td>
				</td>
				<td>
					кол-во занятий
				</td>
				<td>
					были на занятии
				</td>
				<td>
					опоздали
				</td>
				<td>
					пропустили
				</td>
				<td>
					доля пропуска
				</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach($Teachers as $Teacher): ?>
			<tr>
				<td>
					<a href="teachers/edit/<?= $Teacher->id?>"><?= $Teacher->last_name ." ". $Teacher->first_name ." ". $Teacher->middle_name ?></a>
				</td>
				<td>
					<?= $Teacher->lesson_count ? $Teacher->lesson_count : '' ?>
				</td>
				<td>
					<?= $Teacher->visit_count ? $Teacher->visit_count : '' ?>
				</td>
				<td>
					<?= $Teacher->late_count ? $Teacher->late_count : '' ?>
				</td>
				<td>
					<?= $Teacher->abscent_count ? $Teacher->abscent_count : '' ?>
				</td>
				<td>
					<?= $Teacher->visit_count ? $Teacher->late_percent . '%' : '' ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
</div>