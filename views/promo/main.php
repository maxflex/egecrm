<div ng-app="Promo" ng-controller="MainCtrl" ng-init="<?= $ang_init_data ?>">
	<h4 class="center" style="font-weight: bold">Приглашайте в ЕГЭ-Центр учиться своих друзей<br>
		и забирайте призы в 317 кабинете на следующий день!</h4>
	
	<div class="promo-images">
		<div>
			<span>за 1 ученика</span>
			<img src="img/promo/ipod.png">
			<label>iPod 7 nano</label>
			<a href="http://www.apple.com/ru/ipod-nano/specs/" target="_blank">подробнее</a>
		</div>
		<div>
			<span>за 2 или 3 учеников</span>
			<img src="img/promo/iwatch.png">
			<label>iWatch Sport</label>
			<a href="http://www.apple.com/ru/shop/buy-watch/apple-watch-sport/%D0%9A%D0%BE%D1%80%D0%BF%D1%83%D1%81-38-%D0%BC%D0%BC-%D0%B8%D0%B7-%D1%81%D0%B5%D1%80%D0%B5%D0%B1%D1%80%D0%B8%D1%81%D1%82%D0%BE%D0%B3%D0%BE-%D0%B0%D0%BB%D1%8E%D0%BC%D0%B8%D0%BD%D0%B8%D1%8F-%D0%B1%D0%B5%D0%BB%D1%8B%D0%B9-%D1%81%D0%BF%D0%BE%D1%80%D1%82%D0%B8%D0%B2%D0%BD%D1%8B%D0%B9-%D1%80%D0%B5%D0%BC%D0%B5%D1%88%D0%BE%D0%BA?product=MJ2T2RU/A&step=detail#" target="_blank">подробнее</a>
		</div>
		<div>
			<span>за 4 учеников</span>
			<img src="img/promo/iphone.jpeg">
			<label>iPhone 6</label>
			<a href="http://www.apple.com/ru/iphone-6/specs/" target="_blank">подробнее</a>
		</div>
	</div>
	
	<h3>Возможные способы:</h3>
	<div class="promo-info">
		<div class="row">
			<div class="col-sm-1" style="width: 41px">
				<circle>1</circle>
			</div>
			<div class="col-sm-11">
				<p>Поделитесь ссылкой <a href="http://ege-centr.ru/courses/11/?code={{Student.code}}">http://ege-centr.ru/courses/11/?code={{Student.code}}</a>.</p>
				<p>Эта ссылка – ваша личная рекомендация. Пришедшие</p> 
				<p>заключать договор сообщат, что пришли от</p> 
				<p>{{sklName(Student, 'genitive')}}, что даст ученику право на скидку 
				<p>5% на годовое обучение, а вам будет засчитан +1 ученик.</p>
			</div>
		</div>
		<div class="row pull-right">
			<div class="col-sm-1" style="width: 41px">
				<circle>2</circle>
			</div>
			<div class="col-sm-11">
				<p>Расскажите о ЕГЭ-Центре вашим одноклассникам.</p>
				<p>Если при заключении договора ученик сообщит нам</p>
				<p>ваши имя и фамилию, то он получит скидку 5% на годовое</p>
				<p>обучение, а вам будет засчитан +1 ученик.</p>
			</div>
		</div>
	</div>
	
	<h3 style="margin-bottom: 0">По вашей рекомендации пока пришло:</h3>
	<div class="center" style="position: relative">
		<div class="counter"></div>
		<ng-pluralize style="position: absolute; top: 49px; left: calc(50% + 40px)" count="students_count" when="{
			'one': 'ученик',
			'few': 'ученика',
			'many': 'учеников',
		}"></ng-pluralize>
		<div>
			цифра меняется автоматически в режиме реального времени<br>
			программа действует до 28 ноября 2015 года
		</div>
	</div>
</div>