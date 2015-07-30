	var test;

	angular.module("Request", ["ngAnimate", "ngMap", "ui.bootstrap"])
		.config( [
		    '$compileProvider',
		    function( $compileProvider )
		    {
		        $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|chrome-extension|sip):/);
		        // Angular before v1.2 uses $compileProvider.urlSanitizationWhitelist(...)
		    }
		])
		.filter('reverse', function() {
			return function(items) {
				if (items) {
					return items.slice().reverse();
				}
			};
		})
		/*

			Контроллер списка заявок

		*/
		.controller("ListCtrl", function($scope, $log) {
			// хэндл псевдо-истории
			window.addEventListener("popstate", function(e) {
				// анфокус
				$(".list-link").blur()

				// меняем список
				if (e.state === null) {
					$scope.changeList($scope.request_statuses[0], false)
				} else {
					$scope.changeList(e.state, false)
				}
			})
			
			// проверить номер телефона
		    $scope.isMobilePhone = function(phone) {
			    // пустой номер телефона – это тоже правильный номер телефона
			    if (!phone) {
				    return false
			    }
				
				return !phone.indexOf("+7 (9")
		    }

			$scope.smsDialog = smsDialog;
			
			$scope.sipNumber = function(number) {
				return "sip:" + number.replace(/[^0-9]/g, '')
			}

			$scope.callSip = function(number) {
				number = $scope.sipNumber(number)
				window.open(number);
			}

			$scope.getTimeClass = function(timestamp) {
				hour = 60 * 60 * 1000;

				// если больше 2 часов
				if (Date.now() - timestamp >= (hour * 2)) {
					return 'label-red'
				}

				if (Date.now() - timestamp >= hour) {
					return 'label-yellow'
				}
			}

			// Выбрать список
			$scope.changeList = function(request_status, push_history) {
				//  Если нажимаем на один и тот же список -- ничего не делаем
				/*
				if (request_status.id == $scope.chosen_list) {
					return
				}*/

				// Устанавливаем список
				$scope.chosen_list = request_status.id

				if (push_history) {
					window.history.pushState(request_status, '', 'requests/' + request_status.constant.toLowerCase());
				}

				// Получаем первую страницу задач списка
				$scope.getByPage(1)
			}

			// Страница изменилась
			$scope.pageChanged = function() {
				// Получаем задачи, соответствующие странице и списку
				$scope.getByPage($scope.currentPage)
			}

			// Получаем задачи, соответствующие странице и списку
			$scope.getByPage = function(page) {
				ajaxStart()
				$.get("requests/ajax/GetByPage", {
					'page'		: page,
					'id_status'	: $scope.chosen_list
				}, function(response) {
					ajaxEnd()
					$scope.requests = response
					$scope.$apply()
					bindUserColorControl()
					initComments()
				}, "json")
			}

		})
		.controller("EditCtrl", function ($scope, $log) {
			// значение "Платежи" по умолчанию (иначе подставляет пустое значение)
			$scope.new_payment = {id_status : 0}
			$scope.current_contract = {subjects : []}

			// сохранение
			$scope.saving = false

			// Маркеры
			$scope.markers 	= [];
			// ID маркера
			$scope.marker_id= 1;
			// Дни недели
			$scope.weekdays = [
				{"short" : "ПН", "full" : "Понедельник"},
				{"short" : "ВТ", "full" : "Вторник"},
				{"short" : "СР", "full" : "Среда"},
				{"short" : "ЧТ", "full" : "Четверг"},
				{"short" : "ПТ", "full" : "Пятница"},
				{"short" : "СБ", "full" : "Суббота"},
				{"short" : "ВС", "full" : "Воскресенье"}
			]

			// ID свежеиспеченного договора (у новых отрицательный ID,  потом на серваке
			// отрицательные IDшники создаются, а положительные обновляются (положительные -- уже существующие)
			// $scope.new_contract_id = -1;

			// анимация загрузки RENDER ANGULAR
			angular.element(document).ready(function() {
				$("#request-edit").css("opacity", 1)
				$("#panel-loading").hide()
			})
			
			$scope.toggleSubject = function(id_subject) {
				// если предметы не установлены
				$scope.current_contract.subjects = initIfNotSet($scope.current_contract.subjects)
				
				if ($("#checkbox-subject-" + id_subject).is(":checked") == false) {
					delete $scope.current_contract.subjects[id_subject]
				} else {
					$scope.current_contract.subjects[id_subject] = {'id_subject': id_subject, 'name' : $scope.subjects[id_subject], 'count' : '' }
				}
				// console.log($scope.current_contract.subjects[id_subject]);
				setTimeout(function(){
					$scope.$apply()
				}, 100);
			}

			/**
			 * Показывать в свободном времени только дни, где есть свободное время
			 *
			 */
			$scope.freetimeControl = function(day_number) {
				return $.grep($scope.freetime, function(v, i) {
					return (v.day == (day_number + 1) && !v.deleted)
				}).length > 0
			}


			$scope.formatContractDate = function(date) {
				if (date == null) {
					return
				}
				date = date.split('.')
				
				// был баг. месяц делал автоматически +1
				month = date[1] - 1;
				if (month <= 0) {
					month = 12
				}
				
				date = new Date(date[2], month, date[0])
				return moment(date).format("DD MMMM YYYY г.")
			}


			/**
			 * Склеить заявки
			 *
			 */
			$scope.glue = function(delete_student) {
				ajaxStart()
				$.post("requests/ajax/GlueRequest", {
					id_request: $scope.id_request, 
					id_student: $scope.id_student_glue,
					delete_student: delete_student
				}, function(response) {
					// если склеилось, то обновляем страницу
					if (response === true) {
						location.href = "requests/edit/" + $scope.id_request;
					} else {
						notifyError("Не удалось склеить")
					}
				}, "json")
			}

			/**
			 * Получить ученика для склейки
			 *
			 */
			$scope.findStudent = function() {
				console.log($("#id-student-glue").val())
				// если есть ID в поле, то ищем ученика по ID
				if ($scope.id_student_glue) {
					$.get("requests/ajax/getStudent", {id: $scope.id_student_glue}, function(response) {
						$scope.GlueStudent = response
						$scope.$apply()
					}, "json")
				} else {
				//  иначе обнуляем ученика
					$scope.GlueStudent = null
				}
			}



			/**
			 * Выбрать ID контракта для последующей печати договора
			 *
			 */
			$scope.printContract = function(id_contract) {
				$scope.id_contract_print = id_contract
				lightBoxShow('print')
			//	printDiv("contract-print-" + id_contract);
			}
			
			/**
			 * Запустить печать договора
			 *
			 */
			$scope.runPrint = function() {
				printDiv("contract-print-" + $scope.id_contract_print)
				lightBoxHide()
			}
			

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
				lightBoxShow('map')
				google.maps.event.trigger($scope.gmap, 'resize')
				
				// Зум и центр карты по умолчанию
				$scope.gmap.setCenter(new google.maps.LatLng(55.7387, 37.6032))
				$scope.gmap.setZoom(10)
				
				// Обнуляем значение поиска
				$("#map-search").val("")
				
				// Удаляем все маркеры поиска
				if ($scope.search_markers && $scope.search_markers.length) {
					$.each($scope.search_markers, function(i, marker) {
						$log.log(marker)
						marker.setMap(null)
					})
					$scope.search_markers = []
				}
				
				// Если уже есть добавленные маркеры
				if ($scope.markers.length) {
					// отображать только метки с выбранным типом
					bounds = new google.maps.LatLngBounds()
					// есть отображаемые маркеры
					markers_count = 0
					// отображаем маркеры по одному
					$.each($scope.markers, function(index, marker) {
						if (marker.type != type) {
							marker.setVisible(false)
						} else {
							markers_count++ // отображаемые маркеры есть
							marker.setVisible(true)
							bounds.extend(marker.position) // границы карты в зависимости от поставленных меток
						}
					})

					// если отображаемые маркеры есть, делаем зум на них
					if (markers_count > 0) {
						$scope.gmap.fitBounds(bounds)
						$scope.gmap.panToBounds(bounds)
						$scope.gmap.setZoom(12)
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

			// следим за количеством маркеров
			$scope.$watchCollection("markers", function(newValue, oldValue) {
				$scope.marker_school_count 	= 0
				$scope.marker_home_count	= 0

				// подсчитываем кол-во
				$.each(newValue, function(i, marker) {
					if (marker.type == "school") {
						$scope.marker_school_count++
					} else {
						$scope.marker_home_count++
					}
				})

				//$scope.$apply()
			})

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
			
			$scope.searchMap = function(element) {
				console.log($(element))	
			}
			
			$scope.gmapAddMarker = function(event) {
				// Создаем маркер
				var marker = newMarker($scope.marker_id++, $scope.marker_type, event.latLng)

				// Добавляем маркер в маркеры
				$scope.markers.push(marker)

				// Добавляем маркер на карту
				marker.setMap($scope.gmap)

				// Добавляем ивент удаления маркера
				$scope.bindMarkerDelete(marker)
			}
			
			// ПОСЛЕ ЗАГРУЗКИ КАРТЫ
			$scope.$on('mapInitialized', function(event, map) {
				// Запоминаем карту после инициалицации
				$scope.gmap = map
				
				test = map
				
				// Добавляем существующие метки
				$scope.loadServerMarkers();
				
				
				// generate recommended search bounds
				INIT_COORDS = {lat: 55.7387, lng: 37.6032};
				$scope.RECOM_BOUNDS = new google.maps.LatLngBounds(
		            new google.maps.LatLng(INIT_COORDS.lat-0.5, INIT_COORDS.lng-0.5), 
		            new google.maps.LatLng(INIT_COORDS.lat+0.5, INIT_COORDS.lng+0.5)
		        );        
				$scope.geocoder = new google.maps.Geocoder();
				
				// События добавления меток
				google.maps.event.addListener(map, 'click', function(event) {
					$scope.gmapAddMarker(event)
				})
		    });
		    
		    // Поиск по карте
		    $scope.searchMap = function(address) {
				$scope.geocoder.geocode({
					address: address + ", московская область",
//					componentRestrictions: {
//						locality: "Moscow",
//				    },
					bounds: $scope.RECOM_BOUNDS,
				}, function(results, status) {
				    if (status == google.maps.GeocoderStatus.OK) {
					    
					    // максимальное кол-во результатов
					    max_results = 3
					    
					    // масштаб поиска
						search_result_bounds = new google.maps.LatLngBounds()
					    
					    $.each(results, function(i, result) {
						    if (i >= max_results) {
							    return
						    }
						    
//							$scope.gmap.setCenter(result.geometry.location)
							search_result_bounds.extend(result.geometry.location) // границы карты в зависимости от поставленных меток
							
							var myIcon = {
							  url: "http://www.clker.com/cliparts/U/8/J/z/5/D/google-maps-icon-blue-th.png",
							  scaledSize: new google.maps.Size(22,40), // the new size you want to use
							  origin: new google.maps.Point(0,0) // position in the sprite                   
							};
					        
							search_marker = new google.maps.Marker({
							    map: $scope.map,
							    position: result.geometry.location,
							    icon: myIcon,
							});
							
							google.maps.event.addListener(search_marker, 'click', function(event) {
								this.setMap(null)	
								$scope.gmapAddMarker(event)
							})
							
							$scope.search_markers = initIfNotSet($scope.search_markers)
//							$log.log($scope.search_markers)
							$scope.search_markers.push(search_marker)  
					    })
					    
					    // если отображаемые маркеры есть, делаем зум на них
						if (results.length > 0) {
							$scope.gmap.fitBounds(search_result_bounds)
							$scope.gmap.panToBounds(search_result_bounds)
							if (results.length == 1) {
								$scope.gmap.setZoom(12)	
							}
						
						}
				    } else {
						$("#map-search").addClass("has-error").focus()
				    }
				});
			}
			
			// Запуск поиска по карте
			$scope.gmapsSearch = function($event) {
				if ($event.keyCode == 13 || $event.type == "click") {
					// prevent empty
					if ($("#map-search").val() == "") {
						$("#map-search").addClass("has-error").focus()
						return
					} else {
						$("#map-search").removeClass("has-error")
					}
					
					$scope.searchMap($("#map-search").val())
				}
			}
			
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

			$scope.addAndRedirect = function() {
				$scope.redirect_after_save = $scope.id_request
				$("#save-button").click()
			}

			$scope.toggleCancelled = function(contract) {
				console.log(contract)
				if (contract.cancelled) {
					console.log("here")
					contract.cancelled_date = ""
					contract.cancelled_reason = ""
				}
			}

			// Добавление отрывка свободного времени
			$scope.addFreetime = function() {
				if (!$scope.freetime) {
					$scope.freetime = []
				}

				// Элементы
				free_time_start = $("#free_time_start")
				free_time_end 	= $("#free_time_end")

				// Если есть пустые поля
				if (!$scope.adding_day) {
					$("[ng-model='adding_day'").addClass("has-error")
					return
				} else  {
					$("[ng-model='adding_day'").removeClass("has-error")
				}


				if (!free_time_start.val()) {
					free_time_start.focus().addClass("has-error")
					return false
				} else {
					free_time_start.removeClass("has-error")
				}

				if (!free_time_end.val()) {
					free_time_end.focus().addClass("has-error")
					return false
				} else {
					free_time_end.removeClass("has-error")
				}

				// Добавляем свободное время
				$scope.freetime.push({
					"day"	: $scope.adding_day,
					"start"	: free_time_start.val(),
					"end"	: free_time_end.val()
				});

				// Добавляем JSON
				$("#freetime_json").val(JSON.stringify($scope.freetime));

				// Обнуляем значения в добавлении
				free_time_start.val("")
				free_time_end.val("")
				$scope.adding_day = ""
			}

			// Удаление свободного времени
			$scope.removeFreetime =  function(freetime) {
				$.each($scope.freetime, function(index, ft) {
					if (angular.equals(freetime, ft)) {
						//$scope.freetime.splice(index, 1)
						// все уже имеющиеся в базе с deleted = true будут удаляться из базы
						// все новые с deleted = true добавляться не будут
						$scope.freetime[index].deleted = true
						// Добавляем JSON
						$("#freetime_json").val(JSON.stringify($scope.freetime))
						$scope.$apply()
						return true
					}
				})
			}

			// Подсчет кол-ва свободного времени конкретного дня
			$scope.hasFreetime = function(day) {
				count = 0
				angular.forEach($scope.freetime, function(freetime) {
					if (freetime.day == day && !freetime.deleted) {
						count++;
					}
				})
				// возвращаем либо FALSE, либо кол-во дней
				return (count == 0 ? false : count);
			}


			// Получить общее количество предметов (для печати договора)
			$scope.subjectCount = function(contract) {
				count = 0
				$.each(contract.subjects, function(i, subject) {
					if (subject != undefined) {
						count = count + parseInt(subject.count)
					}
				})
				return count
			}

			// Передаем функция numToText() в SCOPE
			$scope.numToText = numToText;

			// Склонять имя в дательном падеже
			// https://github.com/petrovich/petrovich-js
			$scope.contractPrintName = function(person, padej) {
				var person = {
					first	: person.first_name,
					last	: person.last_name,
					middle	: person.middle_name,
				};

				// склоняем в дательный падеж
				person = petrovich(person, padej);

				// возвращаем ФИО
				return person.last + " " + person.first + " " + person.middle;
			}

			// Добавить предмет к договору
			$scope.addSubject = function(contract) {
				// HTML-элементы предмета и кол-ва предметов
				subject 		= $("#subjects-select" + contract.id)
				subject_count	= $("#add-subject-count" + contract.id)

				// Если предмет или кол-во не установлено
				if (!subject.val()) {
					subject.focus().addClass("has-error")
					return false
				} else {
					subject.removeClass("has-error")
				}

				if (!subject_count.val()) {
					subject_count.focus().addClass("has-error")
					return false
				} else {
					subject_count.removeClass("has-error")
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

			$scope.showHistory = function(contract) {
				if (contract._show_history) {
					contract._show_history = false
					$("#contract-history-" + contract.id).slideUp(300)
				} else {
					contract._show_history = true
					$("#contract-history-" + contract.id).slideDown(300)
				}
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


			/**
			 * Напоминание установлено? Если установлено, то появляется иконка "удалить напоминание"
			 *
			 */
			$scope.notificationSet = function() {
				return ($("#notificationtypes-select").val() &&
					$scope.notification_date &&
					$scope.notification_time
				) ? true : false
			}

			/**
			 * Удалить напоминание
			 *
			 */
			$scope.deleteNotification = function() {
				bootbox.confirm("Удалить напоминание?", function(result) {
					if (result === true) {
						$('#notificationtypes-select option:first-child').attr("selected", "selected");
						$scope.notification_date = ""
						$scope.notification_time = ""
						$scope.$apply()
					}
				})
			}

			/**
			 * Проверка на пустой договор. Если пустой, появляется функционал удаления
			 *
			 */
			$scope.emptyContract = function(contract) {
				if (contract.subjects.length || contract.sum || contract.date || (contract.files && contract.files.length)) {
					return false
				} else {
					return true
				}
			}

			// Время
			function datePair() {
				$('.time').timepicker({
				    'timeFormat'	: 'H:i',
				    'scrollDefault'	: "15:30",
				    'unknownFirst' 	: true		// параметр "неизвестно"
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

			// для позднего обновления
			$scope.lateApply = function() {
				setTimeout(function() {
					$scope.$apply()
				}, 100)
			}


			$scope.addContractNew = function() {
				// валидация параметров договора
				if (!$scope.current_contract.sum) {
					$("#contract-sum").addClass("has-error").focus()
					return false
				} else {
					$("#contract-sum").removeClass("has-error")
				}

				if (!$scope.current_contract.date) {
					$("#contract-date").addClass("has-error").focus()
					return false
				} else {
					$("#contract-date").removeClass("has-error")
				}

				if (!$scope.current_contract.grade) {
					$("select[name='grades']").addClass("has-error").focus()
					return false
				} else {
					$("select[name='grades']").removeClass("has-error")
				}

				// количество активных, но незаполненных полей "кол-во занятий"
				count = $(".contract-lessons").filter(function() {
					if (!$(this).val() && $(this).is(":visible")) {
						$(this).addClass("has-error").focus()
					} else {
						$(this).removeClass("has-error")
					}
					return ($(this).val() == "" && $(this).is(":visible"))
				}).length

				// если есть незаполненные поля, то валидация не пройдена
				if (count > 0) {
					return
				}

				// валидация формы расторжения
				if ($scope.current_contract.cancelled) {
					if (!$scope.current_contract.cancelled_date) {
						$("#contract-cancelled-date").addClass("has-error").focus()
						return false
					} else {
						$("#contract-cancelled-date").removeClass("has-error")
					}

					if (!$scope.current_contract.cancelled_reason) {
						$("#contract-cancelled-reason").addClass("has-error").focus()
						return false
					} else {
						$("#contract-cancelled-reason").removeClass("has-error")
					}
				}



				// очищаем subjects от undefined элементов (ВАЖНО!)
/*
				try {
					$scope.current_contract.subjects = $scope.current_contract.subjects.filter(function(e){return e})
				}
				catch (err) {

				}
*/


				$scope.current_contract.id_student = $scope.student.id

				if ($scope.current_contract.id) {
					ajaxStart()
					$.post("ajax/contractEdit", $scope.current_contract, function(response) {
						angular.forEach($scope.contracts, function(contract, i) {
							if (contract.id == $scope.current_contract.id) {
								$scope.contracts[i] = $scope.current_contract
								$scope.$apply()
							}
						})
						ajaxEnd()
						lightBoxHide()
					})
				} else {
					// сохраняем догавар
					ajaxStart()
					$.post("ajax/contractSave", $scope.current_contract, function(response) {
						ajaxEnd()
						lightBoxHide()

						$scope.current_contract.id 			= response.id
						$scope.current_contract.user_login 	= response.user_login
						$scope.current_contract.date_changed= response.date_changed
						
						//new_contract = angular.copy($scope.current_contract)
						//new_contract.subjects = angular.copy($scope.current_contract.subjects)
						new_contract = $.extend(true, {}, $scope.current_contract)
						
						
						new_contract.subjects = new_contract.subjects.filter(function(e){return e})
						
						new_contract.subjects.sort(function(a, b) {
							return a.id_subject - b.id_subject
						})
						
//						testyy = new_contract
						
						$scope.contracts.push(new_contract)
						$scope.$apply()

					}, "json");
				}
			}
			
			$scope.subjectChecked = function(id_subject) {
				checked = false
				angular.forEach($scope.current_contract.subjects, function(subject) {
					if (subject.id_subject == id_subject) {
						checked = true
						return
					}
				})

				return checked
			}

			$scope.getIndexByIdSubject = function(id_subject) {
				res = false
				angular.forEach($scope.current_contract.subjects, function(subject, i) {
					console.log(subject, i)
					if (subject.id_subject == id_subject) {
						res = i
						return
					}
				})

				return res
			}
			
			// вызывает окно редактирования контракта
			$scope.callContractEdit = function(contract)
			{
				$scope.current_contract = angular.copy(contract)

				if ($scope.current_contract.grade === null) {
					$scope.current_contract.grade = ""
				}
				
//				$.makeArray($scope.current_contract.subjects)
				
				// баг-контрол!!!! надо прокомментировать
/*
				if ($scope.current_contract.subjects[0]) {
					$scope.current_contract.subjects.unshift("bug")
				}
*/
				
				test = $scope.current_contract
				
				console.log($scope.current_contract.subjects)
				
				lightBoxShow('addcontract')
				$("select[name='grades']").removeClass("has-error")
				$scope.lateApply()
			}

			// Окно редактирования договора
			$scope.editContract = function(contract) {
				contract.no_version_control = 0

				$scope.callContractEdit(contract)
			}


			// без проводки
			$scope.editContractWithoutVersionControl = function(contract) {
				contract.no_version_control = 1

				$scope.callContractEdit(contract)
			}

			// Показать окно добавления платежа
			$scope.addContractDialog = function() {
				$scope.current_contract = {subjects : []}
				lightBoxShow('addcontract')
				$("select[name='grades']").removeClass("has-error")
				$scope.lateApply()
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

			// Удалить контракт
			$scope.deleteContract = function(contract) {
				bootbox.confirm("Вы уверены, что хотите удалить договор?", function(result) {
					if (result === true) {
						$.post("ajax/contractDelete", {"id_contract": contract.id})

						$.each($scope.contracts, function(i, v) {
							if (v !== undefined) {
								if (v.id == contract.id) {
									$scope.contracts.splice(i, 1)
									$scope.$apply()
									return
								}
							}
						})
					}
				})
			}

			// Удалить контракт из истории
			$scope.deleteContractHistory = function(contract, contract_history, index) {
				bootbox.confirm("Вы уверены, что хотите удалить версию договора из истории?", function(result) {
					if (result === true) {
						$.post("ajax/contractDeleteHistory", {"id_contract": contract_history.id})

						$("#contract_history_" + contract.id).addClass("active");
						$("#contract_history_li_" + contract.id).addClass("active");

						$.each($scope.contracts, function(i, v) {
							if (v.id == contract.id) {
								$scope.contracts[i].History.splice(index, 1)
								$scope.$apply()
								return
							}
						})
					}
				})
			}

			// Окно редактирования платежа
			$scope.editPayment = function(payment) {
				$scope.new_payment = angular.copy(payment)
				lightBoxShow('addpayment')
			}

			// Показать окно добавления платежа
			$scope.addPaymentDialog = function() {
				$scope.new_payment = {id_status : 0}
				lightBoxShow('addpayment')
			}

			// Разбиваем кнопку
			$scope.$watch("phone_duplicate", function(newValue, oldValue) {
				if (newValue !== null && newValue != "null") {
					$scope.id_student_phone_exists = newValue
				} else {
					$scope.id_student_phone_exists = null
				}
				// $scope.$apply()
			})


			$scope.addRequestToExisting = function() {
				var num = $scope.id_student_phone_exists.replace(/[^0-9]/g, '');
//				$scope.student.id = parseInt(num)
				$("#id_student_force").val(parseInt(num))
//				console.log($scope.id_student_phone_exists, $scope.student.id)
				$scope.addAndRedirect()
			}

			// Добавить платеж
			$scope.addPayment = function() {
				// Получаем элементы (я знаю, что по-хорошему нужно получить их один раз вне функции
				// а не каждый раз, когда функция вызывается, искать их заново. Но забей. Хочу их внутри когда
				payment_date	= $("#payment-date")
				payment_sum 	= $("#payment-sum")
				payment_select	= $("#payment-select")
				payment_type	= $("#paymenttypes-select")

				// Установлен ли способ оплаты
				if (!$scope.new_payment.id_status) {
					payment_select.focus().parent().addClass("has-error")
					return
				} else {
					payment_select.parent().removeClass("has-error")
				}

				// Установлен ли тип платежа?
				if (!$scope.new_payment.id_type) {
					payment_type.focus().parent().addClass("has-error")
					return
				} else {
					payment_type.parent().removeClass("has-error")
				}

				// Установлена ли сумма платежа?
				if (!$scope.new_payment.sum) {
					payment_sum.focus().parent().addClass("has-error")
					return
				} else {
					payment_sum.parent().removeClass("has-error")
				}

				// Установлена ли дата платежа?
				if (!$scope.new_payment.date) {
					payment_date.focus().parent().addClass("has-error")
					return
				} else {
					payment_date.parent().removeClass("has-error")
				}

				// редактирование платежа, если есть ID
				if ($scope.new_payment.id) {
					ajaxStart()
					$.post("ajax/paymentEdit", $scope.new_payment, function(response) {
						angular.forEach($scope.payments, function(payment, i) {
							if (payment.id == $scope.new_payment.id) {
								$scope.payments[i] = $scope.new_payment
								$scope.$apply()
							}
						})
						ajaxEnd()
						lightBoxHide()
					})
				} else {
				// иначе сохранение плтежа
					// Добавляем дополнительные данные в new_payment
					$scope.new_payment.user_login		= $scope.user.login
					$scope.new_payment.first_save_date	= moment().format('YYYY-MM-DD HH:mm:ss')
					$scope.new_payment.id_student		= $scope.student.id
					$scope.new_payment.id_user			= $scope.user.id

					ajaxStart()
					$.post("ajax/paymentAdd", $scope.new_payment, function(response) {
						$scope.new_payment.id = response;

						// Инициализация если не установлено
						$scope.payments = initIfNotSet($scope.payments);

						$scope.payments.push($scope.new_payment)

						$scope.new_payment = {id_status : 0}

						$scope.$apply()

						ajaxEnd()
						lightBoxHide()
					})
				}
			}

			// ссылка "удалить профиль ученика"
			setInterval(function() {
				if (($scope.contracts.length <= 0) && ($scope.payments.length <= 0)) {
					$("#delete-student").show()
				} else {
					$("#delete-student").hide()
				}
			}, 300)

			//  Удалить платеж
			$scope.deletePayment = function(index, payment) {
				bootbox.confirm("Вы уверены, что хотите удалить платеж?", function(result) {
					if (result === true) {
						$.post("ajax/deletePayment", {"id_payment": payment.id})
						$scope.payments.splice(index, 1)
						$scope.$apply()
					}
				})
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

		    // Удалить файл из догавара
		    $scope.deleteContractFile = function (contract, id) {
			    contract.files.splice(id, 1)
			    $.post("ajax/UploadFiles", {
					"id_contract":	contract.id,
					"files":		contract.files
				});
		    }

		    // расторжение договора
		    $scope.cancelContract = function(contract) {
			    $scope.current_contract = angular.copy(contract)
			    $scope.current_contract.cancelled = 1
			    lightBoxShow('cancelcontract');
		    }

		    // отменить расторжение
		    $scope.cancelCancel = function(contract) {
				bootbox.confirm("Восстановить договор?", function(result) {
					if (result === true) {
						contract.cancelled = 0
						contract.cancelled_reason = ""
						contract.cancelled_date	= ""

						$scope.current_contract = contract;
						$scope.$apply()

						$scope.addContractNew()
					}
				})
		    }

		    // проверить номер телефона
		    $scope.phoneCorrect = function(element) {
			    // пустой номер телефона – это тоже правильный номер телефона
			    if (!$("#" + element).val()) {
				    return false
			    }

			    // если есть нижнее подчеркивание, то номер заполнен не полностью
				not_filled = $("#" + element).val().match(/_/)
				return not_filled == null
		    }
		    
			// проверить номер телефона
		    $scope.isMobilePhone = function(element) {
			    phone = $("#" + element).val();
			    
			    // пустой номер телефона – это тоже правильный номер телефона
			    if (!phone) {
				    return false
			    }
				
				return !phone.indexOf("+7 (9")
		    }

		    // Превратить в файлаплоад
		    $scope.bindFileUpload = function(contract) {
				// загрузка файла договора
				$('#fileupload' + contract.id).fileupload({
					dataType: 'json',
					maxFileSize: 10000000, // 10 MB
					// начало загрузки
					send: function() {
						NProgress.configure({ showSpinner: true })
					},
					// во время загрузки
					progress: function (e, data) {
			            NProgress.set(data.loaded / data.total)
			        },
			        // всегда по окончании загрузки (неважно, ошибка или успех)
			        always: function() {
				        NProgress.configure({ showSpinner: false })
				        ajaxEnd()
			        },
			        done: function (i, response) {
						if (response.result !== "ERROR") {
							contract.files = initIfNotSet(contract.files)
							contract.files.push(response.result)
							$.post("ajax/UploadFiles", {
								"id_contract":	contract.id,
								"files":		contract.files
							});
							$scope.$apply()
						} else {
							notifyError("Ошибка загрузки")
						}
			        },
			        fail: function (e, data) {
						$.each(data.messages, function (index, error) {
							notifyError(error)
						})
			        }
			    })
		    }

		    $scope.minimizeStudent = function(minimized) {
			    console.log("berer")
			    $scope.student.minimized = minimized
			    $.post("ajax/MinimizeStudent", {"minimized": minimized, "id_student": $scope.student.id})
		    }


			$(document).ready(function() {
				
				$("#request-edit").on('keyup change', 'input, select, textarea', function(){
			        $scope.form_changed = true
			        $scope.$apply()
			    })
			    
			    $(".map-save-button, .bs-datetime").on("click", function() {
				    $scope.form_changed = true
			        $scope.$apply()
			    })
				
				// Добавляем JSON
				$("#freetime_json").val(JSON.stringify($scope.freetime));

				// Биндим загрузку к уже имеющимся дагаварам
				$.each($scope.contracts, function(index, contract) {
					$scope.bindFileUpload(contract)
				})

				// Биндим пару-время к свободному времени
				datePair()


				// Биндим удаление Напоминания
				$("#notificationtypes-select").on("change", function() {
					if (!$(this).val()) {
						$("#notification-date").val("").parent().hide()
						$("#notification-time").val("").parent().hide()
					} else {
						$("#notification-date").parent().show()
						$("#notification-time").parent().show()
					}
				})

				// если изначально напоминание не установлено, не отображаем поля
				if (!$("#notificationtypes-select").val()) {
					$("#notification-date").val("").parent().hide()
					$("#notification-time").val("").parent().hide()
				}
				
				// Кнопка сохранения
				$("#save-button").on("click", function() {
					// Проверяем все ли номера телефона заполнены
					has_errors = false
					
					$(".phone-masked").filter(function() {
						// если есть нижнее подчеркивание, то номер заполнен не полностью
						not_filled = $(this).val().match(/_/)

						if (not_filled !== null) {
							$(this).addClass("has-error").focus()
							notifyError("Номер телефона указан неполностью")
							has_errors = true
							return false
						} else {
							$(this).removeClass("has-error")
						}
					})

					// если в предварительной проверке были ошибки
					if (has_errors) {
						return false
					}

					// если установлено уведомнелие
					if ($("#notificationtypes-select").val()) {
						if (!$("input[name='Notification[date]']").val()) {
							$("input[name='Notification[date]']").addClass("has-error").focus()
							notifyError("Не установлена дата напоминания")
							return false
						} else {
							$("input[name='Notification[date]']").removeClass("has-error")
						}
						if (!$("input[name='Notification[time]']").val()) {
							$("input[name='Notification[time]']").addClass("has-error").focus()
							notifyError("Не установлено время напоминания")
							return false
						} else {
							$("input[name='Notification[time]']").removeClass("has-error")
						}
					}


					ajaxStart()
					$scope.saving = true
					$scope.$apply()

					data = $("#request-edit").serializeArray()
					console.log($scope.representative, data)
					$.post("requests/ajax/Save", data)
						.success(function() {
							if ($scope.redirect_after_save) {
								redirect("requests/edit/" + $scope.redirect_after_save)
							} else {
							//	notifySuccess("Данные сохранены")
							}
						})
						.error(function() {
							notifyError("Ошибка сохранения")
						})
						.complete(function() {
							if (!$scope.redirect_after_save) {
								$scope.saving = false
								$scope.form_changed = false
								$scope.$apply()
								ajaxEnd()
							}
						})
				});

				// дополнительный apply on document.ready
				$scope.$apply()
			})
		})
