<div id="contract-licenced-print-{{contract.id}}" class="printable printable-contract">

	 <style type="text/css">
    .ng-hide {
	    display: none !important;
    }
	p {
		text-align: justify !important;
	}
	ul {
		margin: 0;
        -webkit-padding-start: 50px;
	}
    </style>

	<h4 style="margin-bottom: 0">Договор №{{contract.id}}</h4>
    <h4 style="margin-top: 0">на оказание платных образовательных услуг</h4>
    
    <div style="display: inline-block; width: 100%; margin-bottom: 20px">
		<span style="float: left">г. Москва</span>
		<span style="float: right">{{contract.date}} г.</span>
	</div>
 
	<p><b>Общество с ограниченной ответственностью «ЕГЭ-ЦЕНТР»</b>, в лице Генерального директора Капралова Константина Александровича, действующего на основании Устава, именуемое в дальнейшем «Исполнитель», «ЕГЭ-Центр», на основании лицензии на осуществление образовательной деятельности № 037742, выданной Департаментом образования города Москвы 08.08.2016 г., с одной стороны, и
{{contractPrintName(representative, 'nominative')}}, именуемый(ая) в дальнейшем «Заказчик», являющийся(щаяся) родителем (законным представителем) {{contractPrintName(student, 'genitive')}}, {{ student.Passport.date_birthday }} года рождения, именуемого(ой) в дальнейшем «Обучающийся», с другой стороны, совместно именуемые «Стороны», заключили настоящий Договор на оказание платных образовательных услуг (далее – «Договор») о нижеследующем:</p>
<h4>1. Предмет Договора.</h4>
<p>1.1. По настоящему Договору Исполнитель обязуется зачислить Обучающегося на обучение и оказать Обучающемуся образовательные услуги по программе дополнительного обучения по курсу (курсам): «<span ng-repeat="subject in contract.subjects">{{SubjectsFull2[subject.id_subject]}}{{!$last ? ", " : ""}}</span>» (далее – «Услуги»), а Заказчик обязуется оплатить указанные Услуги в порядке и на условиях, которые установлены настоящим Договором.</p>
<p>1.2. Обучение осуществляется в порядке, установленном локальными нормативными актами Исполнителя и настоящим Договором.</p>
<p>1.3. Продолжительность образовательной программы по программе курса
	<span ng-repeat="program in contract.subjects">
			«{{SubjectsFull2[program.id_subject]  | lowercase }}-{{program.count + program.count2}}» ({{(program.count + program.count2)*4.5}}
			<ng-pluralize count="(program.count + program.count2)*4.5" when="{'one' : 'час', 'few' : 'часа', 'many' : 'часов'}"></ng-pluralize>)
			{{$last ? '.' : ','}}
	</span>
	 Форма обучения – очная. </p>
<p>1.4. Место оказания Услуг по Договору: г. Москва, ул. Мясницкая, дом 40, стр. 1, 3 этаж.</p>
<p>1.5. Вид образования: дополнительное образование детей и взрослых, продолжительность обучения: {{ week_count(contract.subjects) }} <ng-pluralize count="week_count(contract.subjects)" when="{'one' 	: 'неделя', 'few'	: 'недели', 'many'	: 'недель'}"></ng-pluralize>. </p>

<h4>2. Права и обязанности Сторон.</h4>

<p><b>2.1. Исполнитель обязуется:</b></p>
	<p>2.1.1. Предоставить Заказчику и Обучающемуся доступ к личному кабинету. Логин для доступа в личный кабинет <?= $Request->Student->login ?>, пароль <?= $Request->Student->password ?>. Указанные данные известны только сторонам договора. </p>

	<p>2.1.2. Выполнять принятые на себя обязательства по оказанию Услуг Обучающемуся качественно и в сроки, согласованные с Заказчиком в соответствии с требованиями, изложенными в настоящем Договоре и дополнительных соглашениях к нему. </p>
<p>2.1.3. Зачислить Обучающегося на обучение после оплаты Заказчиком стоимости услуг Исполнителя и представления следующих документов:</p>
    <ul style="margin: 0">
        <li>Копия свидетельства о рождении или паспорта Обучающегося;</li>
        <li>Копия паспорта Заказчика;</li>
        <li>Информация о контактных данных (номер телефона, адрес фактического проживания Обучающегося и Заказчика);</li>
        <li>Информация об адресе или номере школы Обучающегося;</li>
        <li>Заявление о приеме на обучение;</li>
        <li>Фото в формате jpeg.</li>
    </ul>
