<div id="service-act-print" class="printable">
    <div style="margin-left:2cm;">

        <style>
            p {
                text-align: justify !important;
                text-indent: 1cm;
            }
            p.no-ident {
                text-indent: initial;
            }
        </style>
        <div style="text-align: right">
            <div>Приложение № 1</div>
            <div>к Договору оказания платных</div>
            <div>образовательных услуг</div>
            <div>№{{service_contract_parent.id}} от {{service_contract_parent.date}}</div>
        </div>

        <h4 style="text-align:center;margin-bottom: 0">АКТ ОБ ОКАЗАННЫХ УСЛУГАХ</h4>
        <h4 style="text-align:center;margin-top: 0">ПО ДОГОВОРУ №{{service_contract_parent.id}} от {{service_contract_parent.date}}</h4>

        <div style="display: inline-block; width: 100%; margin-bottom: 20px">
            <div>г. Москва {{formatContractDate2(service_contract.date_original)}}</div>
            <div>Мы, нижеподписавшиеся:</div>
        </div>

        <p>
            От имени Заказчика: {{representative.last_name}} {{representative.first_name}} {{representative.middle_name}},
            и от имени Исполнителя Генеральный директор ООО «ЕГЭ-ЦЕНТР» Эрдман Константин Александрович,
            действующий на основании Устава, составили акт о том,
            что в соответствии с обязательствами, предусмотренными
            Договором от {{formatContractDate2(service_contract_parent.date_original)}} №{{service_contract_parent.id}}
            Исполнитель оказал Заказчику в полном объеме услуги
            на сумму:
			{{ getContractSum(service_contract) | number }}
			({{ numToText(getContractSum(service_contract)) }} ) <ng-pluralize count="getContractSum(service_contract)" when="{'one': 'рубль', 'few': 'рубля', 'many': 'рублей'}"></ng-pluralize> 00 копеек.
            НДС не облагается. Заказчик претензий к Исполнителю не имеет.
        </p>

        <p class="no-ident">Настоящий акт составлен в двух экземплярах, по одному для каждой Стороны.</p>

        <div style='margin: 50px 0 0 0'>
            <div style="display: inline-block; float: left; width: 50%">
                Заказчик
            </div>
            <div style="display: inline-block; width: 50%">
                Исполнитель
            </div>
        </div>
        <div style='margin: 50px 0 0 0'>
            <div style="display: inline-block; float: left; width: 50%">
            </div>
            <div style="display: inline-block; width: 50%">
                Генеральный директор ООО «ЕГЭ- Центр»
            </div>
        </div>

        <div style='margin: 50px 0 0'>
            <div style="display: inline-block; width: 50%">
                <div style='margin-top: 30px'>
                    ________________/________________
                </div>
            </div>
            <div style="display: inline-block; width: 50%">
                <div style='margin-top: 30px'>
                    ________________/<span style="text-decoration: underline;">Эрдман К. А.</span>
                </div>
            </div>
        </div>
    </div>
</div>
