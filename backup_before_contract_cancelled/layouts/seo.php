<div class="row">
  <div class="col-sm-2" style="margin-left: 10px">
	  <div>
	<div class="list-group">
    <a class="list-group-item active">Меню</a>
	    <a href="tasks/seo" class="list-group-item">Задачи
		<?php
			// Количество новых заявок
			$new_tasks_count = Task::countNew(1);
			
			// Если есть новые заявки
			if ($new_tasks_count) {
				echo '<span class="badge pull-right">'. $new_tasks_count .'</span>';
			}
		?>
    <a href="logout" class="list-group-item">Выход</a>
  </div>


	</div>
  </div>
  <div class="col-sm-9 content-col" style="padding: 0; width: 80.6%;">
    
  	<?php if (!$this->_custom_panel) { ?>
		<div class="panel panel-primary">
		<div class="panel-heading">
			<?= $this->tabTitle() ?>
		</div>
		<div class="panel-body">
	<?php } ?>