<p>2.1.4. Создать Обучающемуся необходимые условия для освоения выбранной образовательной программы, а именно, предоставить каждому Обучающемуся учебное посадочное место размером не менее 70 см. в каждом помещении (классе), где проводятся занятия, обеспечить наличие досок, проекторов по требованию преподавателя в части учебных классах, ноутбуков с необходимым для обучения программным обеспечением в классе информатики.</p>
<p>2.1.5. Размещать расписание занятий (с точным указанием времени) не позднее, чем за 1 день до начала обучения на стенде Исполнителя в коридоре по месту оказания услуг, а также в личном кабинете Обучающегося.</p>
<p>2.1.6. Обеспечивать контроль знаний Обучающегося в порядке, предусмотренном  программой обучения. </p>
<p>2.1.7. Предоставить возможность Заказчику, в том числе до момента зачисления, ознакомиться с локальными актами ЕГЭ-Центра, а также информацией и документами ЕГЭ-Центра согласно указаниям Закона «Об Образовании в РФ».</p>
<p>2.1.8. Обеспечивать охрану жизни и безопасность здоровья, создавать безопасные условия обучения и содержания Обучающегося в ЕГЭ-Центре в соответствии с установленными нормами, обеспечивающими его жизнь и здоровье.</p>
<p><b>2.2. Исполнитель вправе:</b></p>
<p>2.2.1. Самостоятельно осуществлять образовательный процесс, выбирать системы оценивания и формы, порядок и периодичность промежуточной и итоговой аттестации (если требуется образовательной программой), определять содержание образовательных программ, самостоятельно выбирать, разрабатывать и применять методики преподавания,  технологии.</p>
<p>2.2.2. При необходимости в одностороннем порядке смещать даты начала или окончания обучения на период не более 30 (тридцать) дней, назначать внеплановые занятия в рамках обычного расписания, при условии предварительного уведомления Заказчика любым из указанных способов либо из сочетанием: запись в личном кабинете, смс или телефонный звонок на номер телефона Заказчика, указанный в настоящем Договоре.</p>
<p>2.2.3. В случае необходимости Исполнитель вправе изменять расписание занятий и заменять указанных в нем, специалистов, как до начала обучения, так и в его процессе, с внесением изменений в расписание не позднее, чем за 1 день. При этом Исполнитель не несет ответственности за указанные обстоятельства, а также за задержку начала занятий на срок не более 15 минут, возникающие в процессе обучения по вине Исполнителя. В случае задержки проведения занятия более чем на 15 минут по вине Исполнителя, Исполнитель обязуется провести в конце учебного периода занятие по соответствующему предмету или несколько занятий, руководствуясь суммарным количеством минут задержки.</p>
<p>2.2.4. Для организации образовательного процесса привлекать по своему усмотрению третьих лиц.</p>
<p>2.2.5. Применять к Обучающемуся меры дисциплинарного взыскания в соответствии с законодательством РФ и локальными актами Исполнителя. </p>
<p>2.2.6. Восстановить Обучающегося на обучение после отчисления при условии, что на момент восстановления Исполнитель продолжает набор групп на обучение по выбранной в заявке Заказчиком программе, а также при условии оплаты Заказчиком стоимости обучения (разницы в стоимости) с учетом цен, действующих в период восстановления. Порядок восстановления определяется внутренними локальными нормативными актами Исполнителя.</p>
<p>2.2.7. Если Обучающийся представляет непосредственную опасность для себя, других Обучающихся, преподавателей, сотрудников, Исполнитель имеет право приостановить исполнение Договора немедленно, с уведомлением Заказчика в течение 1 (Одного) рабочего дня с момента возникновения вышеуказанных обстоятельств.
	Стоимость пропущенных занятий при этом не компенсируется.
