<div class="row" style="position: relative" ng-show="current_menu == 7">
	<div class="col-sm-12">
		<?= globalPartial('loading', ['model' => 'TeacherAdditionalPayments']) ?>
		<h4 style='margin-bottom: 10px'>Дополнительные услуги</h4>
		<table class="table">
			<tr ng-repeat="payment in TeacherAdditionalPayments">
				<td width='150'>
					{{ payment.date }}
				</td>
				<td width='150'>
					{{ yearLabel(payment.year) }}
				</td>
				<td width='150'>
					{{ payment.sum | number }} руб.
				</td>
				<td width='350'>
					{{ payment.purpose }}
				</td>
				<td>
	                {{payment.user_login}} {{formatDate(payment.created_at) | date:'dd.MM.yy в HH:mm'}}
	            </td>
	            <td style="text-align: right">
					<a class="link-like" ng-click="editPaymentAdditional(payment)">редактировать</a>
	            </td>
			</tr>
			<tr>
				<td colspan="6">
					<span class="link-like" ng-click="addAdditionalPaymentDialog()">добавить услугу</span>
				</td>
			</tr>
		</table>

		<h4 style='margin-bottom: 10px' ng-show="AdditionalLessons">Дополнительные занятия</h4>
		<table class="table">
			<tr ng-repeat="Lesson in AdditionalLessons">
				<td width='150'>
					{{ Lesson.lesson_date_formatted }} в {{ Lesson.lesson_time }}
				</td>
				<td width='150'>
					{{ yearLabel(Lesson.year) }}
				</td>
				<td width='150'>
					{{ Lesson.teacher_price | number }} руб.
				</td>
				<td width='100'>
					{{Subjects[Lesson.id_subject]}}{{Lesson.grade ? '-' + Lesson.grade_short : ''}}
				</td>
				<td width='100'>
					<span style='color: {{ getCabinet(Lesson.cabinet).color }}'>{{ getCabinet(Lesson.cabinet).label }}</span>
				</td>
				<td width='150' style='cursor: default' title='{{ getStudentsHint(Lesson) }}'>
					{{ Lesson.students.length }} <ng-pluralize count="Lesson.students.length" when="{
						'one': 'ученик',
						'few': 'ученика',
						'many': 'учеников'
					}"></ng-pluralize>
				</td>
				<td>
					<span ng-show='Lesson.is_conducted'>{{ Lesson.credentials }}</span>
				</td>
				<td>
					<a class="pointer" href="lesson/{{ Lesson.id }}">{{ Lesson.is_conducted ? 'проведено' : 'зарегистрировать урок' }}</a>
				</td>
				<td style='text-align: right'>
					<a class="pointer" ng-click="addAdditionalLessonDialog(Lesson)">{{ Lesson.is_planned ? 'редактировать' : 'посмотреть' }}</a>
				</td>
			</tr>
			<tr>
				<td colspan="9">
					<span class="link-like" ng-click="addAdditionalLessonDialog()">добавить занятие</span>
				</td>
			</tr>
		</table>
	</div>
</div>


