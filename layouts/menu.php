<div class="row">
  <div class="col-sm-1">
  </div>
  <div class="col-sm-2">
	  
	<div class="list-group">
    <a href="#" class="list-group-item active">Меню</a>
    <a href="requests" class="list-group-item">Заявки 
	    <?php
			// Количество новых заявок
			$new_request_count = Request::countNew();
			
			// Если есть новые заявки
			if ($new_request_count) {
				echo '<span class="badge pull-right">'. $new_request_count .'</span>';
			}
		?>
	</a>
    <a href="#" class="list-group-item">Ученики</a>
    <a href="#" class="list-group-item">Преподователи</a>
    <a href="#" class="list-group-item">Группы</a>
    <a href="#" class="list-group-item">Школы</a>
    <a href="#" class="list-group-item active">Настройки</a>
    <a href="#" class="list-group-item">Пользователи</a>
    <a href="logout" class="list-group-item">Выход</a>
  </div>
<!--
    <div class="sidebar-nav">
      <div class="navbar navbar-default" role="navigation">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <span class="visible-xs navbar-brand">ЕГЭ Центр</span>
        </div>
        <div class="navbar-collapse collapse sidebar-navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="#">Заявки <span class="badge pull-right">23</span></a></a></li>
            <li><a href="#">Преподователи</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Ученики <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="#"><span class="glyphicon glyphicon-plus"></span> Добавить</a></li>
                <li class="divider"></li>
                <li><a href="#">Горячие</a></li>
                <li><a href="#">С договором</a></li>
                <li><a href="#">Новые</a></li>
                <li><a href="#">Отказы</a></li>
              </ul>
            </li>
            <li><a href="#">Группы</a></li>
            <li><a href="#">Школы</a></li>
          </ul>
        </div>
      </div>
    </div>
-->
  </div>
  <div class="col-sm-8">
    
	<div class="panel panel-primary">
	<div class="panel-heading">
		<?= $this->tabTitle() ?>
	</div>
	<div class="panel-body">