В том числе, но, не ограничиваясь, при поступлении информации о заразной болезни Обучающегося, которая может препятствовать образовательному процессу для других обучающихся – не допускать такого Обучающегося до занятий, а также, требовать справки об окончании болезни Обучающегося. При этом стоимость пропущенных по причине болезни занятий не возмещается.</p>
<p>2.2.8. На основании п.7 ст. 54 Закона «Об образовании в РФ» в случае просрочки надлежащей оплаты услуг по Договору более, чем на 14 (четырнадцать) календарных дней, Исполнитель вправе в одностороннем порядке не допустить Обучающегося к занятиям, отказаться от исполнения Договора, расторгнуть его во внесудебном порядке.</p>
<p><b>2.3. Заказчик вправе:</b></p>
<p>2.3.1. Требовать надлежащего исполнения Исполнителем своих обязательств по организации процесса обучения, в том числе предоставления информации о расписании занятий.</p>
<p>2.3.2. Обращаться к работникам Исполнителя по вопросам, касающимся исполнения обязательств Заказчика по Договору. </p>
<p><b>2.4. Заказчик обязуется:</b></p>
<p>2.4.1. Своевременно вносить плату за оказываемые Исполнителем в соответствии с настоящим Договором услуги.</p>
<p>2.4.2. При заключении настоящего Договора предоставить Исполнителю оригиналы или надлежащим образом заверенные копии документов, перечисленных в п. 2.1.3. Договора. </p>
<p>2.4.3. Предоставить Исполнителю согласие на обработку своих персональных данных и персональных данных Обучающегося.</p>
<p>2.4.4. Заказчик не вправе требовать от Исполнителя возврата или перерасчета оплаченных денежных средств за Услуги в случае пропуска/опоздания Обучающегося на занятия, а также другого вида замещения занятия, независимо от причины пропуска, в том числе, по основаниям п. 2.2.7. </p>
<p>2.4.5. Незамедлительно сообщать Исполнителю об изменении контактного телефона и места жительства.</p>
<p>2.4.6. Обеспечить посещение Обучающимся ЕГЭ-Центра согласно правилам внутреннего распорядка Исполнителя.</p>
<p>2.4.7. Предоставлять справку об окончании болезни по требованию Исполнителя.</p>
<p>2.4.8. Бережно относиться к имуществу Исполнителя, возмещать ущерб, причиненный Обучающимся имуществу Исполнителя, в соответствии с законодательством Российской Федерации. Утрата имущества Исполнителя подлежит возмещению со стороны Заказчика в случае причинения материального ущерба на сумму более 1000 (одна тысяча) рублей.</p>
<p>2.4.9. Отслеживать состояние личного кабинета на сайте Исполнителя, с периодичностью не менее, чем один раз в месяц. </p>
 </p>
<h4>3. Размер и порядок оплаты Услуг.</h4>
<p>3.1. Общая стоимость Услуг Исполнителя по Договору складывается из стоимости отдельных занятий, приобретаемых на момент заключения Договора, и составляет {{contract.sum | number}} (<span class="m_title">{{numToText(contract.sum)}}</span>)
		 <ng-pluralize count="contract.sum" when="{
			'one'	: 'рубль',
			'few'	: 'рубля',
			'many'	: 'рублей',
		}"></ng-pluralize>.</p>
