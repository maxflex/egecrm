<div ng-app="Group" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="row" style="position: relative">
		<div class="col-sm-12">
			
			<?= globalPartial("groups_list", ["filter" => false, "loading" => true]) ?>
			
			<div ng-show="Groups.length == 0" class="center half-black small" style="margin-bottom: 30px">список групп пуст</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div class="title-section-1">Работа ЕГЭ-Центра</div>
			<div class="question">
				<span>
				<b>Вопрос: </b> мой вопрос
				</span>
				<div>
					<b>Ответ:</b> мой ответ
				</div>
			</div>
			<div class="question">
				<span>
				<b>Вопрос: </b> мой вопрос
				</span>
				<div>
					<b>Ответ:</b> мой ответ
				</div>
			</div>
		</div>
	</div>
</div>
