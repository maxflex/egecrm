<style>
	table tr td:not(:first-child) {
		text-align: center;
	}
</style>

<div ng-app="Stats" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="top-links pull-right">
		<a href="stats/visits/total">хронологически</a>
		<span class="link-like active">ученики</span>
		<a href="stats/visits/teachers">преподаватели</a>
		<a href="stats/visits/grades">классы</a>
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
					пришел вовремя
				</td>
				<td>
					опоздал
				</td>
				<td>
					отсутствовал
				</td>
				<td>
					доля пропуска
				</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach($Students as $Student): ?>
			<tr>
				<td>
					<a href="student/<?= $Student->id?>"><?= $Student->last_name ." ". $Student->first_name ." ". $Student->middle_name ?></a>
				</td>
				<td>
					<?= $Student->lesson_count ? $Student->lesson_count : '' ?>
				</td>
				<td>
					<?= $Student->in_time ? $Student->in_time : '' ?>
				</td>
				<td>
					<?= $Student->late_count ? $Student->late_count : '' ?>
				</td>
				<td>
					<?= $Student->abscent_count ? $Student->abscent_count : '' ?>
				</td>
				<td>
					<?= $Student->abscent_percent ? $Student->abscent_percent . '%' : '' ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
</div>