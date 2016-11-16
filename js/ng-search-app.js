angular.module("Search", ["ngAnimate"]).filter('reverse', function() {
			return function(items) {
				return items.slice().reverse();
			};
		}).controller("ResultsCtrl", function($scope) {
			setTimeout(function(){
				console.log($scope.requests)
			}, 1000)
		}).controller('SearchCtrl', function($scope,$http) {
			$scope.result = [];

			var active = 0;

			$scope.links = {}


			//обрабатываем событие по нажатию на стрелочки клавиатуры
			$scope.key = function($event){

				//console.log('enter',$event.keyCode)

				if ($event.keyCode == 38){ //клавиша стрелка вверх
					if(active > 0){
						active--;
					}

					//console.log('up',active)
					build()
				}else if($event.keyCode == 40){//клавиша стрелка вниз
					if(active < $scope.success.data.total ){
						active++;
					}

					//console.log('down',active)
					build()
				}else if($event.keyCode == 13) {//
					window.open($scope.links[active])
					console.log('enter',active,$scope.links[active])
				}
				return false;
			}

			var build = function(){
				var resultHTML = '';
				var all = 0;
				if($scope.success.data.search.students.length > 0){
					angular.forEach($scope.success.data.search.students, function(v,k){
						all++;
						var classNameActive = (active == all)?'active':'';
						$scope.links[all] = '/student/'+ v.id;
						resultHTML += '<div class="resultRow '+classNameActive+'"><a href="'+$scope.links[all]+'" target="_blank">' + v.last_name + ' ' + v.first_name + ' ' + v.middle_name + '</a> - Ученик</div>';
					});
				}

				//ученики которые представитили
				if($scope.success.data.search.representatives.length > 0){
					angular.forEach($scope.success.data.search.representatives, function(v,k){
						all++;
						var classNameActive = (active == all)?'active':'';
						$scope.links[all] = '/student/'+ v.student_id;
						resultHTML += '<div class="resultRow '+classNameActive+'"><a href="'+$scope.links[all]+'" target="_blank">' + v.last_name + ' ' + v.first_name + ' ' + v.middle_name + '</a> - Ученик</div>';
					});
				}

				//преподователи
				if($scope.success.data.search.teachers.length > 0){
					angular.forEach($scope.success.data.search.teachers, function(v,k){
						all++;
						var classNameActive = (active == all)?'active':'';
						$scope.links[all] = '/teachers/edit/'+ v.id;
						resultHTML += '<div class="resultRow '+classNameActive+'"><a href="'+$scope.links[all]+'" target="_blank">' + v.last_name + ' ' + v.first_name + ' ' + v.middle_name + '</a> - Преподаватель</div>';
					});
				}

				//заявки
				if($scope.success.data.search.requests.length > 0){
					angular.forEach($scope.success.data.search.requests, function(v,k){
						all++;
						var classNameActive = (active == all)?'active':'';
						$scope.links[all] = '/requests/edit/' + v.id
						resultHTML += '<div class="resultRow '+classNameActive+'"><a href="'+$scope.links[all]+'" target="_blank">' + v.name + '</a> - Заявка </div>';
					});
				}

				angular.element("#searchResult").html(resultHTML)

				return resultHTML;
			}



			$scope.$watch('query',function(val){
				if(!angular.isUndefined(val) && val != ''){

					$http.post('/search',{
						query: val
					}).then(function(success){
						if(angular.isArray(success.data.search)){
							//пустой массив от поиска
							console.log('search is clear')
							active = 0;
							angular.element("#searchResult").html('<div class="notFound">Совпадений нет</div>')
							var height = $('#searchResult').height();
							$('#searchResult .notFound').css('height',height-10).css('padding-top',parseInt(height/2)-20)
							height = null
						}else{
							active = 0;
							$scope.success = success;
							build()
						}
					})
					//console.log('query change','-'+val+'-')
				}else{
					$scope.result = []; //обнуляем если ничего не введено или стерли
					angular.element("#searchResult").html('');

				}

			})



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

		setTimeout(function(){
			$('input#searchQueryInput').focus()
		},50)


		return false;
	})




})

