<?php if (file_exists("img/maps/branch_address/" . $Group->id_branch . ".jpg") || ($Group->id_branch == Branches::TRG)) :?>
	<div class='dotted-link' onclick="$('#how-to-get').slideToggle(300)">Как добраться?</div>
	<div id="how-to-get">
		<div class="row">
			<div class="col-sm-9" style="max-width: 830px">
				<div class="alert alert-danger" style="text-align: center">
					<span style="font-size: 21px; font-weight: bold">Распечатайте эту информацию перед первым занятием. Чтобы найти центр и кабинет в первый раз прийдите за <?=  ($Group->id_branch == Branches::TRG ? 15 : 40) ?> минут до начала занятия</span>
				</div>
				<?php if ($Group->id_branch != Branches::TRG) :?>
					<img src="img/maps/branch_address/<?= $Group->id_branch ?>.jpg">
				<?php endif ?>
				
				<?php if ($Group->id_branch == Branches::BRT) :?>
				<div style="margin: 15px 0">Выходите из северного вестибюля к кинотеатру "Солярис", огибаете ТЦ "Бум" справа и двигаетесь вдоль линии жилых домов как показано на карте. Направляйтесь к высокому рыжему зданию. Поднимаетесь по лестнице со двора к первому подъезду.</div>
				<img src="files/task/task_55fae50a4d16f7.06946892.jpg">
					<div style="margin: 15px 0">Заходите в главный вход. По лестнице (рядом с лифтом) поднимаетесь на 3 этаж, ищите кабинет с табличкой "ЕГЭ-Центр".</div>
				<?php endif ?>
				
				<?php if ($Group->id_branch == Branches::PER) :?>
				<div style="margin: 15px 0">Здание, в котором находится ЕГЭ-Центр-Перово находится с 50 метрах от метро. Точный адрес Зеленый проспект дом 20.</div>
				<img src="files/task/task_55fae24d1fe4f0.89276811.jpg">
					<div style="margin: 15px 0">На охране скажите, что идете в ЕГЭ-Центр и сообщите ФИО (пропуск не нужен). На лифте поднимаетесь на 3 этаж, затем найдите офис №9. Кабинет ЕГЭ-Центра - следующая дверь.</div>
				<?php endif ?>
				
				
				<?php if ($Group->id_branch == Branches::KLG) :?>
				<div style="margin: 15px 0">Выходите из северного вестибюля метро к ТЦ "Калужский" (большое оранжевое здание), идете к Хлебобулочному проезду, поворачиваете направо. Через 200 метров по правую руку увидите парк. Через парк по дорожке. Далее мимо шлагбаума (по левую руку идет стройка) до бизнес центра "На Научном" (точный адрес Научный проезд дом 8 строение 1)</div>
				<img src="files/task/task_55faf7630356c3.68968191.jpg">
					<div style="margin: 15px 0">На пункте охраны предъявите паспорт, возьмите одноразовый пропуск (магнитный пропуст готовится 1-2 недели). После турникета зайдите в дверь справа. Идете по коридору и в районе кабинета №111 спускаетесь по лестнице на цокольный этаж, после чего ищете кабинет №035.</div>
				<?php endif ?>
				
				<?php if ($Group->id_branch == Branches::PVN) :?>
				<div style="margin: 15px 0">Из метро двигайтесь в сторону "Детского мира". Вам нужно следующее административное здание (точный адрес Проспект Вернадского дом 37 корпус 2). Заходите в центральный вход.</div>
				<img src="files/task/task_55faf9b90889c0.34601226.jpg">
					<div style="margin: 15px 0">На ресепшн предьявляете паспорт, выписываете разовый пропуск в ЕГЭ-Центр (пока не готов постоянный), далее от стойки ресепшн направо по коридору до конца, затем налево. Дойдете до поста охраны, которая пропустит вас по выписаному пропуску. После турникета поднимаетесь на лифте на 4 этаж, из лифта налево по коридору до конца, еще раз налево и ищете кабинет №62.</div>
				<?php endif ?>
				
				
				<?php if ($Group->id_branch == Branches::MLD) :?>
				<div style="margin: 15px 0">После выхода из южного вестибюля идете по Ярцевской улице до улицы Партизанская и поворачиваете на перекрестке налево. После поворота примерно через минуту справа увидите шлагбаум и арка.</div>
				<img src="files/task/task_55fbc5449231d7.49564918.jpg">
					<div style="margin: 15px 0">Сразу за аркой слева вход в здание, в котором находится кабинет ЕГЭ-Центра.</div>
				<img src="files/task/task_55fbc547ed4d26.36212895.jpg">					
					<div style="margin: 15px 0">На охране сообщите, что идете в ЕГЭ-Центр. Поднимаетесь на 5 этаж по лестнице, ищете 506 кабинет. При себе обязательно иметь паспорт.</div>
				<?php endif ?>
				
				
				<?php if ($Group->id_branch == Branches::VKS) :?>
				<div style="margin: 15px 0">Первый вагон из центра. После турникетов направо. Далее идете как показано красными кружками на карте. Перед последним поворотом увидите слева белый забор с левой стороны. Двигайтесь вдоль него, чтобы он оставался по правую руку. Далее упретесь в шлагбаум. Сразу за ним - вход в голубое высокое здание - в нем находится ЕГЭ-центр.</div>
				<img src="files/task/task_55fc16f8008638.67427056.jpg">
					<div style="margin: 15px 0">На охране нужно сказать что вы идете в 704 кабинет на 7 этаже. Для доступа на этаж выдается пластиковая карта. За турникетом лифт, на этаже слева дверь с магнитным пропуском. Кабинет прямо слева.  При себе обязательно иметь паспорт.</div>
				<?php endif ?>
				
				
				<?php if ($Group->id_branch == Branches::TRG) :?>
				<div style="margin: 15px 0">Выходите из метро Тургеневская/Чистые Пруды/Сретенский бульвар, идите по направлению к Макдональдс. Затем двигайтесь по улице Мясницкая, чтобы Макдональдс оставался от вас по правую руку. Через 3 минуты увидите длинное 3-этажное желтое здание. Дойдите до конца, в торце вход в ЕГЭ-Центр. Далее поднимаетесь на 3 этаж и находите нужный кабинет.</div>
				<img src="files/task/task_55ffcdf91758c7.98013760.jpg" style="margin-bottom: 15px">
				<?php endif ?>
				
			</div>
		</div>
	</div>
<?php endif ?>