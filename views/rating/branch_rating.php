<center style="font-weight: bold">
	<?= Branches::getName($id_branch) ?>
</center>
<hr style="visibility: hidden">
<table class="table table-hover">
	<thead>
		<tr>
			<td></td>
			<?php for ($i = 9; $i <= 11; $i++): ?>
				<td><?= $i ?> класс</td>
			<?php endfor ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach (Subjects::$all as $id_subject => $name): ?>
			<tr>
				<td><?= $name ?></td>
				<?php for ($i = 9; $i <= 11; $i++): ?>
					<td>
						<?php if ($result[$i][$id_subject] || $result_prognoz[$i][$id_subject]): ?>
							<?= ($result[$i][$id_subject] ? $result[$i][$id_subject] : 0) ?>
							<?php if ($result_prognoz[$i][$id_subject]): ?>
								<span class="quater-black"> +
									<?= ($result_prognoz[$i][$id_subject] ? $result_prognoz[$i][$id_subject] : 0) ?>
								</span>
							<?php endif ?>
						<?php endif ?>
					</td>
				<?php endfor ?>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>