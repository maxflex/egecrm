<div id="service-act-print" class="printable">
    <style>
        p {
            text-align: justify !important;
            text-indent: 2cm;
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
        <div>г. Москва {{formatContractDate2(service_contract.date)}}</div>
        <div>Мы, нижеподписавшиеся:</div>
    </div>

    <p>
        От имени Заказчика: {{representative.last_name}} {{representative.first_name}} {{representative.middle_name}},
        и от имени Исполнителя Генеральный директор ООО «ЕГЭ-ЦЕНТР» Капралов Константин Александрович,
        действующий на основании Устава, составили акт о том,
        что в соответствии с обязательствами, предусмотренными
        Договором от {{formatContractDate2(service_contract_parent.date)}} №{{service_contract_parent.id}}
        Исполнитель оказал Заказчику в полном объеме услуги
        на сумму: {{service_contract.sum}} ({{numToText(service_contract.sum)}}) <ng-pluralize count="service_contract.sum" when="{'one': 'рубль', 'few': 'рубя', 'many': 'рублей'}"></ng-pluralize> 00 копеек.
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
                ___________________/____________________
            </div>
        </div>
        <div style="display: inline-block; width: 50%">
            <div style='margin-top: 30px'>
                ___________________/<span style="text-decoration: underline;">Капралов К. А.</span>
            </div>
        </div>
    </div>

</div>