<p>3.2. Стоимость занятий с 1 по 64 составляет 1600 (одна тысяча пятьсот) рублей за одно занятие, стоимость занятий с 65 по 96 составляет 1500 (одна тысяча четыреста) рублей за одно занятие, стоимость занятий с 97 и всех последующих составляет 1400 (одна тысяча триста) рублей.</p>
<p>3.3. В случае изменения Договора в связи с уменьшением или увеличением количества необходимых Заказчику занятий цена Договора пересчитывается в соответствии со стоимостью занятий по их порядковому номеру.</p>
<p>3.4. Оплата Услуг по настоящему Договору  производится Заказчиком следующим образом:</p>
<ul>
<li>50 % суммы, указанной в п.3.1. вносится в момент заключения Договора;</li>
<li>оставшиеся 50  % суммы, указанной в п.3.1. вносятся до 15 января 2017 года</li>
</ul>
<p>В случае нарушения Заказчиком обязанностей по оплате услуг Исполнителя, согласованных в графике платежей, Исполнитель вправе применить согласованные Сторонами в Договоре меры ответственности за просрочку оплаты. </p>
<p>3.5. Расчеты по настоящему Договору осуществляются в наличной или безналичной форме, в рублях Российской Федерации. Датой платежа считается дата поступления денежных средств на соответствующий расчетный счет Исполнителя или дата оплаты денежной суммы Заказчиком в кассу Исполнителя.</p>
<p>3.6. Услуги Исполнителя не облагаются НДС.</p>
<p>3.7. К отношениям сторон не применяются нормы ст. 317.1 ГК РФ. </p>
</p>
<h4>4. Согласие на обработку персональных данных.</h4>
<p>4.1. Заказчик, подписывая настоящий договор, выражает согласие на обработку Исполнителем персональных данных Заказчика и Обучающегося, предоставленных Исполнителю, в том числе, на сбор, систематизацию, накопление, хранение, уточнение (обновление, изменение), использование, обезличивание, блокирование, уничтожение персональных данных. Исполнитель вправе приступить к обработке персональных данных Заказчика (Обучающегося) только после получения подтверждения наличия такого согласия.</p>
<p>4.2. Обработка персональных данных Заказчика (Обучающегося) осуществляется Исполнителем в соответствии с законодательством Российской Федерации, как в электронном, так и документированном виде. </p>
<p>4.3. Перечень передаваемых Исполнителю на обработку персональных данных Заказчика, Обучающегося: </p>
<p>4.3.1. При заключении Договора Заказчик предоставляет следующие персональные данные Заказчика и Обучающегося:</p>
<ul style="font-style: italic" style="margin: 0">
<li>фамилия, имя, отчество;</li>
<li>адрес электронной почты;</li>
<li>фотография Обучающегося;</li>
<li>номер телефона;</li>
<li>дата рождения;</li>
<li>адрес проживания и (или) регистрации по месту жительства;</li>
<li>паспортные данные Заказчика и Обучающегося, данные свидетельства о рождении Обучающегося в случае отсутствия паспорта.</li>
<li>адрес школы Обучающегося или ее номер.</li>
</ul>
<p>4.3.2. Заказчик дает согласие на видео и фотосъемку Обучающегося, организуемую Исполнителем в процессе обучения. Видео и фотосъемка производятся во время одного из занятий. Видео и фотосъемка могут продолжаться в течение не более чем одного академического часа.
Исполнитель вправе в целях исполнения обязательств, предусмотренных настоящим Договором и законом запросить предоставление иных сведений или копии документов.</p>
<p>4.4. Целью обработки персональных данных Заказчика и Обучающегося является исполнение Сторонами обязательств по настоящему Договору, в том числе, обеспечение организации учебного процесса, ведение бухгалтерского учета, сбор и обработка статистических данных, популяризация оказываемых Исполнителем образовательных услуг, контроль качества оказания услуг, выполнение требований законодательства Российской Федерации об образовании и пр.
Исполнитель обязуется использовать полученные персональные данные Заказчика исключительно в заявленных целях в соответствии с действующим законодательством Российской Федерации.
Исполнитель имеет право размещать переданные ему персональные данные на собственном сайте, в том числе, но, не ограничиваясь, для организации работы личного кабинета Обучающегося, организации системы контроля качества исполнения услуг. </p>
<p>4.5. В соответствии с п. 4 ст. 14 Федерального закона от 27.07.2006 года № 152–ФЗ «О персональных данных», Заказчик имеет право на получение информации, касающейся обработки его персональных данных. Заказчик вправе отозвать согласие на обработку персональных данных, с учетом того, что риск дальнейшей невозможности оказания услуг по настоящему Договору в этом случае несет Заказчик.</p>
Согласие на обработку моих персональных данных и персональных данных Обучающегося  предоставляю: ___________________________________________________________________ </p>
</p>
<h4>5. Порядок приемки и сдачи Услуг.</h4>
<p>5.1. В течение 3 (Трех) рабочих дней с даты проведения последнего занятия согласно расписанию, Заказчик обязуется подписать Акт об оказанных услугах (далее – «Акт») по форме, установленной в Приложении №1 к настоящему Договору по месту нахождения Исполнителя. </p>
<p>5.2. В случае мотивированного отказа Заказчика от подписания Акта Сторонами составляется двухсторонний акт с перечнем разногласий  и сроков их исполнения.</p>
<p>5.3. В случае, если в соответствии с п. 5.1. настоящего Договора, Заказчик не явится для подписания Акта и не представит мотивированный отказ от приемки оказанных Услуг, обязательство Исполнителя по оказанию Услуг будет считаться исполненным в полном объеме, а Услуги, оказанные по настоящему Договору, принятыми Заказчиком в полном объеме и без оговорок.</p>
</p>
<h4 style="page-break-before: always; margin-top: 0">6. Порядок расторжения Договора.</h4>
<p>6.1. Договор прекращает свое действие по следующим основаниям:</p>
<ul>
<li>по окончании срока действия настоящего Договора;</li>
<li>после завершения обучения;</li>
<li>в случае отчисления Обучающегося;</li>
<li>по соглашению Сторон;</li>
<li>по инициативе одной из Сторон при условии уведомления второй Стороны не позднее, чем за 10 (десять) рабочих дней;</li>
<li>по инициативе одной из Сторон в случае существенного нарушения второй Стороной условий Договора, локальных актов Исполнителя со дня, следующего за днем уведомления о нарушении;</li>
<li>по иным основаниям, прямо вытекающим из условий настоящего Договора.</li>
</ul>
<p>6.2. Каждая из Сторон вправе в одностороннем порядке отказаться от исполнения Договора. </p>
<p>Если Исполнитель отказывается от исполнения Договора, то он возвращает Заказчику все полученные от него в порядке предварительной оплаты на основании соответствующей заявки на обучение денежные средства, за исключением стоимости фактически оказанных услуг. </p>
<p>Если Заказчик отказывается от исполнения Договора, то он оплачивает оказанные услуги (проведенные занятия) и фактически понесенные расходы Исполнителя (сумма, пропорциональная объему образовательной программы, который был надлежащим образом обеспечен Исполнителем к моменту отказа от договора). </p>
<p>Односторонний отказ от исполнения Договора любой из Сторон по основаниям, указанным в настоящем Договоре, влечет за собой его прекращение во внесудебном порядке. </p>
<p>6.3. Существенными считаются, в том числе, следующие нарушения со стороны Заказчика: </p>
<p>6.3.1. Просрочка надлежащей оплаты услуг по Договору более, чем на 14 (четырнадцать) календарных дней. Исполнитель вправе при этом в одностороннем порядке не допустить Обучающегося к занятиям, отказаться от исполнения Договора, расторгнуть его во внесудебном порядке.</p>
<p>6.3.2. Если надлежащее исполнение обязательств по Договору стало невозможным вследствие действий (бездействия) Обучающегося или Заказчика, в т.ч.:</p>
<ul>
	<li>при систематическом нарушении дисциплины (более 1 раза в течение календарного месяца);</li>
	<li>при действиях со стороны Обучающегося, в результате которых создается угроза для физического и психологического здоровья и благополучия остальных Обучающихся, педагогов и иных работников Исполнителя;</li>
	<li>при действиях со стороны Обучающегося, в результате которых создаются препятствия для образовательного процесса, в т.ч. для остальных обучающихся;</li>
	<li>при демонстративном общем неуважении к окружающим, проявлении расизма и национализма, склонности к созданию агрессивных группировок;</li>
	<li>вследствие состояния здоровья Обучающегося, не позволяющего ему систематически посещать занятия; </li>
	<li>при грубом нарушении локальных актов Исполнителя, которые распространяются на Обучающегося и на Заказчика и с которыми последний был ознакомлен;</li>
	<li>установления нарушения порядка приема в образовательную организацию, повлекшего по вине Заказчика незаконное зачисление Обучающегося в группу обучения.</li>
