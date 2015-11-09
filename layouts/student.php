<?php if (User::fromSession()->AsUser) :?>
<div class="view-as">
	<span style="position: absolute; left: 10px">Режим просмотра ученика</span>
	<span class='center'>
		<?= User::fromSession()->AsUser->last_name." ".User::fromSession()->AsUser->first_name." ".User::fromSession()->AsUser->middle_name ?>
	</span>
	<a href="as/cancel" class="link-white" style="position: absolute; right: 10px">Выйти из режима просмотра</a>
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
    <a href="students/promo" class="list-group-item">Получи iPhone 6</a>
    <a href="students/journal" class="list-group-item">Журнал посещаемости</a>
    <a href="students/reviews" class="list-group-item">Оставить отзыв</a>
    <a href="students/faq" class="list-group-item">Необходимая информация</a>
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