<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ВНЕПЛАНОВОГО -->
<div class="lightbox-new lightbox-additional-lesson">
	<h4 style="display: inline-block">{{ modal_additional_lesson.id ? "Редактировать" : "Добавить" }} занятие</h4>
	<div class="row" style='position: relative'>
		<div class="div-blocker"></div>
		<div class="col-sm-6">
			<div class="form-group">
				<div class="input-group custom" style="position: relative">
					<span class="input-group-addon">цена преподавателя - </span>
					<input class="form-control digits-only" ng-model="modal_additional_lesson.teacher_price">
				</div>
			</div>
			<div class="form-group">
				<div class="input-group custom" style="position: relative">
					<span class="input-group-addon">дата занятия - </span>
					<input class="form-control bs-date-clear pointer" readonly ng-model="modal_additional_lesson.lesson_date">
				</div>
			</div>
			<div class="form-group">
				<div class="input-group custom" style="position: relative">
					<span class="input-group-addon">время занятия - </span>
					<input type="text" class="form-control timemask" ng-model="modal_additional_lesson.lesson_time">
				</div>
			</div>
			<div class="form-group">
				<angucomplete-alt
				  placeholder="добавить ученика"
				  clear-selected="true"
				  pause="100"
				  selected-object="studentSelected"
				  local-data="Students"
				  search-fields="name"
				  title-field="name"
				  minlength="2"
				  input-class="form-control form-control-small"
				  text-searching="Поиск..."
				  text-no-results="Не найдено"
				  match-class="highlight"
				  auto-match="true"
				/>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="form-group">
				<?= Subjects::buildSelector(false, false, [
					"ng-model" => "modal_additional_lesson.id_subject",
				], true) ?>
			</div>
			<div class="form-group">
				<?= Grades::buildSelector(false, false, ["ng-model" => "modal_additional_lesson.grade"]) ?>
			</div>
			<div class="form-group">
				<select class='form-control full-width branch-cabinet' ng-model='modal_additional_lesson.cabinet'>
					<option selected value=''>кабинет</option>
					<option disabled>──────────────</option>
					<option ng-repeat='cabinet in all_cabinets' value="{{ cabinet.id }}">{{ cabinet.label}}</option>
				</select>
			</div>
			<div class="form-group">
	            <select class="form-control" ng-model="modal_additional_lesson.year" style='margin: 0; width: 100%'>
	                <option value="">выберите год</option>
	                <option disabled>──────────────</option>
	                <option ng-repeat="year in <?= Years::json() ?>"
	                    value="{{year}}">{{ yearLabel(year) }}</option>
	            </select>
			</div>
		</div>
	</div>
	<div class="row" style='padding: 10px 0 20px' ng-show="modal_additional_lesson.students.length">
		<div class="col-sm-12">
			<b style='margin-bottom: 10px'>Состав группы:</b>
			<div class="" ng-repeat="id_student in modal_additional_lesson.students">
				{{ $index + 1}}.
				<a href="/student/{{ id_student}}">{{ getStudentName(id_student) }}</a>
				<a ng-click="deleteStudent($index)" style='margin-left: 5px' class="show-on-hover text-danger">удалить</a>
			</div>
		</div>
	</div>
	<center ng-hide="modal_additional_lesson.is_conducted">
		<button class="btn btn-primary ajax-payment-button full-width" ng-click="saveAdditionalLesson()">{{modal_additional_lesson.id ? "Редактировать" : "Добавить"}}</button>
		<button class="btn btn-primary btn-danger full-width" ng-show="modal_additional_lesson.id"
			style="margin-top: 10px" ng-click="deleteAdditionalLesson()">Удалить</button>
	</center>
</div>
<!-- /ЛАЙТБОКС ДОБАВЛЕНИЕ ВНЕПЛАНОВОГО -->


<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
<div class="lightbox-new lightbox-additional-payment">
	<h4 style="display: inline-block">{{new_additional_payment.id ? "Редактировать" : "Добавить"}} услугу</h4>
	<div class="form-group payment-line">
		<div class="form-group">
			<input style='margin: 0' type="text" placeholder="сумма" class="form-control digits-only full-width" id="add-payment-sum" ng-model="new_additional_payment.sum">
		</div>
		<div class="form-group">
            <select class="form-control" ng-model="new_additional_payment.year" style='margin: 0; width: 100%'>
                <option value="">выберите год</option>
                <option disabled>──────────────</option>
                <option ng-repeat="year in <?= Years::json() ?>"
                    value="{{year}}">{{ yearLabel(year) }}</option>
            </select>
		</div>
		<div class="form-group">
			<input style='margin: 0' placeholder="дата" class="form-control bs-date full-width" id="add-payment-date" ng-model="new_additional_payment.date">
		</div>
		<div class="form-group">
			<textarea maxlength="255" placeholder="назначение" class="form-control full-width" id="add-payment-purpose" ng-model="new_additional_payment.purpose"></textarea>
		</div>
	</div>
	<center>
		<button class="btn btn-primary ajax-payment-button full-width" ng-click="addAdditionalPayment()">{{new_additional_payment.id ? "Редактировать" : "Добавить"}}</button>
		<button class="btn btn-primary btn-danger full-width" ng-show="new_additional_payment.id"
			style="margin-top: 10px" ng-click="deletePaymentAdditional()">Удалить</button>
	</center>
</div>
<!-- /ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