</ul>
<p>6.4. Прекращение настоящего Договора по любым основаниям влечет за собой отчисление Обучающегося с соблюдением требований настоящего Договора и действующего законодательства.</p>
</p>
<h4>7. Форс-мажор.</h4>
<p>7.1. Стороны освобождаются от ответственности за частичное или полное неисполнение обязательств по настоящему Договору, если это невыполнение было вызвано наступлением обстоятельств непреодолимой силы (форс-мажор). Под обстоятельствами непреодолимой силы понимаются обстоятельства, которые Стороны не смогли ни предвидеть, ни предотвратить обычным путем. К таким обстоятельствам непреодолимой силы относятся наводнения, землетрясения и другие явления природы, а также военные действия, пожары, массовые беспорядки, революции, вступление в действие законодательных актов, правительственных постановлений и распоряжений государственных органов, прямо или косвенно запрещающих указанные в Договоре виды деятельности, препятствующие осуществлению Сторонами своих обязательств по Договору.</p>
<p>7.2.  В случае возникновения обстоятельств непреодолимой силы во время исполнения Сторонами своих обязательств по настоящему Договору, срок действия Договора продлевается на время действия вышеуказанных обстоятельств.</p>
<p>7.3.  Если указанные обстоятельства продолжаются более 1 (Одного) месяца, каждая Сторона имеет право на досрочное расторжение Договора. В этом случае Стороны подписывают соглашение о расторжении Договора и производят взаиморасчеты.</p>
</p>
<h4>8. Ответственность сторон.</h4>
<p>8.1. За неисполнение или ненадлежащее выполнение обязательств по настоящему Договору Стороны несут ответственность в соответствии с действующим законодательством Российской Федерации.</p>
<p>8.2. В случае, если Услуги не были оказаны (не состоялись занятия) по вине Исполнителя, то Исполнитель назначает дополнительно не проведенные занятия для обеспечения полного исполнения обязательств по Договору. </p>
<p>8.3. Если назначенные Исполнителем в соответствии с п. 2.2.2. внеплановые занятия совпали по времени с любыми другими занятиями, назначенными Обучающимся в Центре и Обучающийся смог посетить только одно из них, Исполнитель возвращает Заказчику стоимость фактически не оказанной услуги. </p>
<p>8.4. Заказчик несет ответственность за правильность и достоверность передаваемых им Исполнителю исходных данных. В случае использования недостоверных исходных данных, полученных от Заказчика, Исполнитель не несет ответственности за качество оказанных Услуг на основании недостоверных данных.</p>
<p>8.5. Настоящим Заказчик признает, что материалы, переданные  ему в рамках оказания Услуг Исполнителем, являются объектом интеллектуальной собственности, права на которые принадлежат Исполнителю. Исполнитель передает материалы Заказчику только с целью оказания Услуг Исполнителем в соответствии с настоящим Договором. Использование материалов Заказчиком разрешено только в объеме, необходимом для оказания Исполнителем Услуг. Заказчик не вправе использовать материалы, переданные Исполнителем, в иных целях, не указанных в настоящем пункте Договора, без письменного согласия Исполнителя. Заказчик несет ответственность за нарушение данного пункта в соответствии с законодательством Российской Федерации.</p>
<p>8.6. Исполнитель несет ответственность в установленном законодательством Российской Федерации порядке за невыполнение или ненадлежащее выполнение функций, отнесенных к его компетенции, за реализацию не в полном объеме образовательных программ в соответствии с учебным планом. За нарушение или незаконное ограничение права на образование и предусмотренных законодательством об образовании прав и свобод обучающихся, нарушение требований к организации и осуществлению образовательной деятельности Исполнитель несет ответственность в соответствии с действующим законодательством РФ</p>
<h4>9. Конфиденциальность.</h4>
<p>9.1. Исполнитель обязан предпринять со своей стороны все возможные действия для обеспечения неразглашения сведений, ставших известными в ходе выполнения настоящего Договора его работникам и являющихся информацией конфиденциального характера по отношению к Заказчику во  время действия настоящего Договора.</p>
<p>9.2. Заказчик обязан не разглашать в той либо иной форме сведения конфиденциального характера, сведения, составляющих коммерческую тайну Исполнителя, а также положения настоящего Договора третьим лицам.</p>
</p>
<h4 style="margin-top: 0">10. Порядок разрешения споров.</h4>
<p>Стороны будут прилагать все усилия к тому, чтобы решать возникающие разногласия и путем переговоров. В случае возникновения разногласий и споров, связанных с исполнением настоящего Договора, Стороны принимают усилия по их урегулированию путем переговоров. Все претензии подаются только в письменной форме и должны быть рассмотрены в течение 30 (тридцати) дней. Если Стороны не достигнут компромисса, спор подлежит разрешению в судебном порядке в соответствии с правилами процессуального законодательства РФ.</p>
<h4>11. Прочие условия.</h4>
<p>11.1. Настоящий Договор составлен в двух одинаковых экземплярах, имеющих равную юридическую силу, по одному для каждой из Сторон. Все приложения и Дополнительные соглашения являются неотъемлемой частью настоящего Договора.</p>
<p>11.2.  Настоящий Договор вступает в силу с даты, его подписания, заключен на срок до 01 июля 2017 года. </p>
<p>11.3. Изменения и дополнения в настоящий Договор вносятся только по соглашению Сторон в письменной форме. Изменения и дополнения оформляются в виде дополнительного соглашения, которое является неотъемлемой частью Договора. Стороны признают надлежаще оформленными настоящий Договор и все документы, составленные в соответствии с ним, при факсимильном воспроизведении подписи Исполнителя. </p>
<p>11.4. Подписание настоящего Договора является подтверждением того, что Заказчик ознакомлен с локальными актами и иными документами и информацией  Исполнителя, размещенными на сайте в сети Интернет http://www.ege-centr.ru/ в соответствии с требованиями ФЗ "Об образовании".</p>
<p>11.5. Приложение №1 является неотъемлемой частью Договора. </p>

