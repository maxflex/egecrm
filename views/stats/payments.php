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
		<a href="stats/payments/?group=d" style="margin-right: 15px">по дням</a>
		<?php } ?>
		
		<?php if ($_GET["group"] == "w") { ?>
		<span style="margin-right: 15px; font-weight: bold">по неделям</span>
		<?php } else { ?>
		<a href="stats/payments/?group=w" style="margin-right: 15px">по неделям</a>
		<?php } ?>
		
		<?php if ($_GET["group"] == "m") { ?>
		<span style="margin-right: 15px; font-weight: bold">по месяцам</span>
		<?php } else { ?>
		<a href="stats/payments/?group=m" style="margin-right: 15px">по месяцам</a>
		<?php } ?>
		
		<?php if ($_GET["group"] == "y") { ?>
		<span style="margin-right: 15px; font-weight: bold">по годам</span>
		<?php } else { ?>
		<a href="stats/payments/?group=y" style="margin-right: 15px">по годам</a>
		<?php } ?>
		
		<div class="pull-right">
			<a href="stats">итоговые данные</a>
			<span class="link-like active">детализация по платежам</span>
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
					платежи<br>
					онлайн
				</td>
				<td>
					возвраты<br>
					онлайн
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
					<?= strftime("%d %b %Y", strtotime($date)) ?>
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
					<?= $stat[Payment::PAID_BILL]['return_confirmed'] ?>
					<?php if ($stat[Payment::PAID_BILL]['return_unconfirmed'] > 0) :?>
						<span class="quater-black"><?= ($stat[Payment::PAID_BILL]['return_confirmed'] > 0 ? ' + ' : '')?><?= $stat[Payment::PAID_BILL]['return_unconfirmed'] ?></span>
					<?php endif ?>
				</td>
				<td>
					<?= $stat[Payment::PAID_CARD]['payment_confirmed'] ?>
					<?php if ($stat[Payment::PAID_CARD]['payment_unconfirmed'] > 0) :?>
						<span class="quater-black"><?= ($stat[Payment::PAID_CARD]['payment_confirmed'] > 0 ? ' + ' : '')?><?= $stat[Payment::PAID_CARD]['payment_unconfirmed'] ?></span>
					<?php endif ?>
				</td>
				<td>
					<?= $stat[Payment::CARD_ONLINE]['payment_confirmed'] ?>
					<?php if ($stat[Payment::CARD_ONLINE]['payment_unconfirmed'] > 0) :?>
						<span class="quater-black"><?= ($stat[Payment::CARD_ONLINE]['payment_confirmed'] > 0 ? ' + ' : '')?><?= $stat[Payment::CARD_ONLINE]['payment_unconfirmed'] ?></span>
					<?php endif ?>
				</td>
				<td>
					<?= $stat[Payment::CARD_ONLINE]['return_confirmed'] ?>
					<?php if ($stat[Payment::CARD_ONLINE]['return_unconfirmed'] > 0) :?>
						<span class="quater-black"><?= ($stat[Payment::CARD_ONLINE]['return_confirmed'] > 0 ? ' + ' : '')?><?= $stat[Payment::CARD_ONLINE]['return_unconfirmed'] ?></span>
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
	
	<?php if ($_GET["group"] == "d" || empty($_GET["group"])) :?>
		<pagination
		  ng-model="currentPage"
		  ng-change="pagePaymentChanged()"
		  total-items="<?= Payment::timeFromFirst() ?>"
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