<?php foreach($Students as $id=> $Student): ?>
<div class="row" style="margin-bottom: 5px">
	<div class="col-sm-4">
		<?= ($id + 1) ?>. 
		<a href="requests/edit/<?= $Student->getRequest()->id ?>">
			<?= empty(trim($Student->fio())) ? "Неизвестно" : $Student->fio() ?>
		</a>
	</div>
	<div class="col-sm-1" style="margin-right: 20px">
		<?= $Student->Contract->grade ? $Student->Contract->grade : "неизвестно" ?>
	</div>
	<div class="col-sm-1" style="margin-right: 20px">
		<?= $Student->Contract->date ? $Student->Contract->date : "неизвестно" ?>
	</div>
	<div class="col-sm-2">
		<?= $Student->Contract->subjects === false ? 0 : count($Student->Contract->subjects) ?> 
		<?= pluralize('предмет', 'предмета', 'предметов', $Student->Contract->subjects === false ? 0 : count($Student->Contract->subjects)) ?>
	</div>
	<div class="col-sm-3">
		<?= number_format($Student->Contract->sum, 0, ",", " ") ?> рублей
		<span class="pull-right"><?= $Student->Contract->cancelled ? "расторгнут" : "" ?></span>
	</div>
	
</div>
<?php endforeach; ?>