<h4>12. Адреса и реквизиты сторон.</h4>


<div style="display: inline-block; width: 100%">
			<div style="width: 50%; float: left;">
				<b>ИСПОЛНИТЕЛЬ:</b><br>
				Общество с ограниченной ответственностью<br>
				«ЕГЭ-Центр»<br>
				ОГРН 1167746382319 от 15.04.2016<br>
				ИНН 9701038111 101000<br>
				г. Москва, ул. Мясницкая, д. 40, стр. 1, 3 эт., к. 7<br>
				Р/сч: 40702 810 8019 6000 0153<br>
				АО «Альфа-Банк»<br>
				к/сч 30101810200000000593<br>
				БИК 044525593<br>
				Тел.: +7(495)646 85 92<br>
				e-mail: info@ege-centr.ru<br>
			</div>
			<div style="width: 50%; float: right;">
				<b>ЗАКАЗЧИК:</b><br>
				{{representative.last_name}} {{representative.first_name}} {{representative.middle_name}}<br>
				Паспорт {{representative.Passport.series}} номер {{representative.Passport.number}}<br>
				Выдан: {{representative.Passport.issued_by}}, {{representative.Passport.date_issued}} <br>
				Код подразделения: {{representative.Passport.code}}<br>
				Зарегистрирован по адресу: {{representative.Passport.address}}<br>
				Факт. адрес: {{representative.address}}<br>
				{{representative.phone ? "Тел.: " + representative.phone : ""}}{{representative.phone2 ? ", " + representative.phone2 : ""}}{{representative.phone3 ? ", " + representative.phone3 : ""}}<br>
				{{representative.email ? "e-mail: " + representative.email : ""}}<br>
			</div>
		</div>
		
		<div style='margin: 50px 0 0 0'>
			<div style="display: inline-block; float: left; width: 50%">
				Генеральный директор  ООО «ЕГЭ-Центр»<br>
					Капралов К. А.
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
<div style="page-break-before: always">
	<div style="text-align: right">
	Приложение № 1<br>
