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
		<a href="stats/payments/<?= isset($_GET['teachers']) ? 'teachers/' : '' ?>?group=d" style="margin-right: 15px">дни</a>
		<?php } ?>

		<?php if ($_GET["group"] == "w") { ?>
		<span style="margin-right: 15px; font-weight: bold">недели</span>
		<?php } else { ?>
		<a href="stats/payments/<?= isset($_GET['teachers']) ? 'teachers/' : '' ?>?group=w" style="margin-right: 15px">недели</a>
		<?php } ?>

		<?php if ($_GET["group"] == "m") { ?>
		<span style="margin-right: 15px; font-weight: bold">месяцы</span>
		<?php } else { ?>
		<a href="stats/payments/<?= isset($_GET['teachers']) ? 'teachers/' : '' ?>?group=m" style="margin-right: 15px">месяцы</a>
		<?php } ?>

		<?php if ($_GET["group"] == "y") { ?>
		<span style="margin-right: 15px; font-weight: bold">годы</span>
		<?php } else { ?>
		<a href="stats/payments/<?= isset($_GET['teachers']) ? 'teachers/' : '' ?>?group=y" style="margin-right: 15px">годы</a>
		<?php } ?>

		<div class="pull-right">
			<a href="stats">итоги</a>
			<a href='stats/payments' class="<?= isset($_GET['teachers']) ?: 'active' ?>">платежи клиентов</a>
			<a href='stats/payments/teachers' class="<?= isset($_GET['teachers']) ? 'active' : '' ?>">платежи преподавателям</a>
		</div>

	</div>

	<table class="table table-hover">
		<thead style="font-size: 10px" class="half-black">
			<tr>
				<td>
				</td>
				<td>
					наличные<br>
					платежи
				</td>
				<td>
					наличные<br>
					возвраты
				</td>
				<td>
					платежи<br>
					по картам
				</td>
				<td>
					возвраты<br>по картам
				</td>
				<td>
					платежи<br>
					по счетам
				</td>
				<td>
					возвраты<br>
					по счетам
				</td>
				<td>
					платежи по<br />
					взаимозачетам
				</td>
				<td>
					возвраты по<br />
					взаимозачетам
				</td>
				<td style="border-left: 1px solid #ddd">
					итого<br>
					платежи
				</td>
				<td>
					итого<br>
					возвраты
				</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach($stats as $date => $stat): ?>
			<tr>
				<td>
					<?php if ($_GET["group"] == "y") { ?>
						<?= strftime("%Y", strtotime($date)) ?>–<?= strftime("%Y", strtotime($date)) + 1 ?> уч. г.
					<?php } else { ?>
						<?= strftime("%d %b %Y", strtotime($date)) ?>
					<?php } ?>
				</td>
				<td>
					<?= $stat[Payment::PAID_CASH]['payment_confirmed'] ?>
					<?php if ($stat[Payment::PAID_CASH]['payment_unconfirmed'] > 0) :?>
						<span class="quater-black"><?= ($stat[Payment::PAID_CASH]['payment_confirmed'] > 0 ? ' + ' : '')?><?= $stat[Payment::PAID_CASH]['payment_unconfirmed'] ?></span>
					<?php endif ?>
				</td>
				<td>
					<?= $stat[Payment::PAID_CASH]['return_confirmed'] ?>
					<?php if ($stat[Payment::PAID_CASH]['return_unconfirmed'] > 0) :?>
						<span class="quater-black"><?= ($stat[Payment::PAID_CASH]['return_confirmed'] > 0 ? ' + ' : '')?><?= $stat[Payment::PAID_CASH]['return_unconfirmed'] ?></span>
					<?php endif ?>
				</td>
				<td>
					<?= $stat[Payment::PAID_CARD]['payment_confirmed'] ?>
					<?php if ($stat[Payment::PAID_CARD]['payment_unconfirmed'] > 0) :?>
						<span class="quater-black"><?= ($stat[Payment::PAID_CARD]['payment_confirmed'] > 0 ? ' + ' : '')?><?= $stat[Payment::PAID_CARD]['payment_unconfirmed'] ?></span>
					<?php endif ?>
				</td>
				<td>
					<?= $stat[Payment::PAID_CARD]['return_confirmed'] ?>
					<?php if ($stat[Payment::PAID_CARD]['return_unconfirmed'] > 0) :?>
						<span class="quater-black"><?= ($stat[Payment::PAID_CARD]['return_confirmed'] > 0 ? ' + ' : '')?><?= $stat[Payment::PAID_CARD]['return_unconfirmed'] ?></span>
					<?php endif ?>
				</td>
				<td>
					<?= $stat[Payment::PAID_BILL]['payment_confirmed'] ?>
					<?php if ($stat[Payment::PAID_BILL]['payment_unconfirmed'] > 0) :?>
						<span class="quater-black"><?= ($stat[Payment::PAID_BILL]['payment_confirmed'] > 0 ? ' + ' : '')?><?= $stat[Payment::PAID_BILL]['payment_unconfirmed'] ?></span>
					<?php endif ?>

				</td>
				<td>
					<?= $stat[Payment::PAID_BILL]['return_confirmed'] ?>
					<?php if ($stat[Payment::PAID_BILL]['return_unconfirmed'] > 0) :?>
						<span class="quater-black"><?= ($stat[Payment::PAID_BILL]['return_confirmed'] > 0 ? ' + ' : '')?><?= $stat[Payment::PAID_BILL]['return_unconfirmed'] ?></span>
					<?php endif ?>
				</td>
				<td>
					<?= $stat[Payment::MUTUAL_DEBTS]['payment_confirmed'] ?>
					<?php if ($stat[Payment::MUTUAL_DEBTS]['payment_unconfirmed'] > 0) :?>
						<span class="quater-black"><?= ($stat[Payment::MUTUAL_DEBTS]['payment_confirmed'] > 0 ? ' + ' : '')?><?= $stat[Payment::MUTUAL_DEBTS]['payment_unconfirmed'] ?></span>
					<?php endif ?>
				</td>
				<td>
					<?= $stat[Payment::MUTUAL_DEBTS]['return_confirmed'] ?>
					<?php if ($stat[Payment::MUTUAL_DEBTS]['return_unconfirmed'] > 0) :?>
						<span class="quater-black"><?= ($stat[Payment::MUTUAL_DEBTS]['return_confirmed'] > 0 ? ' + ' : '')?><?= $stat[Payment::MUTUAL_DEBTS]['return_unconfirmed'] ?></span>
					<?php endif ?>
				</td>
				<td style="border-left: 1px solid #ddd">
					<?= $stat['payment_total_confirmed'] ?>
					<?php if ($stat['payment_total_unconfirmed'] > 0) :?>
						<span class="quater-black"><?= ($stat['payment_total_confirmed'] > 0 ? ' + ' : '')?><?= $stat['payment_total_unconfirmed'] ?></span>
					<?php endif ?>
				</td>
				<td>
					<?= $stat['return_total_confirmed'] ?>
					<?php if ($stat['return_total_unconfirmed'] > 0) :?>
						<span class="quater-black"><?= ($stat['return_total_confirmed'] > 0 ? ' + ' : '')?><?= $stat['return_total_unconfirmed'] ?></span>
					<?php endif ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ((Payment::timeFromFirst($_GET["group"]) / StatsController::PER_PAGE) > 1) :?>
		<pagination
		  ng-model="currentPage"
		  ng-change="pagePaymentChanged('<?= $_GET['group'] ?>')"
		  total-items="<?= Payment::timeFromFirst($_GET['group']) ?>"
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
