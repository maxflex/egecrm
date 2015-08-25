<table class="table table-divlike">
	<?php foreach($Students as $id=> $Student): ?>
	<tr>
		<td style="width: 30%">
			<?= ($id + 1) ?>.
			<a href="requests/edit/<?= $Student->getRequest()->id ?>">
				<?= empty(trim($Student->fio())) ? "Неизвестно" : $Student->fio() ?>
			</a>
		</td>
		<td>
			<?php foreach ($Student->Contracts as $Contract): ?>
				<div>
					<?= $Contract->id ?>
				</div>
			<?php endforeach ?>
		</td>
		<td>
			<?php foreach ($Student->Contracts as $Contract): ?>
				<div>
					<?= $Contract->grade ? $Contract->grade. " класс" : "неизвестно" ?>
				</div>
			<?php endforeach ?>
		</td>
		<td>
			<?php foreach ($Student->Contracts as $Contract): ?>
				<div>
					<?= $Contract->date ? $Contract->date : "неизвестно" ?>
				</div>
			<?php endforeach ?>
		</td>
		<td>
			<?php foreach ($Student->Contracts as $Contract): ?>
				<div>
					<?= $Contract->subjects === false ? 0 : count($Contract->subjects) ?> 
					<?= pluralize('предмет', 'предмета', 'предметов', $Contract->subjects === false ? 0 : count($Contract->subjects)) ?>
				</div>
			<?php endforeach ?>
		</td>
		<td>
			<?php foreach ($Student->Contracts as $Contract): ?>
				<div>
					<?= number_format($Contract->sum, 0, ",", " ") ?> рублей
					<span class="pull-right"><?= $Contract->cancelled ? "расторгнут" : "" ?></span>
				</div>
			<?php endforeach ?>
		</td>
		<td>
			<span class="pull-right">
			<?php 
				foreach ($Student->Contracts as $Contract) {
					$scores = [];
					foreach ($Contract->subjects as $subject) {
						if ($subject['score'] != "") {
							$scores[] = $subject['score'];
						}
					}
					echo "<div><b>" . implode($scores, " + ") . "</b></div>";
				}
			?>
			</span>
		</td>
	</tr>
	<?php endforeach; ?>
</table>

<div class="pull-right">
	<b class="text-success">+<?= $without_contract ?></b> <?= pluralize('ученик', 'ученика', 'учеников', $without_contract) ?> без договора
</div>