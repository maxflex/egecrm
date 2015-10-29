<div style="margin-bottom: 15px">
	<b>Всего СМС:</b> <?= count($all_sms) ?>
</div>
<table class="table table-hover">
	<?php foreach ($all_sms as $sms) :?>
		<tr>
			<td width="150">
				<?= $sms->number ?>
				<div class="small half-black">
					<input type="checkbox" onclick="noProblem(<?= $sms->id ?>, this)" 
						<?= $sms->force_ok ? "checked" : "" ?>> проблема устранена
				</div>
			</td>
			<td><?= $sms->message ?></td>
			<td><?= $sms->id_user ? User::findById($sms->id_user)->login : 'system' ?></td>
			<td width="150"><?= date("d.m в H:i", strtotime($sms->date)) ?></td>
			<td>
				<b id="status-<?= $sms->id ?>" style="display: <?= $sms->force_ok ? "none" : "block" ?>">
				<?=($sms->status ? "<span class='text-success'>{$sms->status_text}</span>" : "<span class='text-danger'>{$sms->status_text}</span>")?>
				</b>
				<b id="status-<?= $sms->id ?>-ok" style="display: <?= $sms->force_ok ? "block" : "none" ?>">
					<span class='text-success'>OK</span>
				</b>
			</td>
			<td style="width: 15px">
				<span class="glyphicon glyphicon-envelope <?= $sms->not_notified ? 'quater-black' : 'text-success' ?>" style="margin: 0"></span>
			</td>
			<td>
				<?php if ($sms->not_agreed) :?>
					<b class="text-danger">НЕ СОГЛАСЕН</b>
				<?php endif ?>
			</td>
		</tr>
	<?php endforeach ?>
</table>

<script>
	function noProblem(id, el) {
		if ($(el).is(":checked")) {
			$("#status-" + id + "-ok").show();
			$("#status-" + id).hide();
			$.post("ajax/SmsCheckOk", {id: id});
		} else {
			$("#status-" + id + "-ok").hide();
			$("#status-" + id).show();
			$.post("ajax/SmsCheckDelete", {id: id});	
		}
	}
</script>