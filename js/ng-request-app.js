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
			// ID свежеиспеченного договора (у новых отрицательный ID,  потом на серваке
			// отрицательные IDшники создаются, а положительные обновляются (положительные -- уже существующие)
			$scope.new_contract_id = -1;
			
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
			$scope.addSubject = function(contract) {
				// HTML-элементы предмета и кол-ва предметов
				subject 		= $("#subjects-select" + contract.id)
				subject_count	= $("#add-subject-count" + contract.id)
				
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
				
				// Инициализируем, если не установлено
				contract.subjects = initIfNotSet(contract.subjects)
				
				// Добавляем предмет
				contract.subjects.push({
					"id_subject": subject.val(),
					"name"		: $("#subjects-select" + contract.id  + " option:selected").text(),
					"count"		: subject_count.val()
				})
				
				
				// Обнуляем значения
				$('#subjects-select' + contract.id  + ' option:first-child').attr("selected", "selected")
				subject_count.val("")
			}
			
			// Удалить предмет из договора
			$scope.removeSubject = function(contract, index) {
				contract.subjects.splice(index, 1);
			}
			
			
			/**
			 * Расторгнуть договор/отменить расторжение
			 *
			 * status расторжения  [0/1 активен/расторгнут]
			 */
			$scope.contractCancelled = function(contract, status) {
				// отменить расторжение
				if (contract.cancelled) {
					bootbox.confirm("Вы уверены, что хотите отменить расторжение?", function(result) {
						if (result === true) {
							contract.cancelled = 0
							$scope.$apply()
						}
					})
				} else {
					// расторгнуть
					bootbox.confirm("Вы уверены, что хотите расторгнуть договор?", function(result) {
						if (result === true) {
							contract.cancelled = 1
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
				// получаем ID элемента
				item_id = $($event.currentTarget).attr("item")
				
				if ($event.keyCode == 13) {
					$event.currentTarget.blur();
					
					// выполняем функцию в зависимости от ID
					switch ($event.currentTarget.id) {
						case "add-subject-count" + item_id: {
							// добавление предмета
							$.each($scope.contracts, function(index, contract) {
								if (contract.id == item_id) {
									$scope.addSubject(contract)
								}
							})
							
							break
						}
						case "payment-sum": {
							// добавление платежа
							$scope.addPayment()
							
							break
						}
					}
				}
			}
			
			// Добавить контракт
			$scope.addContract = function() {
				// Новый контракт
				new_contract = {"id" : $scope.new_contract_id--}
				
				// Добавляем в коллекцию контрактов
				$scope.contracts.push(new_contract)
				
				// Вешаем маску даты на новый элемент
				rebindMasks()
				
				// Вешаем файлаплоад на новый элемент
				setTimeout(function() {
					$scope.bindFileUpload(new_contract)
				}, 100)
			}
		
			// Добавить платеж
			$scope.addPayment = function() {
				// Инициализация если не установлено
				$scope.payments = initIfNotSet($scope.payments);
				
				// Получаем элементы (я знаю, что по-хорошему нужно получить их один раз вне функции
				// а не каждый раз, когда функция вызывается, искать их заново. Но забей. Хочу их внутри когда
				payment_date	= $("#payment-date")
				payment_sum 	= $("#payment-sum")
				payment_select	= $("#payment-select")
				
				// Установлена ли сумма платежа?
				if (!$scope.new_payment.id_status) {
					payment_select.focus().parent().addClass("has-error")
					return
				} else {
					payment_select.parent().removeClass("has-error")
				}
				
				// Установлена ли дата платежа?
				if (!$scope.new_payment.date) {
					payment_date.focus().parent().addClass("has-error")
					return
				} else {
					payment_date.parent().removeClass("has-error")
				}
				
				// Установлена ли сумма платежа?
				if (!$scope.new_payment.sum) {
					payment_sum.focus().parent().addClass("has-error")
					return
				} else {
					payment_sum.parent().removeClass("has-error")
				}

				
				// Добавляем дополнительные данные в new_payment
				$scope.new_payment.user_login		= $scope.user.login
				$scope.new_payment.first_save_date	= moment().format('YYYY-MM-DD HH:mm:ss')
				
				$scope.payments.push($scope.new_payment)
				
				$scope.new_payment = {id_status : 0}
				
				// Выборка дат на новый платеж
				rebindMasks()
			}
			
			// Удаляем предмет
			$scope.removePayment = function(index) {
				$scope.payments[index].deleted = 1;
				$scope.$apply()
			}
			
			// форматировать дату	
			$scope.formatDate = function(date){
		        var dateOut = new Date(date);
		        return dateOut;
		    };
		    
		    // Превратить в файлаплоад
		    $scope.bindFileUpload = function(contract) {
				// загрузка файла договора
				$('#fileupload' + contract.id).fileupload({
					dataType: 'json',
			        done: function (i, response) {
						if (response.result !== "ERROR") {
							contract.file 			= response.result.file 			// Получаем временное имя загруженного файла
							contract.uploaded_file	= response.result.uploaded_file	// Получаем имя загруженного файла
							$scope.$apply()
						} else {
							notifyError("Ошибка загрузки")
						}
			        }
			    })
		    }
			
			$(document).ready(function() {
				// Добавляем JSON
				$("#freetime_json").val(JSON.stringify($scope.freetime));
				
				// Биндим загрузку к уже имеющимся дагаварам
				$.each($scope.contracts, function(index, contract) {
					$scope.bindFileUpload(contract)
				})
				
				// Если дагаваров нет, добавляем один по умолчанию
				if ($scope.contracts.length == 0) {
					$scope.addContract()
				}
				
				// Биндим пару-время к свободному времени
				datePair()
				
				
				// Кнопка сохранения
				$("#save-button").on("click", function() {
					data = $("#request-edit").serializeArray()
					$.post("requests/ajaxSave", data)
						.success(function() {
							notifySuccess("Данные сохранены")
						})
						.error(function() {
							notifyError("Ошибка сохранения")
						})
				});
				
				// дополнительный apply on document.ready
				$scope.$apply()
			})
		})