к Договору оказания платных<br>
образовательных услуг<br>
№{{contract.id}} от {{contract.date}}
	</div>
	
	<h4>
 АКТ ОБ ОКАЗАННЫХ УСЛУГАХ<br>
ПО ДОГОВОРУ №{{contract.id}}<br>
№{{contract.id}} от {{contract.date}}
	</h4>

<div style="display: inline-block; width: 100%; margin-bottom: 20px">
	<span style="float: left">г. Москва</span>
	<span style="float: right">«____»_______ 20____ г.</span>
</div>

Мы, нижеподписавшиеся:<br><br>

От имени Заказчика: __________________, и от имени Исполнителя _______________________ ______________________, действующий (-ая) на основании ___________________, составили акт о том, что в соответствии с обязательствами, предусмотренными Договором от «___» ______ 20____ г. № ________  Исполнитель оказал Заказчику в полном объеме услуги на сумму: ______ (_______) рублей _____ копеек. НДС не облагается.
 <br><br>
Заказчик претензий к Исполнителю не имеет.
 <br><br>
Настоящий акт составлен в двух экземплярах, по одному для каждой Стороны.

	<div style='margin: 50px 0 0 0'>
			<div style="display: inline-block; float: left; width: 50%">
				Генеральный директор  ООО «ЕГЭ-Центр»<br>
					Капралов К. А.
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



</div>
