<!-- ЛАЙТБОКС ОТПРАВКА СООБЩЕИЯ -->
<div class="lightbox-new lightbox-sms">
	<h4 style="text-align: center" id="sms-number">
		<span class="text-danger">Номер не установлен!</span>
	</h4>
	<div class="row">
		<div class="col-sm-12" id="sms-history">
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12" style="text-align: center">
			<div class="form-group">
				<textarea rows="8" class="form-control" style="width: 100%" placeholder="Текст сообщения" id="sms-message"></textarea>
			</div>
			<button class="btn btn-primary" id="sms-send" onclick="sendSms()">Отправить</button>
		</div>
	</div>
</div>
<!-- /ЛАЙТБОКС ОТПРАВКА СООБЩЕНИЯ -->


<div class="row">
  <div class="col-sm-1">
  </div>
  
  <div class="col-sm-2">
	  		<form id="global-search" action="search" method="post" style="margin-bottom: 10px">
		<div class="input-group">
		  <input id="global-search-text" type="text" class="form-control" placeholder="Поиск..." name="text" value="<?= $_POST["text"] ?>">
		  <span class="input-group-btn">
		    <button class="btn btn-default" type="submit">
		    <span class="glyphicon glyphicon-search no-margin-right"></span>
		    </button>
		  </span>
		</div><!-- /input-group -->
		</form>
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
	<a href="notifications" class="list-group-item">Напоминания</a>
	<a href="stats" class="list-group-item">Итоги</a>
    <a href="clients" class="list-group-item">Клиенты</a>
    <a href="sms" class="list-group-item">SMS</a>
    <a href="#" class="list-group-item">Группы</a>
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