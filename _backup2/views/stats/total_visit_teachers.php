<style>
	table tr td:not(:first-child) {
		text-align: center;
	}
</style>

<div ng-app="Stats" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="top-links pull-right">
		<a href="stats/visits/days">по дням</a>
		<a href="stats/visits/students">по ученикам</a>
		<span class="link-like active" style="margin-right: 0">по преподавателям</span>
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
					пришли вовремя
				</td>
				<td>
					опоздали
				</td>
				<td>
					отсутствовали
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
					<?= $Teacher->in_time ? $Teacher->in_time : '' ?>
				</td>
				<td>
					<?= $Teacher->late_count ? $Teacher->late_count : '' ?>
				</td>
				<td>
					<?= $Teacher->abscent_count ? $Teacher->abscent_count : '' ?>
				</td>
				<td>
					<?= $Teacher->abscent_percent ? $Teacher->abscent_percent . '%' : '' ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
</div>