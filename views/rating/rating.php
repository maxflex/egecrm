<table class="table table-hover">
	<thead style="font-weight: bold">
		<tr>
			<td>Филиал</td>
			<td>Рейтинг</td>
		</tr>
	</thead>
	
	<?php foreach($rating as $id_branch => $score): ?>
	<tr onclick="redirect('rating/<?= $id_branch ?>')" class="pointer">
		<td><?= Branches::metroSvg($id_branch) ?><?= Branches::$all[$id_branch] ?></td>
		<td><?= $score ?></td>
	</tr>
	<?php endforeach; ?>
</table>