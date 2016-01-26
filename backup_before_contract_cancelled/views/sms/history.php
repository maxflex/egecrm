<div ng-app="Sms" ng-controller="Main" ng-init="<?= $ang_init_data ?>">
	<table class="table table-hover">
		<thead style="font-weight: bold">
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
		</thead>
		<tbody>
			<?php foreach($History as $SMS): ?>
			<tr>
				<td>
					<?= formatNumber($SMS->number) ?>
				</td>
				<td>
					
<!--
					
					<div id="sms-short-<?= $SMS->id ?>" style="display: <?= empty($SMS->message_short) ? "none" : "block" ?>">
						<?= $SMS->message_short ?> <span class="link-like small" onclick="showFullSms(<?= $SMS->id ?>)">развернуть</span>
					</div>
-->
					
<!-- 					style="display: <?= empty($SMS->message_short) ? "block" : "none" ?>" -->
					<div id="sms-full-<?= $SMS->id ?>" style="display: block">
						<?= $SMS->message ?>
					</div>
				</td>
				<td>
				   <?= $SMS->user_login ?>
				</td>
				<td>
				   <?= dateFormat($SMS->date) ?>
				</td>
				<td>
					<?= $SMS->getStatus() ?>
				</td>
			</tr>
			<?php endforeach; ?>
			
		</tbody>
	</table>
	
	<pagination
	  ng-model="currentPage"
	  ng-change="pageChanged()"
	  total-items="<?= SMS::count() ?>"
	  max-size="10"
	  items-per-page="<?= SMS::PER_PAGE ?>"
	  first-text="«"
	  last-text="»"
	  previous-text="«"
	  next-text="»"
	>
	</pagination>

	
</div>