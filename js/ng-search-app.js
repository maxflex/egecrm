angular.module("Search", ["ngAnimate"]).filter('reverse', function() {
			return function(items) {
				return items.slice().reverse();
			};
		}).controller("ResultsCtrl", function($scope) {
			setTimeout(function(){
				console.log($scope.requests)
			}, 1000)
		}).controller('SearchCtrl', function($scope, $http) {
			$scope.result = [];

			var active = 0;

			$scope.links = {}; //храним ссылки
			$scope.oldQuery = ''; //храним предидущие значение для поиска
			$scope.query = ''; //поисковый запрос

			var scroll = function(){
				var totalObject = Object.keys($scope.links).length;
				$('#searchResult').scrollTop((active - 4) * 30)
			}

			//обрабатываем событие по нажатию на стрелочки клавиатуры
			$scope.key = function($event){

				if ($event.keyCode == 38){ //клавиша стрелка вверх
					if(angular.isUndefined($scope.success.data)){ //проверка на наличе данных
						return false
					}

					if(active > 0){
						active--;
					}

					build()
					scroll()
					$event.preventDefault()

				}else if($event.keyCode == 40){//клавиша стрелка вниз
					if(angular.isUndefined($scope.success.data)){ //проверка на наличе данных
						return false
					}

					if(active < $scope.success.data.total){
						active++;
					}
					build()

					if(active > 4){
						scroll()
					}

				}else if($event.keyCode == 13) {// ентер
					window.open($scope.links[active])
				}else{

					if($scope.oldQuery != $scope.query){
						if(!angular.isUndefined($scope.query) && $scope.query != ''){
							$http.post('/search', {
								query: $scope.query
							}).then(function(success){
								if(angular.isArray(success.data.search)){
									//пустой массив от поиска
									active = 0;
									angular.element("#searchResult").html('<div class="notFound">cовпадений нет</div>')

									var height = $('#searchResult').height();
									$('#searchResult .notFound').css('height', height-10).css('padding-top', parseInt(height/2) - 20)

									height = null
									$scope.success = {}; // обнуляем результат поиска
								}else{
									active = 0;
									$scope.success = success;
									build()
								}
							})
						}else{
							$scope.success = {}; // обнуляем результат поиска
							angular.element("#searchResult").html('');
						}

						$scope.oldQuery = $scope.query
					}

				}

				return false;
			}

			var build = function(){
				var resultHTML = '';
				var all = 0;
				if($scope.success.data.search.students.length > 0){
					angular.forEach($scope.success.data.search.students, function(v, k){
						all++;
						var classNameActive = (active == all) ? 'active' : '';
						$scope.links[all] = '/student/' + v.id;
						resultHTML += '<div class="resultRow ' + classNameActive + '"><a href="' + $scope.links[all] + '" target="_blank">' + v.last_name + ' ' + v.first_name + ' ' + v.middle_name + '</a> - Ученик</div>';
					});
				}

				//ученики которые представитили
				if($scope.success.data.search.representatives.length > 0){
					angular.forEach($scope.success.data.search.representatives, function(v, k){
						all++;
						var classNameActive = (active == all) ? 'active' : '';
						$scope.links[all] = '/student/' + v.student_id;
						resultHTML += '<div class="resultRow ' + classNameActive+'"><a href="' + $scope.links[all] + '" target="_blank">' + v.last_name + ' ' + v.first_name + ' ' + v.middle_name + '</a> - Ученик</div>';
					});
				}

				//преподователи
				if($scope.success.data.search.teachers.length > 0){
					angular.forEach($scope.success.data.search.teachers, function(v, k){
						all++;
						var classNameActive = (active == all) ? 'active' : '';
						$scope.links[all] = '/teachers/edit/' + v.id;
						resultHTML += '<div class="resultRow ' + classNameActive + '"><a href="' + $scope.links[all] + '" target="_blank">' + v.last_name + ' ' + v.first_name + ' ' + v.middle_name + '</a> - Преподаватель</div>';
					});
				}

				//заявки
				if($scope.success.data.search.requests.length > 0){
					angular.forEach($scope.success.data.search.requests, function(v, k){
						all++;
						var classNameActive = (active == all) ? 'active' : '';
						$scope.links[all] = '/requests/edit/' + v.id
						resultHTML += '<div class="resultRow ' + classNameActive + '"><a href="' + $scope.links[all] + '" target="_blank">' + v.name + '</a> - Заявка </div>';
					});
				}

				angular.element("#searchResult").html(resultHTML)

				return resultHTML;
			}
		})

$(document).ready(function(){
	angular.bootstrap(document.getElementById("searchModal"), ['Search']);


	//насаживаем евент открытия модального окна
	//searchModalOpen
	$('#searchModalOpen').click(function(){
		var windowHeight = window.innerHeight; // определеяем высоту выдимой облости
		var windowWidth = window.innerWidth; // определеяем ширину выдимой облости
		var topPadding = parseInt(windowHeight/4) //определяем отступ справа для позиционировния окна
		var leftPadding = parseInt(windowWidth/4) //определяем отступ cлева для позиционировния окна
		var windowHeigh50 = parseInt(windowHeight/2) //определяем высоту окна
		var windowWidth50 = parseInt(windowWidth/2) //определяем высоту окна

		$('#searchModal .modal-content').css('height',windowHeigh50).css('width',windowWidth50)
		$('#searchModal .modal-dialog').css('margin-top',topPadding).css('margin-left',leftPadding)
		$('#searchResult').css('height',windowHeigh50-70)

		$('#searchModal').modal({
			keyboard: true,
		})

		setTimeout(function() {
			$('#searchQueryInput').focus();
		}, 500);




		return false;
	})




})

