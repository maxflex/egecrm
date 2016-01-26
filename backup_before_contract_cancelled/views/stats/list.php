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
		<a href="stats/?group=d" style="margin-right: 15px">по дням</a>
		<?php } ?>
		
		<?php if ($_GET["group"] == "w") { ?>
		<span style="margin-right: 15px; font-weight: bold">по неделям</span>
		<?php } else { ?>
		<a href="stats/?group=w" style="margin-right: 15px">по неделям</a>
		<?php } ?>
		
		<?php if ($_GET["group"] == "m") { ?>
		<span style="margin-right: 15px; font-weight: bold">по месяцам</span>
		<?php } else { ?>
		<a href="stats/?group=m" style="margin-right: 15px">по месяцам</a>
		<?php } ?>
		
		<?php if ($_GET["group"] == "y") { ?>
		<span style="margin-right: 15px; font-weight: bold">по годам</span>
		<?php } else { ?>
		<a href="stats/?group=y" style="margin-right: 15px">по годам</a>
		<?php } ?>
		
		<div class="pull-right">
			<span class="link-like active">итоговые данные</span>
			<a href="stats/payments">детализация по платежам</a>
		</div>
	</div>
	
	<table class="table table-hover">
		<thead style="font-size: 10px" class="half-black">
			<tr>
				<td>
				</td>
				<td>
					заявок
				</td>
				<td>
					заключенные
				</td>
				<td>
					расторгнутые
				</td>
				<td>
					реанимированные
				</td>
				<td>
					заключенные
				</td>
				<td>
					расторгнутые
				</td>
				<td>
					реанимированные
				</td>
				<td>
					изменение существующих
				</td>
				<td>
					платежи
				</td>
				<td>
					возвраты
				</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach($stats as $date => $stat): ?>
			<tr>
				<td>
					<?= strftime("%d %b %Y", strtotime($date)) ?>
				</td>
				<td>
					<?= $stat['requests'] ?>
				</td>
				<td>
					<?= $stat['contract_new'] ?>
				</td>
				<td>
					<?= $stat['contract_cancelled'] ?>
				</td>
				<td>
					<?= $stat['contract_restored'] ?>
				</td>
				<td>
					<?= $stat['contract_sum_new'] ?>
				</td>
				<td>
					<?= $stat['contract_sum_cancelled'] ?>
				</td>
				<td>
					<?= $stat['contract_sum_restored'] ?>
				</td>
				<td>
					<?= $stat['contract_sum_changed'] ?>
				</td>
				<td>
					<?= $stat['payment_confirmed'] ?>
					<?php if ($stat['payment_unconfirmed'] > 0) :?>
						<span class="quater-black"><?= ($stat['payment_confirmed'] > 0 ? ' + ' : '')?><?= $stat['payment_unconfirmed'] ?></span>
					<?php endif ?>
				</td>
				<td>
					<?= $stat['return_confirmed'] ?>
					<?php if ($stat['return_unconfirmed'] > 0) :?>
						<span class="quater-black"><?= ($stat['return_confirmed'] > 0 ? ' + ' : '')?><?= $stat['return_unconfirmed'] ?></span>
					<?php endif ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
	<?php if ($_GET["group"] == "d" || empty($_GET["group"])) :?>
	<pagination
	  ng-model="currentPage"
	  ng-change="pageChanged()"
	  total-items="<?= Request::timeFromFirst() ?>"
	  max-size="10"
	  items-per-page="<?= StatsController::PER_PAGE ?>"
	  first-text="«"
	  last-text="»"
	  previous-text="«"
	  next-text="»"
	>
	</pagination>
	<?php endif ?>
</div>