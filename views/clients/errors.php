<div ng-app="Clients" ng-controller="ErrorsCtrl" ng-init="<?= $ang_init_data ?>">
	
	<div class="top-links">
		
		<?php if ($_GET["mode"] == "phone") { ?>
			<span style="margin-right: 15px; font-weight: bold">дубли телефонов</span>
		<?php } else { ?>
			<a href="clients/errors/?mode=phone" style="margin-right: 15px">дубли телефонов</a>
		<?php } ?>
		
		
	</div>
	
	<?php 
	if (!empty($_GET['mode'])) {
		switch ($_GET["mode"]) {
			case "correspond":
			case "layer":
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
<!--
				<td>
					{{Student.subject.name}}
				</td>
-->
			</tr>
		</tbody>
	</table>
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