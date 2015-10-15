<style>
	table tr td:not(:first-child) {
		text-align: center;
	}
</style>

<div ng-app="Stats" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="top-links">
		<?php if ($_GET["group"] == "d" || empty($_GET["group"])) { ?>
		<span style="margin-right: 15px; font-weight: bold">по дням</span>
		<?php } else { ?>
		<a href="stats/visits/students?group=d" style="margin-right: 15px">по дням</a>
		<?php } ?>
		
		<?php if ($_GET["group"] == "w") { ?>
		<span style="margin-right: 15px; font-weight: bold">по неделям</span>
		<?php } else { ?>
		<a href="stats/visits/students?group=w" style="margin-right: 15px">по неделям</a>
		<?php } ?>
		
		<?php if ($_GET["group"] == "m") { ?>
		<span style="margin-right: 15px; font-weight: bold">по месяцам</span>
		<?php } else { ?>
		<a href="stats/visits/students?group=m" style="margin-right: 15px">по месяцам</a>
		<?php } ?>
		
		<?php if ($_GET["group"] == "y") { ?>
		<span style="margin-right: 15px; font-weight: bold">по годам</span>
		<?php } else { ?>
		<a href="stats/visits/students?group=y" style="margin-right: 15px">по годам</a>
		<?php } ?>
		
		<div class="pull-right">
			<span class="link-like active">общая посещаемость</span>
			<a href="stats/visits/teachers">по преподавателям</a>
		</div>
		
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
			<?php foreach($stats as $date => $stat): ?>
			<tr>
				<td>
					<?= strftime("%d %b %Y", strtotime($date)) ?>
					<?php if (in_array($date, $errors)) :?>
						<span class="text-danger glyphicon glyphicon-exclamation-sign"></span>
					<?php endif ?>
				</td>
				<td>
					<?= $stat['lesson_count'] ? $stat['lesson_count'] : '' ?>
				</td>
				<td>
					<?= $stat['visit_count'] ? $stat['visit_count'] : '' ?>
				</td>
				<td>
					<?= $stat['late_count'] ? $stat['late_count'] : '' ?>
				</td>
				<td>
					<?= $stat['abscent_count'] ? $stat['abscent_count'] : '' ?>
				</td>
				<td>
					<?= $stat['visit_count'] ? $stat['late_percent'] . '%' : '' ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
	<?php if ($_GET["group"] == "d" || empty($_GET["group"])) :?>
<!--
	<pagination
	  ng-model="currentPage"
	  ng-change="pageStudentChanged()"
	  total-items="<?= round(VisitJournal::fromFirstLesson() / StatsController::PER_PAGE) ?>"
	  max-size="10"
	  items-per-page="<?= StatsController::PER_PAGE ?>"
	  first-text="«"
	  last-text="»"
	  previous-text="«"
	  next-text="»"
	>
	</pagination>
-->
	<?php endif ?>
</div>