<div id="act-print" class="printable">
	<style>
	p {
		text-align: justify !important;
		text-indent: 2cm;
	}
    .ng-hide {
	    display: none !important;
    }
    ul {
		margin: 0;
        -webkit-padding-start: 100px;
	}
    br {
        display: none;
    }
    </style>
	<h4 style="margin-bottom: 0">АКТ СДАЧИ-ПРИЕМКИ ОКАЗАННЫХ УСЛУГ </h4>
	<h4 style="margin-top: 0">ДОГОВОР ВОЗМЕЗДНОГО ОКАЗАНИЯ УСЛУГ №{{ Group.Teacher.last_name }}-1 от {{ todayDate() }}</h4>
	<div style="display: inline-block; width: 100%; margin-bottom: 20px">
		<span style="float: left">г. Москва</span>
		<span style="float: right">{{ todayDate() }}</span>
	</div>



	<p>
		ООО «ЕГЭ-Центр», именуемое в дальнейшем «<i><b>Заказчик</i></b>», в лице Генерального директора Эрдмана Константина Александровича,
		действующего на основании Устава, с одной стороны, и <i><b>гражданин (ка) РФ гражданин (ка) РФ {{ Group.Teacher.last_name }} {{ Group.Teacher.first_name }} {{ Group.Teacher.middle_name }}</i></b>, именуемый (ая)
		в дальнейшем «<i><b>Исполнитель</i></b>», с другой стороны, совместно именуемые «Стороны», составили настоящий Акт о нижеследующем:
	</p>

	<p>
		1. Исполнитель по договору возмездного оказания услуг №{{ Group.Teacher.last_name }}-1 от {{ todayDate() }} (далее именуется договор) оказал следующие услуги:
	</p>
	<p>
		Наименование дисциплины/ курса - <b>{{ SubjectsFull[Group.id_subject] }}-{{ Group.grade}}-30</b>.
	</p>
	<p>
		Вид учебной работы - <b>Лекции и практические занятия</b>.
	</p>
	<p>
		Цена услуги за 1 занятие - <b>{{ Group.teacher_price }} ({{ numToText(Group.teacher_price) }} руб. 00 коп.  РФ)</b>.
	</p>
	<p>
		Количество занятий – <b>{{ Group.lesson_count.all }} ({{ numToText(Group.lesson_count.all) }})</b>.
	</p>

<p>2. Услуги, перечисленные в п. 1 настоящего акта оказаны в период <b>с {{ todayDate() }} по 19.07.2018 г.</b></p>
<p>3.  Общая стоимость оказанных услуг составляет <b>{{ Group.lesson_count.all * Group.teacher_price }} ({{ numToText(Group.lesson_count.all * Group.teacher_price) }} руб. 00 коп. РФ)</b> (с учетом суммы НДФЛ-13%).</p>
<p>4. Заказчик претензий по качеству, количеству и срокам оказания услуг претензий не имеет.</p>
<p>5. Настоящий акт является основанием для проведения расчетов по оказанным Исполнителем услугам. Оплата производится наличными денежными средствами через кассу Заказчика.
Стороны допускают проведение взаиморасчетов (оплаты оказанных услуг) в безналичном порядке путем перечисления денежных средств по реквизитам Исполнителя, указанным в договоре.</p>

<p>6. Настоящий Акт составлен в двух экземплярах по одному для каждой из сторон и является неотъемлемой частью договора.</p>
<p>7. Адреса, реквизиты и подписи сторон:</p>




	<div style='margin: 50px 0 0 0'>
		<div style="display: inline-block; float: left; width: 50%">
			<div>
				<b>ЗАКАЗЧИК:</b>
			</div>
			<div>
				Общество с ограниченной ответственностью
			</div>
			<div>
				«ЕГЭ-Центр»
			</div>
			<div>
				ИНН / КПП 9701038111/770101001
			</div>
			<div>
				р/с 40702810801960000153
			</div>
			<div>
				в АО «Альфа-Банк»
			</div>
		</div>
		<div style="display: inline-block; width: 50%">
			<div>
				<b>ИСПОЛНИТЕЛЬ:</b>
			</div>
			<div>
				{{ Group.Teacher.last_name }} {{ Group.Teacher.first_name }} {{ Group.Teacher.middle_name }}
			</div>
			<div>
				Паспорт РФ: серия #### №######
			</div>
			<div>
				Выдан: #########
			</div>
			<div>
				Код подразделения: ###-###
			</div>
			<div>
				Зарегистрирован по адресу: ###-###
			</div>
		</div>
	</div>

	<div style='margin: 50px 0 0'>
		<div style="display: inline-block; width: 50%">
			<div style='margin-top: 30px'>
				Генеральный директор __________/К.А. Эрдман
			</div>
		</div>
		<div style="display: inline-block; width: 50%">
			<div style='margin-top: 30px'>
				____________________/{{ Group.Teacher.first_name[0] }}. {{ Group.Teacher.middle_name[0] }}. {{ Group.Teacher.last_name }}
			</div>
		</div>
	</div>

</div>
