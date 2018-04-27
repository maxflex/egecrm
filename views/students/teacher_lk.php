<div ng-app="StudentProfile" ng-controller="TeacherLk" ng-init="<?= $ang_init_data ?>">
	<div class="top-links wide">
		<span class="link-like" ng-click="setMenu(0, true)" ng-class="{'active': current_menu == 0}">
			РАСПИСАНИЕ И ОТЧЕТЫ
		</span>
		<!-- <span class="link-like" ng-click="setMenu(3, true)" ng-class="{'active': current_menu == 3}">
			ПЛАТЕЖИ
		</span> -->
	</div>
	<?= partial('lessons') ?>
</div>
