<div id="termination-ooo-print" class="printable">
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
            <div>Приложение № 2</div>
            <div>к Договору №{{ term_contract_parent.id }}</div>
            <div>от {{ formatContractDate2(term_contract_parent.date_original) }}</div>
        </div>

        <h4 style="text-align:center;margin-bottom: 0">Соглашение</h4>
        <h4 style="text-align:center;margin-top: 0">о расторжении договора</h4>

        <div style="display: inline-block; width: 100%; margin-bottom: 20px">
            <div style="float: left;">г. Москва</div>
            <div style="float: right;">{{formatContractDate2(term_contract.date_original)}}</div>
        </div>

        <p class="no-ident">
            Общество с ограниченной ответственностью «ЕГЭ-ЦЕНТР», в лице Генерального директора Эрдмана Константина Александровича, действующего на основании Устава, именуемое в дальнейшем «Исполнитель», «ЕГЭ-Центр», на основании лицензии на осуществление образовательной деятельности № 037742, выданной Департаментом образования города Москвы 08.08.2016 г., с одной стороны, и
            и {{representative.last_name}} {{representative.first_name}} {{representative.middle_name}}, именуемый(ая) в дальнейшем «Заказчик», являющийся(щаяся) родителем (законным представителем)
            {{contractPrintName(student, 'genitive')}}, {{ student.Passport.date_birthday }} года рождения,
            именуемого(ой) в дальнейшем «Обучающийся», с другой стороны, совместно именуемые «Стороны», подписали настоящий Акт о нижеследующем:
        <ol>
            <li>
                Стороны прекращают оказание платных образовательных услуг по Договору №{{ term_contract_parent.id }} от {{ formatContractDate2(term_contract_parent.date_original) }} с {{ formatContractDate2(term_contract.date_original) }}
            </li>
            <li>
                По состоянию на дату прекращения оказания платных образовательных услуг размер вознаграждения Исполнителю за действия,
                совершенные им до прекращения Договора,
                составил {{ term_contract.sum }} ({{numToText(term_contract.sum)}}) <ng-pluralize count="term_contract.sum" when="{'one': 'рубль', 'few': 'рубля', 'many': 'рублей'}"></ng-pluralize> 00 копеек., без НДС.
            </li>
            <li>
                Финансовых претензий Стороны друг к другу не имеют.
            </li>
            <li>
                Настоящий Акт составлен в двух экземплярах, по одному для каждой из Сторон, и является неотъемлемой частью Договора.
            </li>
        </ol>
        </p>

        <h4 style="text-align:center;margin-bottom: 0; font-weight: 400;">ПОДПИСИ СТОРОН:</h4>


        <div style='margin: 50px 0 0 0'>
            <div style="display: inline-block; float: left; width: 50%">
                Заказчик:
            </div>
            <div style="display: inline-block; width: 50%">
                Исполнитель: <br>
                Генеральный директор ООО «ЕГЭ-Центр»
            </div>
        </div>
        <div style='margin: 10px 0 0'>
            <div style="display: inline-block; width: 50%">
                <div style='margin-top: 30px'>
                    ________________/<span style="text-decoration: underline;">{{ representative.last_name }} {{ representative.first_name[0] }}. {{ representative.middle_name[0] }}.</span>
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
