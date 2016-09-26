<!-- @contract-refactored  -->
<div id="act-print-{{contract.id}}" class="printable">

	<style type="text/css">
    .print-bill-body { width: 210mm; margin-left: auto; margin-right: auto; border: none;}
        table.invoice_bank_rekv { border-collapse: collapse; border: 1px solid; }
        table.invoice_bank_rekv > tbody > tr > td, table.invoice_bank_rekv > tr > td { border: 1px solid; }
        table.invoice_items { border: 1px solid; border-collapse: collapse;}
        table.invoice_items td, table.invoice_items th { border: 1px solid;}
        table tr td {font-size: 12px}
        table tr th {font-size: 12px}
        .m_title {display:inline-block}
		.m_title:first-letter {text-transform: uppercase}
    </style>


<div style='text-align: right'>от {{ getLastLessonDate() }}г.</div>
<h4 style="margin-top: 0">АКТ №<br>
сдачи-приемки работ (оказания услуг)
</h4>

<p>Индивидуальный предприниматель Капралов Константин Александрович, действующий на основании Свидетельства о государственной регистрации физического лица в качестве индивидуального предпринимателя серии 62 № 002030621, выданного межрайонной инспекцией Федеральной налоговой службы № 1 по г. Рязани 18 июня 2009 года, находящийся на УСНО, именуемый в дальнейшем «Агент», осуществляющий свою деятельность под коммерческим наименованием «ЕГЭ-Центр», с одной стороны, и гр. {{contractPrintName(representative, 'nominative')}}, именуемый в дальнейшем «Принципал», с другой стороны, именуемые в дальнейшем «Стороны», составили настоящий акт о том, что Агентом были выполнены следующие работы (оказаны следующие услуги) по договору №{{contract.id_contact}} от {{getFirstContractInChain(contract).date}}г.:</p>



      <table class="invoice_items" width="100%" cellpadding="2" cellspacing="2" style="margin-top: 15px">
            <thead>
                <tr>
                    <th style="width:13mm;">№</th>

                    <th>Наименование работы (услуги)</th>

                    <th style="width:12mm;">Количество</th>

                    <th style="width:12mm;">Ед. изм.</th>

                    <th style="width:20mm;">Цена</th>

                    <th style="width:27mm;">Сумма</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td align="center" style="font-size: 12px">1</td>

                    <td align="left" style="font-size: 12px">Агентские услуги согласно договору №{{contract.id}} от {{getFirstContractInChain(contract).date}}г.</td>

                    <td align="center" style="font-size: 12px">1</td>

                    <td align="center" style="font-size: 12px">услуга</td>

                    <td align="right" style="font-size: 12px">{{ contract.sum | number }}-00</td>

                    <td align="right" style="font-size: 12px">{{ contract.sum | number }}-00</td>
                </tr>
            </tbody>
        </table>

        <table border="0" width="100%" cellpadding="1" cellspacing="1">
            <tr>
                <td></td>

                <td style="width:80mm; font-weight:bold;  text-align:right;">Итого:</td>

                <td style="width:27mm; font-weight:bold;  text-align:right;">{{ contract.sum | number }}-00</td>
            </tr>

            <tr>
                <td></td>

                <td style="width:80mm; font-weight:bold;  text-align:right;">Без налога (НДС):</td>

                <td style="width:27mm; font-weight:bold;  text-align:right;">–</td>
            </tr>

            <tr>
                <td></td>

                <td style="width:80mm; font-weight:bold;  text-align:right;">Всего (с учетом НДС):</td>

                <td style="width:27mm; font-weight:bold;  text-align:right;">{{contract.sum | number}}-00</td>
            </tr>
        </table>


<div style='margin: 30px 0'>
Всего оказано услуг на сумму (без НДС): <span class="m_title">{{numToText(contract.sum)}} <ng-pluralize count="contract.sum" when="{
			'one'	: 'рубль',
			'few'	: 'рубля',
			'many'	: 'рублей',
		}"></ng-pluralize> 00 копеек</span>
</div>

<div style='margin: 30px 0'>
Вышеперечисленные работы (услуги) выполнены полностью и в срок. Принципал претензий по объему, качеству и срокам оказания агентских услуг претензий не имеет.
</div>


<div style='margin: 30px 0'>
	<div style="display: inline-block; width: 55%">
		<div>
			<b>Принципал</b>
		</div>
		<div style='margin-top: 30px'>
			____________________<i style='text-decoration: underline'>{{ representative.last_name }} {{ representative.first_name[0] }}. {{ representative.middle_name[0] }}.</i>
		</div>
	</div>
	<div style="display: inline-block; width: 44%">
		<div>
			<b>Агент</b>
		</div>
		<div style='margin-top: 30px'>
			____________________<i style='text-decoration: underline'>Капралов К. А.</i>
		</div>
	</div>
</div>

</div>
