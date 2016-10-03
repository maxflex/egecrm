<div id="agreement-ooo-print-{{contract.id}}" class="printable">
	<style>
	p {
		text-align: justify !important;
		text-indent: 2cm;
	}
    </style>
	<h4 style="margin-bottom: 0">Дополнительное соглашение №</h4>
	<h4 style="margin-top: 0">к договору на оказание платных образовательных услуг №{{contract.id_contract}} от {{firstContractInChain(contract).date}} г.</h4>
	<div style="display: inline-block; width: 100%; margin-bottom: 20px">
		<span style="float: left">г. Москва</span>
		<span style="float: right">{{contract.date}} г.</span>
	</div>



	<p> <b>Общество с ограниченной ответственностью «ЕГЭ-ЦЕНТР»</b>, в лице Генерального директора Капралова Константина Александровича, действующего на основании Устава, именуемое в дальнейшем «Исполнитель», «ЕГЭ-Центр», на основании лицензии на осуществление образовательной деятельности № 037742, выданной Департаментом образования города Москвы 08.08.2016 г., с одной стороны, и {{contractPrintName(representative, 'nominative')}}, именуемый(ая) в дальнейшем «Заказчик», являющийся(щаяся) родителем (законным представителем) {{contractPrintName(student, 'genitive')}}, {{student.Passport.date_birthday}} года рождения, именуемого(ой) в дальнейшем «Обучающийся», с другой стороны, совместно именуемые «Стороны», заключили настоящее Дополнительное соглашение (далее – «Договор») о нижеследующем:</p>


		<h4>1. ПРЕДМЕТ СОГЛАШЕНИЯ</h4>

	<p>1.1. Стороны пришли к соглашению внести следующие изменения в Договор №{{contract.id_contract}} от {{firstContractInChain(contract).date}} г.:</p>

	<p>- Пункт 1.3. Договора изложить в следующей редакции:<br>
		«Продолжительность образовательной программы по программе курса
		<span ng-repeat="program in contract.subjects">
			«{{SubjectsFull2[program.id_subject]}}-{{contract.info.grade}}-{{(program.count * 1) + (program.count2 * 1)}}» ({{((program.count * 1) + (program.count2 * 1))*3}}
			аудиторных <ng-pluralize count="((program.count * 1) + (program.count2 * 1))*3" when="{'one' : 'час', 'few' : 'часа', 'many' : 'часов'}"></ng-pluralize> и {{((program.count * 1) + (program.count2 * 1))*1.5}}
			 <ng-pluralize count="((program.count * 1) + (program.count2 * 1))*1.5" when="{'one' : 'час', 'few' : 'часа', 'many' : 'часов'}"></ng-pluralize> на самостоятельную подготовку){{$last ? '.' : ','}}
		</span> Форма обучения – очная.
	</p>
	<p>- Пункт 3.1. Договора изложить в следующей редакции:<br>
		«Общая стоимость Услуг Исполнителя по Договору складывается из стоимости {{ subjectCount(contract) }} <ng-pluralize count="subjectCount(contract)" when="{
			'one' 	: 'занятия',
			'few'	: 'занятий',
			'many'	: 'занятий',
		}"></ng-pluralize>, приобретаемых на момент заключения Договора, и составляет {{contract.sum | number}} (<span class="m_title">{{numToText(contract.sum)}}</span>)
		 <ng-pluralize count="contract.sum" when="{
			'one'	: 'рубль',
			'few'	: 'рубля',
			'many'	: 'рублей',
		}"></ng-pluralize>.»
	</p>
	<p>1.2. Все обязательства Сторон, предусмотренные Договором и не затронутые настоящим Дополнительным соглашением, остаются в неизменном виде.</p>
	<p>1.3. Настоящее Дополнительное соглашение является неотъемлемой частью Договора.</p>
	<p>1.4. Настоящее Дополнительное соглашение вступает в силу с момента подписания и действует в течение срока действия Договора.</p>

	<p>1.5. Настоящее Дополнительное соглашение составлено в двух экземплярах, имеющих одинаковую юридическую силу, по одному экземпляру для каждой из Сторон.</p>

	<div style='margin: 50px 0 0 0'>
		<div style="display: inline-block; float: left; width: 50%">
			Генеральный директор  ООО «ЕГЭ-Центр»<br>
				Капралов К. А.
		</div>
		<div style="display: inline-block; width: 50%">
			{{ representative.last_name }} {{ representative.first_name[0] }}. {{ representative.middle_name[0] }}
		</div>
	</div>

	<div style='margin: 50px 0 0'>
		<div style="display: inline-block; width: 50%">
			<div style='margin-top: 30px'>
				____________________<i style='text-decoration: underline'></i>
			</div>
		</div>
		<div style="display: inline-block; width: 50%">
			<div style='margin-top: 30px'>
				____________________<i style='text-decoration: underline'></i>
			</div>
		</div>
	</div>

</div>
