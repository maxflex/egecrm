<?php if (User::fromSession()->AsUser) :?>
<div class="view-as">
	<span style="position: absolute; left: 10px">Режим просмотра преподавателя</span>
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
    <a href="groups" class="list-group-item">Мои группы
	    <?php
			$groups_count = Teacher::countGroups();
			
			if ($groups_count) {
				echo '<span class="badge pull-right">'. $groups_count .'</span>';
			}
		?>
    </a>
    <a href="payments/teacher" class="list-group-item">Оплата</a>
    <a href="print" class="list-group-item">Печать</a>
    <a href="faq" class="list-group-item">Необходимая информация</a>
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