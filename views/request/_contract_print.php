<div id="contract-print-{{contract.id}}" class="printable">
	<h4 style="margin-bottom: 0">АГЕНТСКИЙ ДОГОВОР</h4>
	<h4 style="margin-top: 0">№ {{contract.id}}</h4>
	<div style="display: inline-block; width: 100%; margin-bottom: 20px">
		<span style="float: left">г. Москва</span> 
		<span style="float: right">{{contract.date}} г.</span>
	</div>



	<p> Индивидуальный предприниматель Капралов Константин Александрович, действующий на основании Свидетельства о государственной регистрации физического лица в качестве индивидуального предпринимателя серии 62 № 002030621,выданного межрайонной инспекцией Федеральной налоговой службы № 1 по г. Рязани 18 июня 2009 года, именуемый в дальнейшем «Агент», осуществляющий свою деятельность под коммерческим наименованием «ЕГЭ-Центр», в лице представителя {{contractPrintName(users[id_user_print], 'genitive')}}, действующего на основании Доверенности {{users[id_user_print].agreement}}, с одной стороны, и гр. {{contractPrintName(representative, 'instrumental')}} именуемый в дальнейшем «Принципал», с другой стороны, именуемые в дальнейшем «Стороны», заключили настоящий договор, в дальнейшем «Договор», о нижеследующем:</p>
	
	 
		<h4>1. ПРЕДМЕТ ДОГОВОРА</h4>
	
	
	<p>1.1. Агент обязуется совершить от своего имени и за счет Принципала действия по организации предметных консультаций, направленных на подготовку Принципала (лица, назначенного Принципалом - консультируемого) к сдаче {{(student.grade >= 10) ? "ЕГЭ" : "ОГЭ"}}, согласно следующим требованиям Принципала:</p>
	<p>- консультируемый — {{student.last_name}} {{student.first_name}} {{student.middle_name}};</p>
	<p>- предмет консультаций — <span ng-repeat="subject in contract.subjects">{{subject.name}}{{!$last ? ", " : ""}}</span>;</p>
	<p>- количество занятий, необходимых для подготовки — не менее {{subjectCount(contract)}}
		 <ng-pluralize count="subjectCount(contract)" when="{
			'one' 	: 'занятия',
			'few'	: 'занятий',
			'many'	: 'занятий',
		}"></ng-pluralize>;</p>
	<p>- общая стоимость организации подготовки — {{contract.sum | number}} ({{numToText(contract.sum)}})
		 <ng-pluralize count="contract.sum" when="{
			'one'	: 'рубль',
			'few'	: 'рубля',
			'many'	: 'рублей',
		}"></ng-pluralize></p>
	<p>1.2. Принципал обязуется оплатить действия Агента, предусмотренные п. 1.1. настоящего договора при его заключении, а также принять результат их осуществления по завершении срока действия договора (или ранее при его досрочном завершении) путем принятия (подписания) Акта сдачи-приемки выполненных работ (по форме приложения № 2).</p>
	
	
		<h4>2. СРОК ДЕЙСТВИЯ ДОГОВОРА</h4>
	
	
	<p>2.1. Настоящий договор вступает в силу с момента подписания его Сторонами и действует до полного выполнения Сторонами принятых на себя обязательств, определяемого датой подписания Акта сдачи-приемки выполненных работ.</p>
	<p>2.2. Агент обязан выполнять действия, предусмотренные в п. 1.1. настоящего договора до 15 июня 2016 года с момента подписания настоящего договора.</p>
	<p>2.3. Стороны вправе в любой момент отказаться от исполнения договора.
	В случае отказа Принципала от исполнения договора по причинам, не зависящим от действий Агента, он обязан возместить Агенту все фактически понесенные расходы к моменту прекращения договора.</p>
	<p>2.4. Условия настоящего договора применяются к отношениям Сторон по выполнению поручения о поиске Агентом преподавателя, соответствующего требованиям Принципала, указанным в Заявке принципала и ведению с ним переговоров, а также о поиске необходимого помещения для обеспечения консультационного процесса в сроки, порядке и на условиях, указанных в Заявке принципала, возникшим до заключения настоящего Договора.</p>
	<p>2.5. Окончание срока действия договора влечет прекращение обязательств сторон по нему.</p>
	<p style="page-break-after: always; margin-top: 30px"><b>Принципал</b> ___________________ / ______________________ /</p>
	
	
	
	
	
	
		<h4>3. ПРАВА И ОБЯЗАННОСТИ СТОРОН</h4>
	
	
	<p>3.1. Агент обязан:</p>
	<p>- осуществить поиск преподавателя, соответствующего требованиям Принципала, указанным в Заявке принципала и обеспечить осуществление консультационного процесса в сроки, порядке и на условиях, указанных в Заявке принципала;</p>
	<p>- осуществить поиск необходимого помещения для обеспечения консультационного процесса в сроки, порядке и на условиях, указанных в Заявке принципала, соответствующего обычно предъявляемым требованиям;</p>
	<p>- организовать взаимодействие между Принципалом (назначенным им лицом - консультируемым) и преподавателем, а также своевременное, полное и точное согласование необходимых изменений (корректировок) условий осуществления консультационного процесса в зависимости от текущих изменений конкретных условий, указанных в Заявке принципала;</p>
	<p>- исполнять данное ему поручение в соответствии с указаниями Принципала; указания Принципала должны быть правомерными, осуществимыми и конкретными;</p>
	<p>- сообщать Принципалу по его требованию все сведения о ходе исполнения поручения;</p>
	<p>- в срок не менее чем за две недели до начала консультационных занятий проинформировать Принципала о предстоящем графике посещений; при этом Принципал вправе в течение недели с момента получения соответствующего уведомления заявить о несогласии с предложенным графиком, с обоснованием конкретных причин, после чего Агент согласовывает изменение предложенного ранее графика в зависимости от наличия такой возможности, исходя из фактического наполнения групп;</p>
	<p>- по завершении консультационного процесса подготовить Акт сдачи-приемки выполненных работ и направить его Принципалу.</p>
	<p>3.2. Агент вправе:</p>
	<p>- при организации консультационного процесса, объединить консультируемых в группы по 4-10 человек, в соответствии с предметами (направлениями) и уровнем индивидуальной подготовки; при этом формирование групп и перевод консультирующихся из одной группы в другую, осуществляются Агентом самостоятельно, в зависимости от конкретной текущей ситуации, в части наполнения групп и интенсивности консультационных занятий;</p>
	<p>- изменить ранее определенное место проведения консультационных занятий, с обязательным заблаговременным надлежащим уведомлением Принципала (назначенного им лица);</p>
	<p>- заменить ранее определенного преподавателя в связи с внезапно возникшими обстоятельствами (например, обусловленные болезнью конкретного исполнителя); при этом замена может быть произведена при условии обеспечения необходимого уровня квалификационных требований к преподавателю;</p>
	<p>- изменить ранее определенное время и дату начала проведения консультационных занятий, в связи с недоукомплектованностью групп, с обязательным заблаговременным надлежащим уведомлением Принципала (назначенного им лица); соответствующее уведомление должно быть произведено не менее чем за 1 рабочий день;</p>
	<p>- увеличить, в случае необходимости, ранее определенное количество консультационных занятий, без дополнительной оплаты со стороны Принципала, с обязательным заблаговременным надлежащим уведомлением Принципала (назначенного им лица); под действие настоящего пункта могут подпадать, к примеру, случаи вынужденной замены преподавателя в группе, явная нехватка запланированных ранее часов консультаций для качественного освоения всего объема запланированной подготовки и т.п.</p>
	<p>- в случае опоздания консультируемого на очередное консультационное занятие без предварительного согласования с Агентом сроком не менее одного академического часа, не допустить к участию в занятии, без каких либо компенсаций.</p>
	<p>3.3. Принципал обязан:</p>
	<p>- при заключении настоящего договора уплатить Агенту обусловленное настоящим договором вознаграждение, в сумме и порядке, предусмотренные настоящим договором;</p>
	<p>- заблаговременно уведомить Агента о невозможности посещения ранее назначенного занятия в связи с болезнью или по иной уважительной причине, в электронном виде или иными средствами связи, с предоставлением подтверждающих документов;</p>
	<p>- заблаговременно уведомить Агента об изменении своих контактных данных (включая номера телефонов и адреса электронной почты), а также места жительства, указанных в настоящем Договоре; при этом риск несвоевременного уведомления и соответствующих негативных последствий, возлагается на Принципала;</p>
	<p>- своевременно уведомить Агента о наличии медицинских противопоказаний в отношении коллективных занятий с высокой контактностью;</p>
	<p>- без промедления принять Отчет Агента, все предоставленные им документы и все исполненное им в соответствии с договором, а также подписать Акт сдачи-приемки выполненных работ.</p>
	<p>3.4. Принципал вправе:</p>
	<p>- требовать от Агента выполнение обязательств, в части обеспечения надлежащего уровня квалификации привлекаемых для консультаций преподавателей, соответствующих условиям настоящего Договора;</p>
	<p>- требовать от Агента своевременного, полного и точного уведомления о текущих изменениях, ранее назначенных времени и места проведения консультационных занятий, а также о замене преподавателей;</p>
	<p>- в случае возникшей необходимости изменения графика или интенсивности занятий, обратиться к Агенту за переводом из одной группы в другую, с обоснованием конкретных причин; при этом Агент вправе согласовать такой переход в зависимости от наличия подобной возможности, исходя из фактического наполнения групп.</p>
	<p>3.5. После исполнения поручения Агент составляет и направляет в адрес Принципала Акт сдачи-приемки выполненных работ (по форме приложения № 2 к настоящему Договору).</p>
	<p>3.5.1. Акт сдачи-приемки выполненных работ составляется в письменной форме и подписывается лицом, уполномоченным действовать от имени Агента.</p>
	<p>3.5.2. Агент направляет Принципалу копию Акта сдачи-приемки выполненных работ по адресу электронной почты, указанный в разделе 10 настоящего Договора (с учетом возможных изменений, согласно уведомлениям Принципала).
	Оригинал Акта сдачи-приемки выполненных работ Агент, по требованию Принципала, направляет заказным письмом с уведомлением о вручении, по адресу Принципала, указанному в разделе 10 настоящего Договора (с учетом возможных изменений, согласно уведомлениям Принципала).</p>
	<p>3.5.3. Стороны Договора согласились с тем, что сообщение считается доставленным адресату и в том случае, если оно фактически не было получено по причинам, зависящим от адресата. Если Акт сдачи-приемки выполненных работ поступит в почтовое отделение, а Принципал не получит его по своей вине, документ будет считаться доставленным.</p>
	<p>3.5.4. Акт сдачи-приемки выполненных работ должен быть рассмотрен Принципалом в течение 10 рабочих дней со дня его получения по электронной почте. Возражения по Акту сдачи-приемки выполненных работ могут быть направлены Агенту по электронной почте, почтой или службой курьерской доставки.</p>
	<p>3.5.5. Акт сдачи-приемки выполненных работ считается принятым без возражений, если Агент не получит их в течение 10 рабочих дней со дня получения Акта сдачи-приемки выполненных работ Принципалом на адрес электронной почты, указанный в разделе 10 настоящего Договора (с учетом возможных изменений, согласно уведомлениям Принципала).</p>
	
		<h4 style="margin-top: 100px">4.	ПОРЯДОК РАСЧЕТОВ</h4>
		
	
	<p>4.1. За совершение порученных действий по настоящему договору Принципал выплачивает Агенту вознаграждение в размере {{contract.sum | number}} ({{numToText(contract.sum)}}) <ng-pluralize count="contract.sum" when="{
			'one'	: 'рубль',
			'few'	: 'рубля',
			'many'	: 'рублей',
		}"></ng-pluralize>, согласно расчету Агента, исходя из необходимого перечня предметов подготовки и количества занятий, необходимых по каждому из заявленных предметов. Принципал уплачивает Агенту вознаграждение в размере 50% , что составляет {{(contract.sum / 2) | number}} ({{numToText((contract.sum / 2))}}) <ng-pluralize count="(contract.sum / 2)" when="{
			'one'	: 'рубль',
			'few'	: 'рубля',
			'many'	: 'рублей',
		}"></ng-pluralize>. на условиях предоплаты непосредственно при заключении настоящего Договора;</p>
	<p>- принципал обязуется выплатить вторую часть суммы в размере {{(contract.sum / 2) | number}} ({{numToText((contract.sum / 2))}}) <ng-pluralize count="(contract.sum / 2)" when="{
			'one'	: 'рубль',
			'few'	: 'рубля',
			'many'	: 'рублей',
		}"></ng-pluralize>, до 15 января 2016 года.</p>
	
	<p>В связи с применением Агентом упрощенной системы налогообложения налог на добавленную стоимость не уплачивается.</p>
	<p>	4.2. Оплата по договору может производиться внесением наличных денежных средств в кассу Агента либо передаваться уполномоченному представителю Агента и оформляться путем оформления и выдачи приходного кассового ордера (в т.ч. с использованием бланков строгой отчетности) в соответствии с правилами ведения бухгалтерских операций и учета наличных денежных средств.
	</p>
	<p>	4.3. Расчеты по настоящему договору могут также осуществляться в безналичном порядке путем перечисления денежных средств на расчетный счет Агента на основании платежного поручения Принципала. Денежные средства перечисляются по реквизитам, указанным в разделе 10 настоящего Договора, если иные реквизиты не указаны в выставленном Агентом (уполномоченным представителем Агента) счете на оплату.
	</p>
	<p>	4.4. Стороны вправе изменить размер вознаграждения Агента в любой момент в течение срока действия договора по взаимному согласию.
	
	Соглашение об изменении настоящего договора заключается путем подписания Сторонами дополнительного соглашения к нему.</p>
	<p>	4.5. Возврат уплаченных Принципалом денежных средств по настоящему договору возможен лишь в случае расторжения (прекращения) договора, в связи с отказом Принципала от оплаченных занятий по уважительным причинам (таким как болезнь, длительное отсутствие в месте проведения занятий и т.п.) при своевременном предоставлении Агенту подтверждающих документов. Размер возвращаемых денежных средств определяется пропорционально количеству оставшихся оплаченных занятий, с учетом организационного сбора в размере не менее 10 % от общей стоимости организации подготовки, определенной п 1.1. настоящего Договора.
	</p>
	<p>	4.6. В случае расторжения (прекращения) договора, в связи с отказом Принципала от оплаченных занятий без указания уважительных причин и/или без предоставления необходимых подтверждающих документов, возврат ранее уплаченных денежных средств не производится.
	</p>
	<p>	4.7. В случае пропуска назначенных Агентом консультационных занятий без уважительных причин, соответствующая стоимость занятий Принципалу не возмещается.</p>
	
	
	
		<h4>5. ОТВЕТСТВЕННОСТЬ ПО НАСТОЯЩЕМУ ДОГОВОРУ</h4>
	
	
	<p>	5.1. В случае неисполнения или ненадлежащего исполнения одной из Сторон обязательств по настоящему договору Стороны несут ответственность в соответствии с действующим законодательством РФ.</p>
	
		<h4>6. ФОРС-МАЖОР</h4>
		
		
	<p>6.1. Стороны освобождаются от ответственности за частичное или полное неисполнение обязательств по настоящему договору, если это неисполнение явилось следствием возникших после заключения настоящего договора обстоятельств непреодолимой силы, которые стороны не могли предвидеть или предотвратить.</p>
	<p>6.2. При наступлении обстоятельств, указанных в п. 6.1 настоящего договора, каждая Сторона должна без промедления известить о них в письменном виде другую сторону. Извещение должно содержать данные о характере обстоятельств, а также официальные документы, удостоверяющие наличие этих обстоятельств и, по возможности, дающие оценку их влияния на исполнение стороной своих обязательств по данному договору.</p>
	<p>6.3. В случаях наступления обстоятельств, предусмотренных в п. 6.1 настоящего договора, срок выполнения стороной обязательств по настоящему договору отодвигается соразмерно времени, в течение которого действуют эти обстоятельства и их последствия.</p>
	<p>6.4. Если наступившие обстоятельства, перечисленные в п. 6.1 настоящего договора, и их последствия продолжают действовать более 3 месяцев, Стороны проводят дополнительные переговоры для выявления приемлемых альтернативных способов исполнения настоящего договора.</p>
	
	
		<h4>7. РАЗРЕШЕНИЕ СПОРОВ</h4>
	
	<p>7.1. Все споры и разногласия, которые могут возникнуть между Сторонами по вопросам, не нашедшим своего разрешения в тексте данного договора, будут разрешаться путем переговоров.</p>
	<p>7.2. При не урегулировании в процессе переговоров спорных вопросов споры разрешаются в порядке, установленном действующим законодательством.</p>
	
		<h4>8. ИЗМЕНЕНИЕ И ПРЕКРАЩЕНИЕ ДОГОВОР</h4>
	
	</p>
	<p>8.1. Настоящий договор может быть изменен или прекращен по письменному соглашению Сторон, а также в других случаяхи в порядке, предусмотренных законодательством и настоящим договором.</p>
	<p>8.2. Принципал вправе в любое время отказаться от исполнения настоящего договора путем направления письменного уведомления Агенту за 3 рабочих дня до даты предполагаемого прекращения договора. В случае отказа от настоящего договора Принципал обязан незамедлительно произвести выплату причитающегося Агенту вознаграждения за действия, совершенные им до прекращения Договора и возместить фактически понесенные им расходы в связи с исполнением поручения Принципала, в размере не менее 10 % от общей стоимости организации подготовки, определенной п. 1.1. настоящего Договора (организационный сбор).</p>
	<p>8.3. В случае грубого либо неоднократного нарушения Принципалом (назначенным им лицом - консультируемым) дисциплины или порядка проведения, установленных преподавателем при проведении консультационных занятий, а равно и правил внутреннего распорядка организации (учреждения) предоставивших помещение для проведения занятий, а также прав и законных интересов других слушателей группы, Агент вправе в любое время отказаться от исполнения настоящего договора путем направления письменного уведомления Принципалу за 3 рабочих дня до даты предполагаемого прекращения договора.</p>
	<p>8.4. В случае расторжения договора по инициативе Принципала, он обращается к Агенту с письменным заявлением о расторжении, с указанием конкретных причин (оснований) для расторжения и приложением соответствующих оправдательных документов.</p>
	<p>Агент обязан рассмотреть полученное заявление о расторжении в течение пяти рабочих дней с момента его получения и уведомить Принципала о принятом решении одним из способов, предусмотренных условиями настоящего Договора.</p>
	<p>В случае принятия решения о расторжении договора, Агент готовит к подписанию и предлагает Принципалу подписать Акт о расторжении договора, с изложением основных доводов для расторжения и обоснованием принятого решения, с указанием положений договора и норм действующего законодательства, послуживших основанием для принятия решения.</p>
	<p>Договор считается расторгнутым с момента подписания сторонами Акта о расторжении.</p>
	
	
		<h4>9. ЗАКЛЮЧИТЕЛЬНЫЕ ПОЛОЖЕНИЯ</h4>
	
	<p>9.1. Во всем остальном, что не предусмотрено настоящим договором, стороны руководствуются действующим законодательством Российской Федерации.</p>
	<p>9.2. Любые изменения и дополнения к настоящему договору действительны при условии, если они совершены в письменной форме и подписаны надлежаще уполномоченными на то представителями Сторон.</p>
	<p>9.3. Все уведомления и сообщения в рамках настоящего договора должны направляться Сторонами друг другу, а Агентом, также и на адреса (телефоны) консультируемого, в виде электронных сообщений (включая смс-сообщения) либо в письменной форме по адресам и телефонам, указанным в разделе 10 настоящего Договора (с учетом возможных изменений, согласно уведомлениям Принципала).</p>
	<p>9.4. Настоящий договор вступает в силу с момента его подписания Сторонами.</p>
	<p>9.5. Настоящий договор составлен в двух экземплярах, имеющих одинаковую юридическую силу, по одному экземпляру для каждой из Сторон.</p>
	
	
		<h4>10. ЮРИДИЧЕСКИЕ АДРЕСА РЕКВИЗИТЫ И ПОДПИСИ СТОРОН</h4>
	
	<p style="margin-bottom: 20px"><b>Агент: Индивидуальный предприниматель Капралов Константин Александрович</b>, Свидетельство о государственной регистрации физического лица в качестве индивидуального предпринимателя серии 62 № 002030621, выдано межрайонной инспекцией Федеральной налоговой службы № 1 по г. Рязани 18 июня 2009 года, паспорт серия 6108№ 524392, выдан: отделом УФМС России по Рязанской области в городе Рязани 24.11.2008 года, зарегистрирован по адресу: 390044 г. Рязань , ул. Костычева , д.11, кв 33. Телефон 89268289232, адрес для направления почтовой корреспонденции: : 390044 г. Рязань , ул. Костычева , д.11, кв 33., e:mail: kapralov.k@gmail.com</p>
	
	<p style="margin-bottom: 20px"><b>Принципал: {{representative.last_name}} {{representative.first_name}} {{representative.middle_name}}</b>, паспорт серия: {{representative.Passport.series}} № {{representative.Passport.number}} , выдан: {{representative.Passport.issued_by}}, дата выдачи {{representative.Passport.date_issued}},  зарегистрирован по адресу: {{representative.Passport.address}}, адрес для направления почтовой корреспонденции: {{representative.address}}{{representative.phone ? ", телефон: " + representative.phone : ""}}{{representative.phone2 ? ", " + representative.phone2 : ""}}{{representative.phone3 ? ", " + representative.phone3 : ""}}{{representative.email ? ", email: " + representative.email : ""}}
	</p>
	
	<p style="margin-bottom: 40px"><b>Консультируемый: {{student.last_name}} {{student.first_name}} {{student.middle_name}}</b>, паспорт серия: {{student.Passport.series}} № {{student.Passport.number}}{{student.phone ? ", телефон: " + student.phone : ""}}{{student.phone2 ? ", " + student.phone2 : ""}}{{student.phone3 ? ", " + student.phone3 : ""}}{{student.email ? ", email: " + student.email : ""}}
	</p>
	
	<p style="margin-bottom: 40px"><b>Агент</b> __________________ / 
		{{users[id_user_print].last_name}} {{users[id_user_print].first_name[0]}}. {{users[id_user_print].middle_name[0]}}. /</p>
	
	<p><b>Принципал</b> ___________________ / ______________________ /</p>
</div>