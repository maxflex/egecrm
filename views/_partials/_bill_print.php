    <div class="print-bill-body printable" id="bill-print">
	     <style type="text/css">
             @media only print {
                 .print-bill-body {
                     width: 210mm;
                     margin-left: auto;
                     margin-right: auto;
                     border: none;
                 }

                 .printable table.invoice_bank_rekv {
                     border-collapse: collapse;
                     border: 1px solid;
                 }

                 .printable table.invoice_bank_rekv > tbody > tr > td, table.invoice_bank_rekv > tr > td {
                     border: 1px solid;
                 }

                 .printable table.invoice_items {
                     border: 1px solid;
                     border-collapse: collapse;
                 }

                 .printable table.invoice_items td, table.invoice_items th {
                     border: 1px solid;
                 }

                 .printable table tr td {
                     font-size: 12px
                 }

                 .printable table tr th {
                     font-size: 12px
                 }

                 .m_title {
                     display: inline-block
                 }

                 .m_title:first-letter {
                     text-transform: uppercase
                 }
             }
    </style>

        <center>
            <div style="width: 70%; font-size: 10px; margin-bottom: 10px">
                Внимание! Оплата данного счета означает согласие с условиями поставки товара. Уведомление об оплате обязательно, в противном случае не гарантируется наличие товара на складе. Товар отпускается по факту прихода денег на р/с Поставщика, самовывозом, при наличии доверенности и паспорта.
            </div>
        </center>

        <table width="100%" cellpadding="2" cellspacing="2" class="invoice_bank_rekv">
            <tr>
                <td colspan="2" rowspan="2" style="min-height:13mm; width: 105mm;">
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="height: 10mm;">
                        <tr>
                            <td valign="top">
                                <div>
                                    АО "АЛЬФА-БАНК"
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td valign="bottom" style="height: 3mm;">
                                <div style="font-size:10pt;">
                                    Банк получателя
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>

                <td style="min-height:7mm;height:auto; width: 25mm;">
                    <div>
                        БИK
                    </div>
                </td>

                <td rowspan="2" style="vertical-align: top; width: 60mm;">
                    <div style=" height: 7mm; line-height: 7mm; vertical-align: middle;">
                        044525593
                    </div>

                    <div>
                        30101810200000000593
                    </div>
                </td>
            </tr>

            <tr>
                <td style="width: 25mm;">
                    <div>
                        Сч. №
                    </div>
                </td>
            </tr>

            <tr>
                <td style="min-height:6mm; height:auto; width: 50mm;">
                    <div>
                        ИНН 622709802649
                    </div>
                </td>

                <td style="min-height:6mm; height:auto; width: 55mm;">
                    <div>
                        КПП <span style='display: inline-block; margin-left: 10px'>0</span>
                    </div>
                </td>

                <td rowspan="2" style="min-height:19mm; height:auto; vertical-align: top; width: 25mm;">
                    <div>
                        Сч. №
                    </div>
                </td>

                <td rowspan="2" style="min-height:19mm; height:auto; vertical-align: top; width: 60mm;">
                    <div>
                        40802810002390000312
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="2" style="min-height:13mm; height:auto;">
                    <table border="0" cellpadding="0" cellspacing="0" style="height: 10mm; width: 105mm;">
                        <tr>
                            <td valign="top">
                                <div>
                                    ИП Эрдман Константин Александрович
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td valign="bottom" style="height: 3mm;">
                                <div style="font-size: 10px;">
                                    Получатель
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table><br>

        <div style="font-weight: bold; font-size: 16px; padding-left:5px; margin-bottom: 5px; border-bottom: 2px solid black; padding-bottom: 5px">
            Счет на оплату №{{contracts[0].id}} от {{formatContractDate(PrintPayment.date)}}
        </div>

        <table width="100%">
            <tr>
                <td style="width: 30mm;">
                    <div style=" padding-left:2px;">
                        Поставщик:
                    </div>
                </td>

                <td>
                    <div style="font-weight:bold;  padding-left:2px;">
                        ИП Эрдман Константин Александрович, ИНН 622709802649, КПП 0, 390044, Россия, Рязанская обл., г.Рязань, ул. Костычева, д.11, кв.33, тел.: 8 (495) 646-85-92
                    </div>
                </td>
            </tr>

            <tr>
                <td style="width: 30mm; padding: 7px 0">
                    <div style=" padding-left:2px;">
                        Покупатель:
                    </div>
                </td>

                <td>
                    <div style="font-weight:bold; padding: 5px 0 5px 2px"></div>
                </td>
            </tr>
        </table>

        <table class="invoice_items" width="100%" cellpadding="2" cellspacing="2">
            <thead>
                <tr>
                    <th style="width:13mm;">№</th>

                    <th>Товары (работы, услуги)</th>

                    <th style="width:12mm;">Кол-во</th>

                    <th style="width:12mm;">Ед.</th>

                    <th style="width:20mm;">Цена</th>

                    <th style="width:27mm;">Сумма</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td align="center" style="font-size: 12px">1</td>

                    <td align="left" style="font-size: 12px">Предметно-консультационные услуги согласно договору №{{contracts[0].id}} от {{contracts[0].date}}г. Без НДС.</td>

                    <td align="right" style="font-size: 12px">{{objectLength(contracts[0].subjects)}}</td>

                    <td align="left" style="font-size: 12px">предмет</td>

                    <td align="right" style="font-size: 12px">{{(PrintPayment.sum / objectLength(contracts[0].subjects)) | number}},00</td>

                    <td align="right" style="font-size: 12px">{{PrintPayment.sum | number}},00</td>
                </tr>
            </tbody>
        </table>

        <table border="0" width="100%" cellpadding="1" cellspacing="1">
            <tr>
                <td></td>

                <td style="width:80mm; font-weight:bold;  text-align:right;">Итого:</td>

                <td style="width:27mm; font-weight:bold;  text-align:right;">{{PrintPayment.sum | number}},00</td>
            </tr>

            <tr>
                <td></td>

                <td style="width:80mm; font-weight:bold;  text-align:right;">В том числе НДС:</td>

                <td style="width:27mm; font-weight:bold;  text-align:right;">0,00</td>
            </tr>

            <tr>
                <td></td>

                <td style="width:80mm; font-weight:bold;  text-align:right;">Всего к оплате:</td>

                <td style="width:27mm; font-weight:bold;  text-align:right;">{{PrintPayment.sum | number}},00</td>
            </tr>
        </table>

        <div style="font-size: 12px; margin-bottom: 8px;  border-bottom: 2px solid black; padding-bottom: 5px">
            Всего наименований 1, на сумму  {{PrintPayment.sum | number}},00 <ng-pluralize count="PrintPayment.sum" when="{
			'one'	: 'рубль',
			'few'	: 'рубля',
			'many'	: 'рублей',
		}"></ng-pluralize>.<br>
            <b class="m_title">{{numToText(PrintPayment.sum)}} <ng-pluralize count="PrintPayment.sum" when="{
			'one'	: 'рубль',
			'few'	: 'рубля',
			'many'	: 'рублей',
		}"></ng-pluralize> 00 копеек</b>
        </div>

        <div style="margin-top: 10px">
            <b style="font-size: 13px">ИП Эрдман К. А.</b>
            <span style="font-size: 12px; display: inline-block; width: 300px; border-bottom: 1px solid black">
            </span>
            
            <span style="float: right; font-size: 12px; display: inline-block; width: 200px; border-bottom: 1px solid black; text-align: center">
	            К. А. Эрдман
            </span>
        </div>
        
        
        
        
        
        <div style="margin: 15px 0">
	        <div style="border-top: 1px dashed gray"></div>
        </div>
        
        
        
        
        
        
        
        <center>
            <div style="width: 70%; font-size: 10px; margin-bottom: 10px">
                Внимание! Оплата данного счета означает согласие с условиями поставки товара. Уведомление об оплате обязательно, в противном случае не гарантируется наличие товара на складе. Товар отпускается по факту прихода денег на р/с Поставщика, самовывозом, при наличии доверенности и паспорта.
            </div>
        </center>

        <table width="100%" cellpadding="2" cellspacing="2" class="invoice_bank_rekv">
            <tr>
                <td colspan="2" rowspan="2" style="min-height:13mm; width: 105mm;">
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="height: 10mm;">
                        <tr>
                            <td valign="top">
                                <div>
                                    АО "АЛЬФА-БАНК"
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td valign="bottom" style="height: 3mm;">
                                <div style="font-size:10pt;">
                                    Банк получателя
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>

                <td style="min-height:7mm;height:auto; width: 25mm;">
                    <div>
                        БИK
                    </div>
                </td>

                <td rowspan="2" style="vertical-align: top; width: 60mm;">
                    <div style=" height: 7mm; line-height: 7mm; vertical-align: middle;">
                        044525593
                    </div>

                    <div>
                        30101810200000000593
                    </div>
                </td>
            </tr>

            <tr>
                <td style="width: 25mm;">
                    <div>
                        Сч. №
                    </div>
                </td>
            </tr>

            <tr>
                <td style="min-height:6mm; height:auto; width: 50mm;">
                    <div>
                        ИНН 622709802649
                    </div>
                </td>

                <td style="min-height:6mm; height:auto; width: 55mm;">
                    <div>
                        КПП <span style='display: inline-block; margin-left: 10px'>0</span>
                    </div>
                </td>

                <td rowspan="2" style="min-height:19mm; height:auto; vertical-align: top; width: 25mm;">
                    <div>
                        Сч. №
                    </div>
                </td>

                <td rowspan="2" style="min-height:19mm; height:auto; vertical-align: top; width: 60mm;">
                    <div>
                        40802810002390000312
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="2" style="min-height:13mm; height:auto;">
                    <table border="0" cellpadding="0" cellspacing="0" style="height: 10mm; width: 105mm;">
                        <tr>
                            <td valign="top">
                                <div>
                                    ИП Эрдман Константин Александрович
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td valign="bottom" style="height: 3mm;">
                                <div style="font-size: 10px;">
                                    Получатель
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table><br>

        <div style="font-weight: bold; font-size: 16px; padding-left:5px; margin-bottom: 5px; border-bottom: 2px solid black; padding-bottom: 5px">
            Счет на оплату №{{contracts[0].id}} от {{formatContractDate(PrintPayment.date)}}
        </div>

        <table width="100%">
            <tr>
                <td style="width: 30mm;">
                    <div style=" padding-left:2px;">
                        Поставщик:
                    </div>
                </td>

                <td>
                    <div style="font-weight:bold;  padding-left:2px;">
                        ИП Эрдман Константин Александрович, ИНН 622709802649, КПП 0, 390044, Россия, Рязанская обл., г.Рязань, ул. Костычева, д.11, кв.33, тел.: 8 (495) 646-85-92
                    </div>
                </td>
            </tr>

            <tr>
                <td style="width: 30mm; padding: 7px 0">
                    <div style=" padding-left:2px;">
                        Покупатель:
                    </div>
                </td>

                <td>
                    <div style="font-weight:bold; padding: 5px 0 5px 2px"></div>
                </td>
            </tr>
        </table>

        <table class="invoice_items" width="100%" cellpadding="2" cellspacing="2">
            <thead>
                <tr>
                    <th style="width:13mm;">№</th>

                    <th>Товары (работы, услуги)</th>

                    <th style="width:12mm;">Кол-во</th>

                    <th style="width:12mm;">Ед.</th>

                    <th style="width:20mm;">Цена</th>

                    <th style="width:27mm;">Сумма</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td align="center" style="font-size: 12px">1</td>

                    <td align="left" style="font-size: 12px">Предметно-консультационные услуги согласно договору №{{contracts[0].id}} от {{contracts[0].date}}г. Без НДС.</td>

                    <td align="right" style="font-size: 12px">{{objectLength(contracts[0].subjects)}}</td>

                    <td align="left" style="font-size: 12px">предмет</td>

                    <td align="right" style="font-size: 12px">{{(PrintPayment.sum / objectLength(contracts[0].subjects)) | number}},00</td>

                    <td align="right" style="font-size: 12px">{{PrintPayment.sum | number}},00</td>
                </tr>
            </tbody>
        </table>

        <table border="0" width="100%" cellpadding="1" cellspacing="1">
            <tr>
                <td></td>

                <td style="width:80mm; font-weight:bold;  text-align:right;">Итого:</td>

                <td style="width:27mm; font-weight:bold;  text-align:right;">{{PrintPayment.sum | number}},00</td>
            </tr>

            <tr>
                <td></td>

                <td style="width:80mm; font-weight:bold;  text-align:right;">В том числе НДС:</td>

                <td style="width:27mm; font-weight:bold;  text-align:right;">0,00</td>
            </tr>

            <tr>
                <td></td>

                <td style="width:80mm; font-weight:bold;  text-align:right;">Всего к оплате:</td>

                <td style="width:27mm; font-weight:bold;  text-align:right;">{{PrintPayment.sum | number}},00</td>
            </tr>
        </table>

        <div style="font-size: 12px; margin-bottom: 8px;  border-bottom: 2px solid black; padding-bottom: 5px">
            Всего наименований 1, на сумму  {{PrintPayment.sum | number}},00 <ng-pluralize count="PrintPayment.sum" when="{
			'one'	: 'рубль',
			'few'	: 'рубля',
			'many'	: 'рублей',
		}"></ng-pluralize>.<br>
            <b class="m_title">{{numToText(PrintPayment.sum)}} <ng-pluralize count="PrintPayment.sum" when="{
			'one'	: 'рубль',
			'few'	: 'рубля',
			'many'	: 'рублей',
		}"></ng-pluralize> 00 копеек</b>
        </div>

        <div style="margin-top: 10px">
            <b style="font-size: 13px">ИП Эрдман К. А.</b>
            <span style="font-size: 12px; display: inline-block; width: 300px; border-bottom: 1px solid black">
            </span>
            
            <span style="float: right; font-size: 12px; display: inline-block; width: 200px; border-bottom: 1px solid black; text-align: center">
	            К. А. Эрдман
            </span>
        </div>
        
        
    </div>