<div class="print-bill-body printable" id="pko-print">
    <style type="text/css">
        .printable .bold-border {border: 2px solid black;}
        .printable .bold-border-top {border-top: 3px solid black;}
        .printable .bold-border-bottom {border-bottom: 3px solid black;}
        .printable .padding {padding: 5px;}
        .printable h3 {margin: 5px;}
    </style>

    <table width="100%" cellpadding="2" cellspacing="2" class="invoice_bank_rekv">
        <tr>
            <td width="65%" align="right"  style="font-size:8px;padding:5px">
                Унифицированная форма КО-1 <br>
                Утверждена постановлением Госкомстата России от 18.08.98 №88
            </td>
            <td width="35%" style="padding:5px;border-left: 1px dotted black;" align="center">
                <table width="100%">
                    <tr><td style="border-bottom: 2px solid black" align="center"><b>ООО "ЕГЭ-Центр"</b></td></tr>
                    <tr style="font-size:8px;text-align: center;"><td>организация</td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td width="65%" style="padding:5px;">
                <table width="100%" style="vertical-align: top;">
                    <tr>
                        <td>
                            <table style="border-collapse:collapse;">
                                <tr>
                                    <td width="65%">
                                        <table width="100%">
                                            <tr><td style="border-bottom:2px solid black;">ООО "ЕГЭ-Центр"</td></tr>
                                            <tr style="font-size:8px;text-align: center;"><td align="center">организация</td></tr>
                                            <tr><td style="border-bottom:2px solid black;" align="center"></td></tr>
                                            <tr style="font-size:8px;text-align: center;"><td align="center">подразделение</td></tr>
                                        </table>
                                    </td>
                                    <td width="35%">
                                        <table width="100%"  style="border-collapse:collapse;">
                                            <tr>
                                                <td rowspan="4" width="50%">Форма по ОКУД <br>по ОКПО</td>
                                                <td width="50%" style="border:2px solid black;">Коды</td>
                                            </tr>
                                            <tr><td style="border-left: 3px solid black;border-right: 3px solid black;border-top:3px solid black;">0310001</td></tr>
                                            <tr><td style="border-left: 3px solid black;border-right: 3px solid black; border-top:2px solid black;">02008879</td></tr>
                                            <tr><td style="border-left: 3px solid black;border-right: 3px solid black;border-bottom: 3px solid black; border-top:2px solid black;">02008879</td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table>
                                <tr>
                                    <td width="50%"><h3>ПРИХОДНЫЙ КАССОВЫЙ ОРДЕР</h3></td>
                                    <td width="50%">
                                        <table width="100%" style="border-collapse:collapse;">
                                            <tr><td width="50%" style="border: 2px solid black;">Номер документа</td><td style="border: 2px solid black;">Дата составления</td></tr>
                                            <tr><td style="border: 3px solid black;">{{PrintPayment.document_number}}</td><td style="border: 3px solid black;">{{PrintPayment.date}}</td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table style="border-collapse: collapse;">
                                <tr>
                                    <td rowspan="2" style="border:2px solid black;">Дебет</td>
                                    <td colspan="4" style="border:2px solid black;" width="50%">Кредит</td>
                                    <td rowspan="2" style="border:2px solid black;">Сумма, руб. коп.</td>
                                    <td rowspan="2" style="border:2px solid black;">Код целевого назначения</td>
                                    <td rowspan="2" style="border:2px solid black;padding:20px;"></td>
                                </tr>
                                <tr>
                                    <td style="border:2px solid black;padding:20px;"></td>
                                    <td style="border:2px solid black;">код структурного подразделения</td>
                                    <td style="border:2px solid black;">корреспондирующий счет, субсчет</td>
                                    <td style="border:2px solid black;">код аналитического учета</td>
                                </tr>
                                <tr style="border: 3px solid black;">
                                    <td style="border-left:3px solid black;border-top:3px solid black;border-right:2px solid black;border-bottom:3px solid black;">50.02</td>
                                    <td style="padding:10px;border:2px solid black;border-bottom:3px solid black;border-top:3px solid black;"></td>
                                    <td style="border:2px solid black;border-bottom:3px solid black;border-top:3px solid black;"></td>
                                    <td style="border:2px solid black;border-bottom:3px solid black;border-top:3px solid black;">62.01, 62.02</td>
                                    <td style="border:2px solid black;border-bottom:3px solid black;border-top:3px solid black;"></td>
                                    <td style="border:2px solid black;border-bottom:3px solid black;border-top:3px solid black;">{{PrintPayment.sum | number}}, 00</td>
                                    <td style="border:2px solid black;border-bottom:3px solid black;border-top:3px solid black;"></td>
                                    <td style="padding:20px;border:2px solid black;border-bottom:3px solid black;border-top:3px solid black;border-right:3px solid black;"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>Принято от: {{representative.last_name }} {{representative.first_name }} {{representative.middle_name }}</td>
                    </tr>

                    <tr><td style="padding:10px;"></td></tr>

                    <tr><td>Основание: <br>Договор на оказание платных образовательных услуг <br>№{{ contracts[0].id }} от {{contracts[0].date}}г.</td></tr>

                    <tr><td style="padding:10px;"></td></tr>

                    <tr>
                        <td colspan="2">{{numToText(PrintPayment.sum)}} <ng-pluralize count="PrintPayment.sum" when="{
                            'one'	: 'рубль',
                            'few'	: 'рубля',
                            'many'	: 'рублей',
                        }"></ng-pluralize> 00 копеек</td>
                    </tr>

                    <tr style="padding:10px;"><td></td></tr>

                    <tr>
                        <td>В том числе: НДС (Без НДС) 0-00 руб.</td>
                    </tr>
                    <tr>
                        <td>Приложение:</td>
                    </tr>

                    <tr>
                        <td>
                            <table width="100%">
                                <tr>
                                    <td><b>Главный бухгалтер</b></td>
                                    <td style="border-bottom: 2px solid black"></td>
                                    <td cellpadding="10"></td>
                                    <td style="border-bottom: 2px solid black">Капралов К. А.</td>
                                </tr>
                                <tr style="font-size:8px;text-align: center;">
                                    <td></td>
                                    <td>подпись</td>
                                    <td></td>
                                    <td>расшифровка подписи</td>
                                </tr>
                                <tr>
                                    <td><b>Получил кассир</b></td>
                                    <td style="border-bottom: 2px solid black"></td>
                                    <td cellpadding="10"></td>
                                    <td style="border-bottom: 2px solid black">Капралов К. А.</td>
                                </tr>
                                <tr style="font-size:8px;text-align: center;">
                                    <td></td>
                                    <td>подпись</td>
                                    <td></td>
                                    <td>расшифровка подписи</td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>
            </td>
            <td width="35%" style="vertical-align: top;border-left: 1px dotted black;">
                <table>
                    <tr><td colspan="2" align="center"><h3><b>Квитанция</b></h3></td></tr>
                    <tr><td colspan="2" align="center" style="border-bottom: 2px solid black;">к приходному кассовому ордеру №{{PrintPayment.document_number}}</td></tr>
                    <tr><td align="right" width="40%">от </td><td align="left" style="border-bottom: 2px solid black"><b>{{formatContractDate(PrintPayment.date)}}</b></td></tr>

                    <tr style="padding:10px;"><td colspan="2"></td></tr>
                    <tr><td colspan="2">Принято от <br>{{representative.last_name}} {{representative.first_name}} {{representative.middle_name}}</td></tr>

                    <tr style="padding:10px;"><td colspan="2"></td></tr>
                    <tr><td colspan="2">Основание <br>Договор на оказание платных образовательных услуг № {{contracts[0].id}} от {{contracts[0].date}}г.</td></tr>

                    <tr style="padding:10px;"><td colspan="2"></td></tr>

                    <tr><td>Сумма</td><td  style="border-bottom: 2px solid black;"><b>{{PrintPayment.sum | number}} руб. 00 коп.</b></td></tr>
                    <tr style="font-size:8px;text-align: center;"><td></td><td ali>цифрами</td></tr>
                    <tr>
                        <td colspan="2">{{ucfirst(numToText(PrintPayment.sum))}} <ng-pluralize count="PrintPayment.sum" when="{
                            'one'	: 'рубль',
                            'few'	: 'рубля',
                            'many'	: 'рублей',
                        }"></ng-pluralize> 00 копеек</td>
                    </tr>

                    <tr style="padding:10px;"><td colspan="2"></td></tr>
                    <tr><td colspan="2">в том числе <br>НДС (Без НДС) 0-00руб.</td></tr>

                    <tr style="padding:10px;"><td colspan="2"></td></tr>
                    <tr style="padding:10px;"><td colspan="2"></td></tr>
                    <tr>
                        <td width="30%"></td>
                        <td style="border-bottom: 2px solid black;"><b>{{formatContractDate(PrintPayment.date)}}</b></td>
                    </tr>
                    <tr>
                        <td colspan="2"><b>М.П. (штампа)<br>Главный бухгалтер</b></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table width="100%">
                                <tr>
                                    <td style="border-bottom: 2px solid black;"></td>
                                    <td width="20%"></td>
                                    <td style="border-bottom: 2px solid black;">Капралов К. А.</td>
                                </tr>
                                <tr style="font-size:8px;text-align: center;">
                                    <td>подпись</td>
                                    <td width="20%"></td>
                                    <td>расшифровка подписи</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr><td colspan="2"><b>Кассир</b></td></tr>
                    <tr>
                        <td colspan="2">
                            <table width="100%">
                                <tr>
                                    <td style="border-bottom: 2px solid black;"></td>
                                    <td width="20%"></td>
                                    <td style="border-bottom: 2px solid black;">Капралов К. А.</td>
                                </tr>
                                <tr style="font-size:8px;text-align: center;">
                                    <td>подпись</td>
                                    <td width="20%"></td>
                                    <td>расшифровка подписи</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>