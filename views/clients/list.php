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
			<?= $Student->Contract->id ?>
		</td>
		<td>
			<?= $Student->Contract->grade ? $Student->Contract->grade. " класс" : "неизвестно" ?>
		</td>
		<td>
			<?= $Student->Contract->date ? $Student->Contract->date : "неизвестно" ?>
		</td>
		<td>
			<?= $Student->Contract->subjects === false ? 0 : count($Student->Contract->subjects) ?> 
			<?= pluralize('предмет', 'предмета', 'предметов', $Student->Contract->subjects === false ? 0 : count($Student->Contract->subjects)) ?>
		</td>
		<td>
			<?= number_format($Student->Contract->sum, 0, ",", " ") ?> рублей
			<span class="pull-right"><?= $Student->Contract->cancelled ? "расторгнут" : "" ?></span>
		</td>
		<td>
			<span class="pull-right"><?= $Student->isNotFull() ? "не полный" : "полный" ?></span>
		</td>
	</tr>
	<?php endforeach; ?>
</table>