<?php if (User::inViewMode()) :?>
<div class="view-as">
	<div>
		Режим просмотра ученика
	</div>
	<div>
		<?= User::fromSession()->last_name." ".User::fromSession()->first_name." ".User::fromSession()->middle_name ?>
	</div>
	<div>
		<a href="as/cancel" class="link-white">Выйти из режима просмотра</a>
	</div>
</div>
<?php endif ?>
<div class="row">
  <div class="col-sm-2" style="margin-left: 10px">
	<div>
	<div class="list-group">
    <a href="#" class="list-group-item active">Меню</a>
    <a href="students/groups" class="list-group-item">Мои группы
	    <?php
			$groups_count = Student::countGroupsStatic(User::fromSession()->id_entity);

			if ($groups_count) {
				echo '<span class="badge pull-right">'. $groups_count .'</span>';
			}
		?>
    </a>
	<a href="students/reports" class="list-group-item">Отчеты
		<?php
			$report_count = Student::getReportCount(User::fromSession()->id_entity);

			if ($report_count) {
				echo '<span class="badge pull-right">' . $report_count . '</span>';
			}
		?>
	</a>
    <a href="students/reviews" class="list-group-item">Оставить отзыв
	    <?php
			$reviews_count = Student::reviewsNeeded();

			if ($reviews_count) {
				echo '<span class="badge badge-danger pull-right">' . $reviews_count . '</span>';
			}
		?>
    </a>
    <a href="students/tests" class="list-group-item">Тесты
	    <?php
			$test_count = TestStudent::countNeeded();

			if ($test_count) {
				echo '<span class="badge pull-right">' . $test_count . '</span>';
			}
		?>
    </a>
    <a href="students/photo" class="list-group-item">Фото <?= User::fromSession()->photo_extension ? '' : '<span class="badge badge-danger pull-right add-photo-badge">добавить</span>'; ?></a>
<!--    <a href="students/faq" class="list-group-item">Необходимая информация</a>-->
    <a href="#" class="list-group-item active">Настройки</a>
    <a href="logout" class="list-group-item">Выход</a>
  </div>
	</div>
  </div>
  <div class="col-sm-9" style="padding: 0; width: 80.6%;">

  	<?php if (!$this->_custom_panel) { ?>
		<div class="panel panel-primary">
		<div class="panel-heading">
			<?= $this->tabTitle() ?>
		</div>
		<div class="panel-body">
	<?php } ?>
