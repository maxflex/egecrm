<style>
	table tr td:not(:first-child) {
		text-align: center;
	}
</style>

<div ng-app="Stats" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="top-links">
		<?php if ($_GET["group"] == "d" || empty($_GET["group"])) { ?>
		<span style="margin-right: 15px; font-weight: bold">дни</span>
		<?php } else { ?>
		<a href="stats/?group=d" style="margin-right: 15px">дни</a>
		<?php } ?>

		<?php if ($_GET["group"] == "w") { ?>
		<span style="margin-right: 15px; font-weight: bold">недели</span>
		<?php } else { ?>
		<a href="stats/?group=w" style="margin-right: 15px">недели</a>
		<?php } ?>

		<?php if ($_GET["group"] == "m") { ?>
		<span style="margin-right: 15px; font-weight: bold">месяцы</span>
		<?php } else { ?>
		<a href="stats/?group=m" style="margin-right: 15px">месяцы</a>
		<?php } ?>

		<?php if ($_GET["group"] == "y") { ?>
		<span style="margin-right: 15px; font-weight: bold">годы</span>
		<?php } else { ?>
		<a href="stats/?group=y" style="margin-right: 15px">годы</a>
		<?php } ?>

		<div class="pull-right">
			<span class="link-like active">итоги</span>
			<a href="stats/payments">платежи клиентов</a>
			<a href="stats/payments/teachers">платежи преподавателям</a>
		</div>
	</div>

	<table class="table table-hover">
		<thead style="font-size: 10px" class="half-black">
			<tr>
				<td>
				</td>
                <td>
					резюме
				</td>
				<td>
					заявок
				</td>
				<td>
					входящих звонков
				</td>
				<td>
					новых договоров
				</td>
				<td>
					новых услуг
				</td>
				<td>
					сумма новых договоров
				</td>
				<td>
					уменьшение услуг
				</td>
				<td>
					увеличение услуг
				</td>
				<td>
					изменение суммы услуг
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
			<?php $index = count(Years::$all) - 1; ?>
			<?php foreach($stats as $date => $stat): ?>
			<tr>
				<td>
					<?php if ($_GET["group"] == "y") { ?>
						<?= Years::$all[$index] ?>–<?= Years::$all[$index] + 1 ?> уч. г.
					<?php } else { ?>
						<?= strftime("%d %b %Y", strtotime($date)) ?>
					<?php } ?>
				</td>
                <td>
					<?= $stat['teachers'] ?: '' ?>
				</td>
				<td>
					<?= $stat['requests'] ?>
				</td>
				<td>
					<?= $stat['incoming_calls'] ?: '' ?>
				</td>
				<td>
					<?= $stat['contract_new']['basic'] ?> <?= $stat['contract_new']['external'] ? "<span class='text-primary'>+ {$stat['contract_new']['external']}</span>" : "" ?>
				</td>
				<td>
					<?= $stat['subjects_new']['basic'] ?> <?= $stat['subjects_new']['external'] ? "<span class='text-primary'>+ {$stat['subjects_new']['external']}</span>" : "" ?>
				</td>
				<td>
					<?= $stat['contract_sum_new']['basic'] ?> <?= $stat['contract_sum_new']['external'] ? "<span class='text-primary'>+ {$stat['contract_sum_new']['external']}</span>" : "" ?>
				</td>
				<td>
					<?= $stat['subjects_minus'] ?>
				</td>
				<td>
					<?= $stat['subjects_plus'] ?>
				</td>
				<td>
					<?= $stat['contract_sum_changed'] ?>
				</td>
				<td>
					<?= $stat['payment_confirmed'] ?: '' ?>
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
			<?php $index--; ?>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if (($total_items / StatsController::PER_PAGE) > 1) :?>
	<pagination
	  ng-model="currentPage"
	  ng-change="pageChanged('<?= $_GET["group"] ?>')"
	  total-items="<?= $total_items ?>"
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
