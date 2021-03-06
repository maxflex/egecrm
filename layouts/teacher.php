<?php if (User::inViewMode()) :?>
<div class="view-as">
	<div>
		Режим просмотра преподавателя
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
    <a href="teachers/groups" class="list-group-item">Мои группы
	    <?php
			$groups_count = Teacher::countGroups();

			if ($groups_count) {
				echo '<span class="badge pull-right">'. $groups_count .'</span>';
			}
		?>
    </a>
    <a href="teachers/reports" class="list-group-item">Отчеты
	    <?php
		    $red_report_count = Teacher::redReportCountStatic(User::id());

			if ($red_report_count) {
				echo '<span class="badge badge-danger pull-right">'. $red_report_count .'</span>';
			}
	   ?>
    </a>
    <a href="teachers/payments/teacher" class="list-group-item">Оплата</a>
    <a href="teachers/faq" class="list-group-item">Необходимая информация</a>
	<?php
		$groups_head_count = Teacher::countGroups(false, true);
		$teacher_head_count = Teacher::getHead(User::id(), 'count');
		$student_head_count = Teacher::getHeadStudents(User::id(), 'count');

		if ($groups_head_count || $teacher_head_count || $student_head_count) {
			echo '<a href="#" class="list-group-item active">КР</a>';
		}

		if ($groups_head_count) {
			echo '<a href="teachers/groups/extended" class="list-group-item">Группы<span class="badge pull-right">'. $groups_head_count .'</span></a>';
		}
		if ($student_head_count) {
			echo '<a href="teachers/students" class="list-group-item">Ученики<span class="badge pull-right">'. $student_head_count .'</span></a>';
		}
		if ($teacher_head_count) {
			echo '<a href="teachers/list" class="list-group-item">Преподаватели<span class="badge pull-right">'. $teacher_head_count .'</span></a>';
		}
	?>
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
