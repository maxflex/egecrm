<div id="service-act-ip-print" class="printable">
    <div style="margin-left: 1cm;">
        <style>
            .p-table {
                border-collapse: collapse;
                width: 100%;
                text-align: center;
            }
            .p-table td {
                padding: 5px;
            }
            .p-table td.left {
                text-align: left;
            }
            .p-table td.right {
                text-align: right;
            }
            .full-border td {
                border: 1px solid black;
            }
            .last-border td:last-child {
                border: 1px solid black;
            }
            p {
                text-align: justify !important;
                text-indent: 1cm;
            }
            p.no-ident {
                text-indent: initial;
            }
            .sign-hint {
                text-align: center;
                font-style: italic;
                font-size: 0.8em;
            }
        </style>
        <div style="text-align: right">
            <div>от {{formatContractDate2(service_contract.date)}}</div>
        </div>

        <h4 style="text-align:center;margin-bottom: 0">АКТ</h4>
        <h4 style="text-align:center;margin-top: 0">сдачи-приемки работ (оказания услуг)</h4>

        <p>
            Индивидуальный предприниматель Капралов Константин Александрович,
            действующий на основании Свидетельства о государственной регистрации физического лица в качестве индивидуального предпринимателя серии 62 № 002030621,
            выданного межрайонной инспекцией Федеральной налоговой службы № 1 по г. Рязани 18 июня 2009 года, находящийся на УСНО, именуемый в дальнейшем «Агент»,
            осуществляющий свою деятельность под коммерческим наименованием «ЕГЭ-Центр»,
            с одной стороны, и гр. {{ representative.last_name }} {{ representative.first_name }} {{ representative.middle_name }}, именуемый в дальнейшем «Принципал»,
            с другой стороны, именуемые в дальнейшем «Стороны», составили настоящий акт о том,
            что Агентом были выполнены следующие работы (оказаны следующие услуги) по договору №{{ service_contract_parent.id }} от {{ service_contract_parent.date }}г.:
        </p>

        <br>
        <table class="p-table">
            <tr class="full-border"><td>№</td><td>Наименование работы (услуги)</td><td>Количество</td><td>Ед. изм.</td><td>Цена</td><td>Сумма</td></tr>
            <tr class="full-border">
                <td>1</td>
                <td class="left" width="47%">Агентские услуги согласно договору №{{ service_contract_parent.id }} от {{ service_contract_parent.date }}г.</td>
                <td>1</td>
                <td>услуга</td>
                <td>{{ service_contract.sum }}-00</td>
                <td>{{ service_contract.sum }}-00</td>
            </tr>
            <tr class="last-border">
                <td colspan="5" class="right"><b>Итого:</b></td>
                <td>{{ service_contract.sum }}-00</td>
            </tr>
            <tr class="last-border">
                <td colspan="5" class="right"><b>Без налога (НДС)</b></td>
                <td> - </td>
            </tr>
            <tr class="last-border">
                <td colspan="5" class="right"><b>Всего (с учетом НДС)</b></td>
                <td>{{ service_contract.sum }}-00</td>
            </tr>
        </table>

        <p class="no-ident">
            Всего оказано услуг на сумму (без НДС):  {{ numToText(service_contract.sum) }} <ng-pluralize count="service_contract.sum" when="{'one': 'рубль', 'few': 'рубя', 'many': 'рублей'}"></ng-pluralize> <span style="text-decoration: underline;">00</span> коп.
        </p>
        <p class="no-ident">
            Вышеперечисленные работы (услуги) выполнены полностью и в срок. Принципал претензий по объему, качеству и срокам оказания агентских услуг претензий не имеет.
        </p>




        <div style='margin: 50px 0 0 0; font-weight: bold'>
            <div style="display: inline-block; float: left; width: 50%">
                Принципал
            </div>
            <div style="display: inline-block; width: 50%">
                Агент
            </div>
        </div>
        <div style='margin: 10px 0 0'>
            <div style="display: inline-block; width: 50%">
                <div style="width: 70%; text-align: right;">
                    <div style="border-bottom: 1px solid black;">
                        {{ representative.last_name }} {{ representative.first_name[0] }}. {{ representative.middle_name[0] }}.
                    </div>
                    <div class="sign-hint">
                        (наименование организации)
                    </div>
                </div>
            </div>
            <div style="display: inline-block; width: 50%">
                <div style="width: 70%; text-align: right;">
                    <div style="border-bottom: 1px solid black;">
                        ИП Капралов К. А.
                    </div>
                    <div class="sign-hint">
                        (наименование организации)
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
