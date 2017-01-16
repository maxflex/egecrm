<div class="panel panel-primary <?= ($mode != 'request' ? 'ng-hide' : '') ?>" ng-show="mode == 'request'">
	<div class="panel-heading">
		<?php if ($Request->adding) :?>
		Добавление заявки
		<?php else :?>
		Редактирование заявки №<?= $Request->id ?>
		<?php endif ?>
		<div class="pull-right">
			<?php if (!$Request->adding) :?>
				<span class="link-reverse pointer" style="margin-left: 10px" onclick="lightBoxShow('glue')">перенести в другой профиль</span>
				<?php if ($Request->getDuplicates()): ?>
					<span class="link-reverse pointer" style="margin-left: 10px" onclick='deleteRequest(<?= $Request->id ?>)'>удалить заявку</span>
				<?php endif ?>
			<?php endif ?>
		</div>
	</div>
	<div class="panel-body">
		<!-- Скрытые поля -->
		<input type="hidden" name="id_request" value="<?= $Request->id ?>">
		<!-- если нажата сохранить, то всегда обнулять  adding -->
		<input type="hidden" name="Request[adding]" value="0">
		<input type="hidden" name="Request[id_student]" id="id_student_force" value="<?= $Request->id_student ?>">
		<input type="hidden" id="subjects_json" name="subjects_json">
		<input type="hidden" id="payments_json" name="payments_json">
		<input type="hidden" ng-value="markerData() | json"  name="marker_data">
		<input type="hidden" name="save_request" value="{{request_comments === undefined ? 0 : 1}}">
		<input type="hidden" name="save_student" value="{{student === undefined ? 0 : 1}}">
		<!-- Конец /скрытые поля -->
		<?= globalPartial('loading', ['model' => 'request_comments']) ?>
		<div class="ng-hide" ng-hide="request_comments === undefined">
			<!-- ВКЛАДКИ ЗАЯВОК -->
			<div class="row" style="margin-bottom: 20px" ng-hide="<?= ($Request->adding && !$_GET["id_student"]) ?>">
				<div class="col-sm-12">
					<span class="tab-link" ng-repeat="request_duplicate in request_duplicates" ng-class="{'active' : request_duplicate == <?= $Request->id ?>}">
						<a href="requests/edit/{{request_duplicate}}">Заявка №{{request_duplicate}}</a>
					</span>
					<span class="tab-link" ng-class="{'active' : <?= ($Request->adding && $Request->id_student) ? 'true' : 'false' ?>}">
						<a href="requests/add?id_student=<?= $Request->id_student ?>">добавить заявку</a>
					</span>
					<div class="top-links pull-right">
						<span class="link-like active">заявки</span>
						<span class="link-like" ng-click="setMode('student')">клиент</span>
					</div>
				</div>
			</div>
			<!-- /ВКЛАДКИ ЗАЯВОК -->
			<!-- ДАННЫЕ ПО ЗАЯВКЕ С САЙТА И УВЕДОМЛЕНИЯ -->
			<div class="row">
				<div class="col-sm-9">
					<div class="row">
						<div class="col-sm-4">
						</div>
					</div>
					<div class="row" style="margin-bottom: 30px">
						<div class="col-sm-12">
							<div class="row">
								<div class="col-sm-12">
									<textarea class="form-control" placeholder="комментарий" name="Request[comment]"><?= $Request->comment ?></textarea>
								</div>
							</div>
							<div class="row" style="margin-top: 10px">
								<div class="col-sm-12">
									<comments entity-id="<?= $Request->id ?>" entity-type="REQUEST" user="user"></comments>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						<?= Subjects::buildMultiSelector($Request->subjects, ["id" => "request-subjects", "name" => "Request[subjects][]"], 'three_letters') ?>
					</div>
					<div class="form-group">
						<?= Grades::buildSelector($Request->grade, "Request[grade]") ?>
					</div>

					<div class="form-group">
						<input placeholder="имя" class="form-control" name="Request[name]" value="<?= $Request->name ?>">
					</div>
					<div class="form-group">
						<phones entity="Request" entity-type="Request"></phones>
					</div>

					<div class="form-group">
						<?= Branches::buildMultiSelector($Request->branches, [
							"id" 	=> "request-branches",
							"name"	=> "Request[branches][]",
						], "филиалы") ?>
					</div>
					<div class="form-group">
						<?= RequestStatuses::buildSelector($Request->id_status, "Request[id_status]") ?>
					</div>
				</div>

				<?= partial("save_button", ["Request" => $Request]) ?>
			</div>
			<!-- /ДАННЫЕ ПО ЗАЯВКЕ С САЙТА И УВЕДОМЛЕНИЯ -->
		</div>
	</div>
</div>