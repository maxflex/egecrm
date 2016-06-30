<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
	<div id="addpayment" class="lightbox-new lightbox-addpayment" style="width: 551px; left: calc(50% - 275px)">
		<h4>{{new_payment.id ? "Редактировать" : "Добавить"}} платеж</h4>
		<div class="form-group payment-line">
			<div class="form-group inline-block">
				<?= Payment::buildSelector(false, false, ["ng-model" => "new_payment.id_status", "style" => "width: 180px"]) ?>
		    </div>
			<div class="form-group inline-block">
				на сумму
		    </div>
			<div class="form-group inline-block">
				<input type="text" class="form-control digits-only" id="payment-sum" ng-model="new_payment.sum"  ng-keydown="watchEnter($event)"> от
			</div>
			<div class="form-group inline-block">
                <input class="form-control bs-date" id="payment-date" ng-model="new_payment.date" pattern="[0-9]{2}.[0-9]{2}.[0-9]{4}">
			</div>
		</div>
        <script>
            $("#payment-date").inputmask("99.99.9999");
        </script>
		<div class="form-group payment-inline" ng-show="new_payment.id_status == <?= Payment::PAID_CARD ?>">
			<h4>Номер карты</h4>
			<div class="form-group inline-block">
				<input class="form-control" disabled placeholder="XXXX" style="width: 60px; display: inline-block; margin-left: 5px"> -
				<input class="form-control" disabled placeholder="XXXX" style="width: 60px; display: inline-block"> -
				<input class="form-control" disabled placeholder="XXXX" style="width: 60px; display: inline-block"> -
				<input class="form-control digits-only" id="payment-card" maxlength="4" ng-model="new_payment.card_number"
					style="width: 60px; display: inline-block">
			</div>
		</div>
		<center>
			<button class="btn btn-primary" ng-click="addPayment()">{{new_payment.id ? "Редактировать" : "Добавить"}}</button>
		</center>
	</div>
	<!-- /ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->

	<div class="row" style="position: relative" ng-show="current_menu == 2">
		<div class="col-sm-12">
			<?= globalPartial('loading', ['model' => 'Lessons', 'message' => 'нет проведенных занятий']) ?>
			<table class="table table-divlike">
				<tr ng-repeat="Lesson in Lessons">
					<td>
						<a href="groups/edit/{{Lesson.id_group}}">Группа №{{Lesson.id_group}}</a>
					</td>
					<td>
						{{formatDateMonthName(Lesson.lesson_date)}}
					</td>
					<td>
						{{formatTime(Lesson.lesson_time)}}
					</td>
					<td>
						{{Lesson.teacher_price | number}} рублей
					</td>
				</tr>
				<tr ng-if="Lessons && payments">
					<td colspan="2"></td>
					<td><b>к выплате</b></td>
					<td><b>{{toBePaid() | number}} рублей</b></td>
				</tr>
			</table>

		</div>
	</div>