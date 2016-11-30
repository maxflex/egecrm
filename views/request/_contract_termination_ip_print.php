<div id="termination-act-ip-print" class="printable" xmlns="http://www.w3.org/1999/html">
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
            <div>к Договору №{{ term_contract_parent.id }}</div>
            <div>от {{ formatContractDate2(term_contract_parent.date) }}</div>
        </div>

        <h4 style="text-align:center;margin-bottom: 0">Соглашение</h4>
        <h4 style="text-align:center;margin-top: 0">о расторжении договора</h4>

        <div style="display: inline-block; width: 100%; margin-bottom: 20px">
            <div style="float: left;">г. Москва</div>
            <div style="float: right;">{{formatContractDate2(term_contract.date)}}</div>
        </div>

        <p class="no-ident">
            Индивидуальный предприниматель Капралов Константин Александрович,
            действующий на основании Свидетельства о государственной регистрации физического лица в качестве индивидуального предпринимателя серии 62 № 002030621,
            выданного межрайонной инспекцией Федеральной налоговой службы № 1 по г. Рязани 18 июня 2009 года,
            находящийся на УСНО, именуемый в дальнейшем «Агент»,
            осуществляющий свою деятельность под коммерческим наименованием «ЕГЭ-Центр», с одной стороны,
            и гр. {{representative.last_name}} {{representative.first_name}} {{representative.middle_name}}, именуемая в дальнейшем «Принципал»,
            с другой стороны, именуемые в дальнейшем «Стороны», подписали настоящий Акт о нижеследующем:

            <ol>
                <li>
                    Стороны прекращают оказание агентских услуг по Договору №{{ term_contract_parent.id }} от {{ formatContractDate2(term_contract_parent.date) }} с {{ formatContractDate2(term_contract.date) }}
                </li>
                <li>
                    По состоянию на дату прекращения оказания агентских услуг размер вознаграждения Агенту за действия,
                    совершенные им до прекращения Договора,
                    составил {{ term_contract.sum }} ({{numToText(term_contract.sum)}}) <ng-pluralize count="term_contract.sum" when="{'one': 'рубль', 'few': 'рубя', 'many': 'рублей'}"></ng-pluralize> 00 копеек., без НДС.
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
                Принципал:
            </div>
            <div style="display: inline-block; width: 50%">
                Агент:
            </div>
        </div>
        <div style='margin: 10px 0 0'>
            <div style="display: inline-block; width: 50%">
                <div style='margin-top: 30px'>
                    ________________/________________
                </div>
                <div style="padding-left: 100px;">
                    М. П.
                </div>
            </div>
            <div style="display: inline-block; width: 50%">
                <div style='margin-top: 30px'>
                    ________________/<span style="text-decoration: underline;">ИП Капралов К. А.</span>
                </div>
                <div style="padding-left: 100px;">
                    М. П.
                </div>
            </div>
        </div>
    </div>
</div>