<!-- ЛАЙТБОКС РЕДАКТИРОВАНИЕ ПЕЧАТИ ДОГОВОРА ВРУЧНУЮ -->
	<div class="lightbox-new lightbox-manualedit">
		<div class="row">
			<textarea id="contract-manual-edit"></textarea>
			<div class="display-none" id="contract-manual-div"></div>
		<center style="margin-top: 10px">
			<button class="btn btn-primary ajax-payment-button" ng-click="runPrintManual()">Печать</button>
		</center>
		</div>
	</div>
	<!-- /ЛАЙТБОКС РЕДАКТИРОВАНИЕ ПЕЧАТИ ДОГОВОРА ВРУЧНУЮ -->

	<?= partial('contract_edit') ?>
	<?= partial('contract_edit_test') ?>

	<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
	<div class="lightbox-new lightbox-addpayment">
		<h4 style="display: inline-block">{{new_payment.id ? "Редактировать" : "Добавить"}} платеж</h4>
		<span class="small" ng-show="new_payment.id_status == <?= Payment::PAID_CASH ?> && new_payment.id_type == <?= PaymentTypes::PAYMENT ?>">
			<span ng-show="new_payment.id">
                номер ПКО: {{ new_payment.document_number }}
			</span>

			<span ng-show="!new_payment.id">
				будет присвоен номер ПКО
			</span>
		</span>

		<div class="form-group payment-line">
			<div class="form-group inline-block">
				<?= Payment::buildSelector(false, false, ["ng-model" => "new_payment.id_status", "style" => "width: 180px"], [Payment::MUTUAL_DEBTS]) ?>
		    </div>
			<div class="form-group inline-block">
				<?= PaymentTypes::buildSelector(false, false, ["ng-model" => "new_payment.id_type"]) ?> на сумму
		    </div>
			<div class="form-group inline-block">
				<input type="text" class="form-control digits-only" id="payment-sum" ng-model="new_payment.sum"  ng-keydown="watchEnter($event)"> от
			</div>
			<div class="form-group inline-block">
				<input class="form-control bs-date" id="payment-date" ng-model="new_payment.date">
			</div> за
            <div class="form-group inline-block">
                <select id="payment-year" class="form-control" ng-model="new_payment.year" style='width: 130px'>
                    <option value="">выберите год</option>
                    <option disabled>──────────────</option>
                    <option ng-repeat="year in <?= Years::json() ?>"
                        value="{{year}}">{{ yearLabel(year) }}</option>
                </select>
    		</div>
            <div class="form-group inline-block">
                <select ng-init='payment_categories = <?= PaymentTypes::categories() ?>' id="payment-category" class="form-control" ng-model="new_payment.category" style='width: 130px'>
                    <option value="0">категория</option>
                    <option disabled>──────────────</option>
                    <option ng-repeat="(id, label) in payment_categories"
                        value="{{id}}">{{ label }}</option>
                </select>
    		</div>
		</div>
		<div class="form-group payment-inline" ng-show="new_payment.id_status == <?= Payment::PAID_CARD ?>">
			<h4>Номер карты</h4>
			<div class="form-group inline-block">
				<input class="form-control card-first-number" placeholder="_XXX" id="payment-card-first-number" ng-model="new_payment.card_first_number" style="width: 70px; display: inline-block; margin-left: 5px"> -
				<input class="form-control" disabled placeholder="XXXX" style="width: 70px; display: inline-block"> -
				<input class="form-control" disabled placeholder="XXXX" style="width: 70px; display: inline-block"> -
				<input class="form-control digits-only" maxlength="4" id="payment-card-number" ng-model="new_payment.card_number"
					style="width: 70px; display: inline-block">
			</div>
		</div>
		<center>
			<button ng-show="new_payment.id" class="btn btn-primary btn-danger ajax-payment-delete" ng-click="deletePayment()">Удалить</button>
			<button class="btn btn-primary ajax-payment-button" ng-click="addPayment()">{{new_payment.id ? "Редактировать" : "Добавить"}}</button>
		</center>
	</div>
	<!-- /ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->

	<!-- ЛАЙТБОКС КАРТА -->
	<div class="lightbox-element lightbox-map">
		<map zoom="10" disable-default-u-i="true" scale-control="true" zoom-control="true" zoom-control-options="{style:'SMALL'}">
			<transit-layer></transit-layer>
			<custom-control position="TOP_RIGHT" index="1">
			<div class="input-group gmap-search-control">
	          <input type="text" id="map-search" class="form-control" ng-keyup="gmapsSearch($event)" placeholder="Поиск...">
	          <span class="input-group-btn">
			    <button class="btn btn-default" ng-click="gmapsSearch($event)">
			    <span class="glyphicon glyphicon-search no-margin-right"></span>
			    </button>
			  </span>
			</div>
	        </custom-control>
		</map>
		<button class="btn btn-default map-save-button" ng-click="saveMarkersToServer()">Сохранить</button>
	</div>
	<!-- КОНЕЦ /КАРТА И ЛАЙТБОКС -->

	<!-- СКЛЕЙКА КЛИЕНТОВ -->
	<div class="lightbox-new lightbox-glue">
		<div style="height: 75px">
			<h4>Перенести в другой профиль</h4>
		    <input id="id-student-glue" type="text" class="form-control" placeholder="ID ученика" ng-model="id_student_glue" ng-change="findStudent()">
		</div>
		<center>
			<span ng-show="request_duplicates.length > 1">
				<button class="btn btn-primary" type="button" ng-disabled="!GlueStudent" ng-click="glue(0)" id="save-glue-button">перенести</button>
			</span>
			<span ng-show="request_duplicates.length <= 1">
				<button class="btn btn-primary" type="button" ng-disabled="!GlueStudent" ng-click="glue(1)">перенести с удалением ученика</button>
				<button class="btn btn-primary" type="button" ng-disabled="!GlueStudent" ng-click="glue(0)">скопировать заявку в указанного ученика</button>
			</span>
		</center>
	</div>
	<!-- /СКЛЕЙКА КЛИЕНТОВ -->

	<!-- Редактирование занятия -->
	<div class="lightbox-new lightbox-edit-lesson">
		<h4>Редактирование занятия</h4>
			<div class="form-group inline-block">
				<select class="form-control" ng-model="modal_lesson.presence" style="width: 150px; margin-right: 20px">
					<option ng-repeat="(id, status) in lesson_statuses" value="{{ id }}">
						{{status}}
					</option>
				</select>
			</div>
			<div class="form-group inline-block">
				<input ng-model="modal_lesson.late" placeholder="опоздание" class="form-control digits-only"
					style="width: 150px">
			</div>
			<div class="form-group inline-block" style="width: 100%">
				<input ng-model="modal_lesson.price" placeholder="цена, руб." class="form-control">
			</div>
			<div class="form-group">
				<textarea ng-model="modal_lesson.comment" placeholder="комментарий" class="form-control" rows="3" maxlength="1000"></textarea>
			</div>
		<center>
			<button style='width: 100%; margin-bottom: 10px' class="btn btn-primary ajax-payment-button" ng-click="saveLesson()">Сохранить</button>
			<button style="width: 100%" class="btn btn-danger ajax-payment-button" ng-click="deleteLesson()">Удалить</button>
		</center>
	</div>
	<!-- /Редактирвоание занятия -->

	<!-- СМС -->
	<sms number='sms_number' templates="full"></sms>
	<!-- /СМС -->
