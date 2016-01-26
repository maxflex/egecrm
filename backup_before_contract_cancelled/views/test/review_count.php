<div class="row small" style="margin-bottom: 10px">
	<div class="col-sm-12">
		<span style="display: inline-block; margin-right: 10px">
			Всего: <b><?= $total_count ?></b>
		</span>
		<span style="display: inline-block; margin-right: 10px" class="quater-black">
			|
		</span>
		<span style="display: inline-block; margin-right: 10px">
			<svg class="review-status not-collected" style="top: 4px; width: 15px">
						<circle r="3" cx="7" cy="7"></circle>
					</svg><b><?= $gray_count ?></b>
		</span>
		<span style="display: inline-block; margin-right: 10px">
			<svg class="review-status collected" style="top: 4px; width: 15px">
						<circle r="3" cx="7" cy="7"></circle>
					</svg><b><?= $green_count ?></b>
		</span>
		<span style="display: inline-block; margin-right: 10px">
			<svg class="review-status orange" style="top: 4px; width: 15px">
						<circle r="3" cx="7" cy="7"></circle>
					</svg><b><?= $orange_count ?></b>
		</span>
		<span style="display: inline-block; margin-right: 10px">
			<svg class="review-status red" style="top: 4px; width: 15px">
						<circle r="3" cx="7" cy="7"></circle>
					</svg><b><?= $red_count ?></b>
		</span>
	</div>
</div>

<?php foreach ($data as $d) :?>
<div class="row">
	<div class="col-sm-4">
		<svg class="review-status <?= $d['class'] ?>" style="top: 4px; width: 15px">
			<circle r="3" cx="7" cy="7"></circle>
		</svg>
		<a href="teachers/edit/<?= $d['Teacher']->id ?>" target="_blank"><?= $d['Teacher']->last_name . ' ' . $d['Teacher']->first_name . ' ' . $d['Teacher']->middle_name ?></a>
	</div>
	<div class="col-sm-4">
		<a href="student/<?= $d['Student']->id ?>" target="_blank"><?= $d['Student']->fio() ?></a>
	</div>
	<div class="col-sm-4">
		<a href="groups/edit/<?= $d['id_group'] ?>" target="_blank">Группа №<?= $d['id_group'] ?></a>
	</div>
</div>
<?php endforeach ?>