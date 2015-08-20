<table class="table table-hover">
	<thead style="font-weight: bold">
		<tr>
			<td style="width: 85%">Филиал</td>
			<td>Рейтинг</td>
		</tr>
	</thead>
	
	<?php foreach($rating as $id_branch => $score): ?>
	<tr onclick="redirect('rating/<?= $id_branch ?>')" class="pointer">
		<td><?= Branches::metroSvg($id_branch) ?><?= Branches::$all[$id_branch] ?></td>
		<td>
			<?php if ($rdata[$id_branch]['actual'] || $rdata[$id_branch]['prognoz']): ?>
				<?= ($rdata[$id_branch]['actual'] ? $rdata[$id_branch]['actual'] : 0) ?>
				<?php if ($rdata[$id_branch]['prognoz']): ?>
					<span class="quater-black"> +
						<?= ($rdata[$id_branch]['prognoz'] ? $rdata[$id_branch]['prognoz'] : 0) ?>
					</span>
				<?php endif ?>
			<?php endif ?>
		</td>
	</tr>
	<?php endforeach; ?>
</table>