<style>
	table tr td:not(:first-child) {
		text-align: center;
	}
</style>

<div ng-app="Stats" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="top-links pull-right">
		<a href="stats/visits/total">хронологически</a>
		<a href="stats/visits/students">ученики</a>
		<a href="stats/visits/teachers">преподаватели</a>
		<span class="link-like active">классы</span>
		<a href="stats/visits/subjects" style="margin-right: 0">предметы</a>
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
			<?php foreach($stats as $grade => $stat): ?>
			<tr>
				<td>
					<?= $grade ?> класс
				</td>
				<td>
					<?= $stat['lesson_count'] ? $stat['lesson_count'] : '' ?>
				</td>
				<td>
					<?= $stat['in_time'] ? $stat['in_time'] : '' ?>
				</td>
				<td>
					<?= $stat['late_count'] ? $stat['late_count'] : '' ?>
				</td>
				<td>
					<?= $stat['abscent_count'] ? $stat['abscent_count'] : '' ?>
				</td>
				<td>
					<?= $stat['abscent_percent'] ? $stat['abscent_percent'] . '%' : '' ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
</div>