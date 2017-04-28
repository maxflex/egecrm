<div id="agreement-ooo-print-{{contract.id}}" class="printable">
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
	<h4 style="margin-bottom: 0">Дополнительное соглашение №</h4>
	<h4 style="margin-top: 0">к договору на оказание платных образовательных услуг №{{contract.id_contract}} от {{firstContractInChain(contract).date}} г.</h4>
	<div style="display: inline-block; width: 100%; margin-bottom: 20px">
		<span style="float: left">г. Москва</span>
		<span style="float: right">{{contract.date}} г.</span>
	</div>



	<p> <b>Общество с ограниченной ответственностью «ЕГЭ-ЦЕНТР»</b>, в лице Генерального директора Эрдмана Константина Александровича, действующего на основании Устава, именуемое в дальнейшем «Исполнитель», «ЕГЭ-Центр», на основании лицензии на осуществление образовательной деятельности № 037742, выданной Департаментом образования города Москвы 08.08.2016 г., с одной стороны, и {{contractPrintName(representative, 'nominative')}}, именуемый(ая) в дальнейшем «Заказчик», являющийся(щаяся) родителем (законным представителем) {{contractPrintName(student, 'genitive')}}, {{student.Passport.date_birthday}} года рождения, именуемого(ой) в дальнейшем «Обучающийся», с другой стороны, совместно именуемые «Стороны», заключили настоящее Дополнительное соглашение (далее – «Договор») о нижеследующем:</p>


		<h4>1. ПРЕДМЕТ СОГЛАШЕНИЯ</h4>

	<p>1.1. Стороны пришли к соглашению внести следующие изменения в Договор №{{contract.id_contract}} от {{firstContractInChain(contract).date}} г.:</p>
    <?php foreach(['1.1', '1.3', '1.5', '3.1', '3.2', '3.4'] as $point) :?>
        <p>- Пункт <?= $point ?>. Договора изложить в следующей редакции:
    		«<?= partial("contract/{$point}") ?>»
    	</p>
    <?php endforeach ?>
	<p>1.2. Все обязательства Сторон, предусмотренные Договором и не затронутые настоящим Дополнительным соглашением, остаются в неизменном виде.</p>
	<p>1.3. Настоящее Дополнительное соглашение является неотъемлемой частью Договора.</p>
	<p>1.4. Настоящее Дополнительное соглашение вступает в силу с момента подписания и действует в течение срока действия Договора.</p>

	<p>1.5. Настоящее Дополнительное соглашение составлено в двух экземплярах, имеющих одинаковую юридическую силу, по одному экземпляру для каждой из Сторон.</p>

	<div style='margin: 50px 0 0 0'>
		<div style="display: inline-block; float: left; width: 50%">
			<div>Генеральный директор  ООО «ЕГЭ-Центр»</div>
			<div>Эрдман К. А.</div>
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
