<table class="table table-hover">
	<thead style="font-weight: bold">
		<tr>
			<td>
				номер
			</td>
			<td>
				сообщение
			</td>
			<td>
				пользователь
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
				<?= $SMS->message ?>
			</td>
			<td>
			   <?= $SMS->user_login ?>
			</td>
			<td>
				<?= $SMS->getStatus() ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>