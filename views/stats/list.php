<div style="margin-bottom: 20px">

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

</div>

<table class="table table-hover">
	<thead style="font-weight: bold">
		<tr>
			<td>
				дата
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
				<?= $stat['count'] ?>
			</td>
			<td>
			   <?= $stat['total'] ?>
			</td>
			<td>
				<?= $stat['total_payment'] ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
				
<!--

<div class="row" style="font-weight: bold; margin-bottom: 5px">
	<div class="col-sm-2">
		дата
	</div>
	<div class="col-sm-2">
		договоров
	</div>
	<div class="col-sm-2">
		сумма
	</div>
	<div class="col-sm-2">
		сумма платежей
	</div>
</div>
<?php foreach($stats as $date => $stat): ?>
<div class="row" style="margin-bottom: 5px">
	<div class="col-sm-2">
		<?= strftime("%d %b %Y", strtotime($date)) ?>
	</div>
	<div class="col-sm-2">
		<?= $stat['count'] ?>
	</div>
	<div class="col-sm-2">
		<?= $stat['total'] ?>
	</div>
	<div class="col-sm-2">
		<?= $stat['total_payment'] ?>
	</div>
</div>
<?php endforeach; ?>
-->