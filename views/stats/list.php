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
			<span class="link-like" ng-click="setPayment(1)" ng-class="{'active': payment_status == 1}">оплата по картам</span>
			<span class="link-like" ng-click="setPayment(2)" ng-class="{'active': payment_status == 2}">наличные</span>
			<span class="link-like" ng-click="setPayment(4)" ng-class="{'active': payment_status == 4}">счет</span>
			<span class="link-like" ng-click="setPayment(5)" ng-class="{'active': payment_status == 5}">карта онлайн</span>
			<span class="link-like" ng-click="setPayment(0)" ng-class="{'active': payment_status == 0 || !payment_status}">все платежи</span>
		</div>
		
	</div>
	
	<table class="table table-hover">
		<thead style="font-weight: bold">
			<tr>
				<td>
					дата
				</td>
				<td>
					заявок
				</td>
				<td>
					договоров
				</td>
				<td>
					сумма
				</td>
				<td>
					сумма платежей
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
					<?= $stat['count'] ?>
					<?php if (isset($stat['plus_contracts']) && $stat['plus_contracts'] != 0): ?>
						<span class="quater-black"> 
							<?= ($stat['plus_contracts'] > 0 ? " + " . $stat['plus_contracts'] : " – " . ($stat['plus_contracts'] * -1)) ?>
						</span>
				   	<?php endif ?>
				</td>
				<td>
				   <?php 
					   if ($stat['total']) {
						   echo $stat['total'];
					   } else {
						   if (isset($stat['plus_sum']) && $stat['plus_sum'] != 0) {
							   echo $stat['plus_sum'];
						   }
					   }
					?>
				   <?php if ($stat['total'] && isset($stat['plus_sum']) && $stat['plus_sum'] > 0): ?>
						<span class="quater-black"> + <?= $stat['plus_sum'] ?></span>
				   	<?php endif ?>
				   	
				   	<?php if (isset($stat['minus_sum']) && $stat['minus_sum'] > 0): ?>
						<span class="quater-black"> – <?= $stat['minus_sum'] ?></span>
					<?php endif ?>
				   	
				</td>
				<td>
					<?= $stat['total_payment'] ?>
					<?php 
						if ($stat['total_payment_plus'] != 0) {
							echo '<span class="quater-black"> ';
							if ($stat['total_payment']) {
								echo "+ ";
							}
							echo $stat['total_payment_plus'];
							echo "</span>";
						}
						
						if (isset($stat["payment_minus"])) {
							echo '<span class="quater-black"> – '. ($stat["payment_minus"] * -1) .'</span>'; 	
						}
					?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
	<?php if ($_GET["group"] == "d" || empty($_GET["group"])) :?>
	<pagination
	  ng-model="currentPage"
	  ng-change="pageChanged()"
	  total-items="160"
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