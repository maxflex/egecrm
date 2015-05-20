	angular.module("Request", ["ngAnimate", "ngMap"])
		.filter('reverse', function() {
			return function(items) {
				return items.slice().reverse();
			};
		})
		/*
			
			Контроллер списка заявок
			
		*/
		.controller("ListCtrl", function($scope) {
			// chosen_list по умолчанию
			$scope.chosen_list = 0
			
			// Выбрать список
			$scope.changeList = function(key) {
				$scope.chosen_list = key;
				$scope.$apply()
			}
		})
		.controller("EditCtrl", function ($scope) {
			// значение "Платежи" по умолчанию (иначе подставляет пустое значение)
			$scope.new_payment = {id_status : 0}
			// Маркеры
			$scope.markers 	= [];
			// ID маркера
			$scope.marker_id= 1;
			// Дни недели
			$scope.weekdays = ["ПН", "ВТ", "СР", "ЧТ", "ПТ", "СБ", "ВС"]
			
			// Возвращаем структурированные данные по маркерам
			// для передачи на сохранение
			$scope.markerData = function() {
				if ($scope.markers.length) {
					marker_data = [] // инициалицазия
					// генерируем данные
					$.each($scope.markers, function(index, marker) {
						marker_data.push({
							"lat" 	: marker.position.lat(),
							"lng" 	: marker.position.lng(),
							"type"	: marker.type
						});	
					})
					
					return marker_data 
				} else {
					return ""
				}
			}
			
			// Показать карту
			$scope.showMap = function(type) {
				// устанавливаем тип метки
				$scope.marker_type = type
				
				// Показываем карту
				lightBoxShow()
				google.maps.event.trigger($scope.gmap, 'resize')
				
				// Зум и центр карты по умолчанию
				$scope.gmap.setCenter(new google.maps.LatLng(55.7387, 37.6032))
				$scope.gmap.setZoom(10)
				
				// Если уже есть добавленные маркеры
				if ($scope.markers.length) {
					// отображать только метки с выбранным типом
					bounds = new google.maps.LatLngBounds()
					// есть отображаемые маркеры
					has_markers = false
					// отображаем маркеры по одному
					$.each($scope.markers, function(index, marker) {
						if (marker.type != type) {
							marker.setVisible(false)
						} else {
							has_markers = true // отображаемые маркеры есть
							marker.setVisible(true)
							bounds.extend(marker.position) // границы карты в зависимости от поставленных меток
						}	
					})
					
					// если отображаемые маркеры есть, делаем зум на них
					if (has_markers) {
						$scope.gmap.setZoom(20)
						$scope.gmap.fitBounds(bounds)
						$scope.gmap.panToBounds(bounds)	
					}
				}
			}
			
			// Добавляем ивент удаления маркера
			$scope.bindMarkerDelete = function(marker) {
				google.maps.event.addListener(marker, "dblclick", function(event) {
					// удаляем маркер с карты
					marker.setMap(null)
					
					// удаляем маркер из коллекции
					$.each($scope.markers, function(index, m) {
						if (angular.equals(marker, m)) {
							$scope.markers.splice(index, 1)
						}
					})
				})	
			}
			
			// Загрузить маркеры, уже сохраненные на серваке и загруженные оттуда
			$scope.loadServerMarkers = function() {
				$.each($scope.server_markers, function(index, marker) {
					// Создаем маркер
					var marker = newMarker($scope.marker_id++, marker.type, new google.maps.LatLng(marker.lat, marker.lng))
										
					// Добавляем маркер в маркеры
					$scope.markers.push(marker)
					
					// Добавляем маркер на карту
					marker.setMap($scope.map)
					
					// Добавляем ивент удаления маркера
					$scope.bindMarkerDelete(marker)
				})
				
				// применяем изменения (ОБЯЗАТЕЛЬНО, иначе слетят метки без изменений)
				$scope.$apply()
			}
			
			// ПОСЛЕ ЗАГРУЗКИ КАРТЫ
			$scope.$on('mapInitialized', function(event, map) {
				// Запоминаем карту после инициалицации
				$scope.gmap = map
				
				// Добавляем существующие метки
				$scope.loadServerMarkers();
				
				// События добавления меток
				google.maps.event.addListener(map, 'click', function(event) {
					
					// Создаем маркер
					var marker = newMarker($scope.marker_id++, $scope.marker_type, event.latLng)
										
					// Добавляем маркер в маркеры
					$scope.markers.push(marker)
					
					// Добавляем маркер на карту
					marker.setMap(map)
					
					// Добавляем ивент удаления маркера
					$scope.bindMarkerDelete(marker)
				})
		    });
			
			// Выбор дня и начало добавления свободного времени
			$scope.chooseDay = function(day) {
				// если этот день уже выбран, снимаем выборку
				if ($scope.adding_day == day) {
					$scope.adding_day = 0
					// снимаем фокус с кнопки (иначе некрасиво)
					$(".btn-group-freetime button").blur();
				} else {
					// иначе выбираем день
					$scope.adding_day = day	
				}
			}
			
			// Добавление отрывка свободного времени
			$scope.addFreetime = function() {
				if (!$scope.freetime) {
					$scope.freetime = []
				}
				
				// Добавляем свободное время
				$scope.freetime.push({
					"day"	: $scope.adding_day,
					"start"	: $("#free_time_start").val(),
					"end"	: $("#free_time_end").val()
				});
				
				// Добавляем JSON
				$("#freetime_json").val(JSON.stringify($scope.freetime));
				
				// Обнуляем значения в добавлении
				$("#free_time_start").val("")
				$("#free_time_end").val("")
			}
			
			// Подсчет кол-ва свободного времени конкретного дня
			$scope.hasFreetime = function(day) {
				count = 0
				angular.forEach($scope.freetime, function(freetime) {
					if (freetime.day == day) {
						count++;
					}
				})
				// возвращаем либо FALSE, либо кол-во дней
				return (count == 0 ? false : count);
			}
			
			// Добавить предмет к договору
			$scope.addSubject = function() {
				// HTML-элементы предмета и кол-ва предметов
				subject 		= $("#subjects-select")
				subject_count	= $("#add-subject-count")
				
				// Если предмет или кол-во не установлено
				if (!subject.val()) {
					subject.focus().parent().addClass("has-error")
					return false
				} else {
					subject.parent().removeClass("has-error")
				}
				
				if (!subject_count.val()) {
					subject_count.focus().parent().addClass("has-error")
					return false
				} else {
					subject_count.parent().removeClass("has-error")
				}
				
				if (!$scope.subjects) {
					$scope.subjects = []
				}
				
				// Добавляем предмет
				$scope.subjects.push({
					"id_subject": $("#subjects-select").val(),
					"name"		: $("#subjects-select option:selected").text(),
					"count"		: $("#add-subject-count").val()
				})
				
				// Обнуляем значения
				$('#subjects-select option:first-child').attr("selected", "selected")
				$("#add-subject-count").val("")
				
				// Добавляем JSON
				$("#subjects_json").val(JSON.stringify($scope.subjects));
				
			}
			
			// Удалить предмет из договора
			$scope.removeSubject = function(index) {
				$scope.subjects.splice(index, 1);
				$scope.$apply();
				
				// Добавляем JSON
				$("#subjects_json").val(JSON.stringify($scope.subjects));
			}
			
			
			/**
			 * Расторгнуть договор/отменить расторжение
			 * 
			 */
			$scope.contractCancelled = function(contract_cancelled) {
				// отменить расторжение
				if (!contract_cancelled) {
					bootbox.confirm("Вы уверены, что хотите отменить расторжение?", function(result) {
						if (result === true) {
							$scope.contract_cancelled = 0
							$scope.$apply()
						}
					})
				} else {
					// расторгнуть
					bootbox.confirm("Вы уверены, что хотите расторгнуть договор?", function(result) {
						if (result === true) {
							$scope.contract_cancelled = 1
							$scope.$apply()
						}
					})
				}
			}
			
			// Время 
			function datePair() {
				$('.time').timepicker({
				    'timeFormat'	: 'H:i',
				    'scrollDefault'	: "15:30"
				});
			
				$('#timepair').datepair({
					'defaultTimeDelta': 7200000 // 2 часа в миллисекундах
				});
			}
			
			// Добавление по нажатию ENTER
			$scope.watchEnter = function($event) {
				console.log($event.currentTarget.id)
				if ($event.keyCode == 13) {
					$event.currentTarget.blur();
					
					// выполняем функцию в зависимости от ID
					switch ($event.currentTarget.id) {
						case "add-subject-count": {
							// добавление предмета
							$scope.addSubject()
							break
						}
						case "payment-sum": {
							// добавление платежа
							$scope.addPayment()
						}
					}
				}
			}
		
			// Добавить платеж
			$scope.addPayment = function() {
				if (!$scope.payments) {
					$scope.payments = []
				}
				
				// Добавляем дополнительные данные в new_payment
				$scope.new_payment.user_login		= $scope.user.login
				$scope.new_payment.first_save_date	= moment().format('YYYY-MM-DD HH:mm:ss')
				
				$scope.payments.push($scope.new_payment)
				
				$scope.new_payment = {id_status : 0}
			}
			
			// Удаляем предмет
			$scope.removePayment = function(index) {
				$scope.payments[index].deleted = 1;
				$scope.$apply();
			}
			
			// форматировать дату	
			$scope.formatDate = function(date){
		        var dateOut = new Date(date);
		        return dateOut;
		    };
			
			$(document).ready(function() {
				// Добавляем JSON
				$("#subjects_json").val(JSON.stringify($scope.subjects));
				// Добавляем JSON
				$("#freetime_json").val(JSON.stringify($scope.freetime));
				
				datePair();
				
				
				// загрузка файла договора
				$('#fileupload').fileupload({
					dataType: 'json',
			        done: function (i, response) {
				        console.log(i, response)
						if (response.result !== "ERROR") {
							bootbox.alert("Договор загружен")
							$scope.contract_file = response.result.file // Получаем расширение загруженного файла
							$scope.$apply()
						} else {
							bootbox.alert("Ошибка загрузки")
						}
			        }
			    });
				
				// Кнопка сохранения
				$("#save-button").on("click", function() {
					data = $("#request-edit").serializeArray();

					$.post("requests/ajaxSave", data)
						.success(function() {
							$.notify({message: "Данные сохранены", icon: "glyphicon glyphicon-ok"}, {
								type : "success",
								allow_dismiss : false,
								placement: {
									from: "top",
								}
							});
						})
						.error(function() {
							$.notify({message: "Ошибка сохранения", icon: "glyphicon glyphicon-remove"}, {
								type : "danger",
								allow_dismiss : false,
								placement: {
									from: "top",
								}
							});
						})
				});
				
				// дополнительный apply on document.ready
				$scope.$apply()
			})
		})