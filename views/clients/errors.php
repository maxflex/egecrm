<div ng-app="Clients" ng-controller="ErrorsCtrl" ng-init="<?= $ang_init_data ?>">
	
	<div class="top-links">
		<?php if ($_GET["mode"] == "nogroup") { ?>
		<span style="margin-right: 15px; font-weight: bold">без групп</span>
		<?php } else { ?>
		<a href="clients/errors/?mode=nogroup" style="margin-right: 15px">без групп</a>
		<?php } ?>
		
		<?php if ($_GET["mode"] == "layer") { ?>
		<span style="margin-right: 15px; font-weight: bold">наслоения в расписании учеников</span>
		<?php } else { ?>
		<a href="clients/errors/?mode=layer" style="margin-right: 15px">наслоения в расписании учеников</a>
		<?php } ?>
		
		<?php if ($_GET["mode"] == "duplicate") { ?>
		<span style="margin-right: 15px; font-weight: bold">дубли групп</span>
		<?php } else { ?>
		<a href="clients/errors/?mode=duplicate" style="margin-right: 15px">дубли групп</a>
		<?php } ?>
		
		<?php if ($_GET["mode"] == "phone") { ?>
		<span style="margin-right: 15px; font-weight: bold">дубли телефонов</span>
		<?php } else { ?>
		<a href="clients/errors/?mode=phone" style="margin-right: 15px">дубли телефонов</a>
		<?php } ?>
		
		<?php if ($_GET["mode"] == "grouptime") { ?>
		<span style="margin-right: 15px; font-weight: bold">несоответсвие в расписании групп</span>
		<?php } else { ?>
		<a href="clients/errors/?mode=grouptime" style="margin-right: 15px">несоответсвие в расписании групп</a>
		<?php } ?>
		
		<?php if ($_GET["mode"] == "groupgrade") { ?>
		<span style="margin-right: 15px; font-weight: bold">класс в группах</span>
		<?php } else { ?>
		<a href="clients/errors/?mode=groupgrade" style="margin-right: 15px">класс в группах</a>
		<?php } ?>
		
		<br>
		
		<?php if ($_GET["mode"] == "cancelled") { ?>
		<span style="margin-right: 15px; font-weight: bold">расторгнутые в группах</span>
		<?php } else { ?>
		<a href="clients/errors/?mode=cancelled" style="margin-right: 15px">расторгнутые в группах</a>
		<?php } ?>
		
	</div>
	
	<?php 
	if (!empty($_GET['mode'])) {
		switch ($_GET["mode"]) {
			case "nogroup":
			case "layer":
			case "cancelled":
			case "duplicate":
			case "phone": {	
	?>
	<table class="table table-divlike">
		<tbody>
			<tr ng-repeat="Student in Response">
				<td>
					<a href="student/{{Student.id}}">
						<span ng-show="Student.last_name || Student.first_name || Student.middle_name">{{Student.last_name}} {{Student.first_name}} {{Student.middle_name}}</span>
						<span ng-show="!Student.last_name && !Student.first_name && !Student.middle_name">Неизвестно</span>
					</a>
				</td>
				<td>
					{{Student.subject.name}}
				</td>
			</tr>
		</tbody>
	</table>
	<?php
			break;
		}
		case "grouptime": {
	?>
		<div ng-repeat="id_group in Response">
			<a href="groups/edit/{{id_group}}">Группа №{{id_group}}</a>
		</div>
	<?php
			break;	
		}
		case "groupgrade": {
	?>
		<div ng-repeat="Data in Response track by $index">
			<a href="groups/edit/{{Data.Group.id}}">Группа №{{Data.Group.id}}</a> {{Data.Group.grade ? Data.Group.grade + ' класс' : 'класс не установлен'}}, 
			<a href="student/{{Data.Student.id}}">
				<span ng-show="Data.Student.last_name || Data.Student.first_name || Data.Student.middle_name">{{Data.Student.last_name}} {{Data.Student.first_name}} {{Data.Student.middle_name}}</span>
				<span ng-show="!Data.Student.last_name && !Data.Student.first_name && !Data.Student.middle_name">Неизвестно</span>
			</a> {{Data.Student.grade ? Data.Student.grade + ' класс' : 'класс не установлен'}}
		</div>
	<?php
			break;	
		}
	}
	?>
	<div class="center small half-black" style="padding: 100px 0" ng-show="!Response">
		<span ng-show="Response === undefined">проверка...</span>
		<span ng-show="Response === null" class="text-success">ошибок нет</span>
	</div>
	<?php
	} 
	?>
	
</div>