<div ng-app="Sms" ng-controller="Main" ng-init="<?= $ang_init_data ?>">
	<form method="get" action="sms" style="margin-bottom: 20px">
		<div class="row">
			<div class="col-sm-2">
				<input type="text" placeholder="телефон" untrack-dublicate="true" class="form-control phone-masked" name="phone" value="<?= $_GET['phone'] ?>"">
			</div>
			<div class="col-sm-6">
				<input class="form-control" placeholder="поиск" name="search" value="<?= $_GET['search'] ?>">
			</div>
			<button type="submit" class="btn btn-primary">Найти SMS</button>
		</div>
	</form>
	<table class="table table-hover">
		<!-- <thead style="font-weight: bold">
			<tr>
				<td style="width: 16%">
					номер
				</td>
				<td style="width: 50%">
					сообщение
				</td>
				<td>
					пользователь
				</td>
				<td style="width: 137px">
					дата
				</td>
				<td>
					статус
				</td>
			</tr>
		</thead> -->
		<tbody>
		<?php foreach($History as $SMS): ?>
			<tr>
				<td class="col-sm-2">
					<?= formatNumber($SMS->number) ?>
				</td>
				<td class="col-sm-6">
					<!--<div id="sms-short-<?= $SMS->id ?>" style="display: <?= empty($SMS->message_short) ? "none" : "block" ?>"><?= $SMS->message_short ?> <span class="link-like small" onclick="showFullSms(<?= $SMS->id ?>)">развернуть</span></div>-->
					<!--style="display: <?= empty($SMS->message_short) ? "block" : "none" ?>" -->
					<div id="sms-full-<?= $SMS->id ?>" style="display: block">
						<?= $SMS->message ?>
					</div>
				</td>
				<td class="col-sm-1">
					<?= $SMS->user_login ?>
				</td>
				<td class="col-sm-2">
					<?= dateFormat($SMS->date) ?>
				</td>
				<td class="col-sm-1">
					<?= $SMS->getStatus() ?>
				</td>
			</tr>
			<?php endforeach; ?>

		</tbody>
	</table>

	<pagination
		ng-model="currentPage"
		ng-change="pageChanged()"
		total-items="<?= SMS::pagesCount(['search' => $_GET['search'], 'phone' => $_GET['phone']]) ?>"
		max-size="10"
		items-per-page="<?= SMS::PER_PAGE ?>"
		first-text="«"
		last-text="»"
		previous-text="«"
		next-text="»"
	>
	</pagination>


</div>