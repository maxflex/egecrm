	angular.module("Request", ["ngAnimate", "ngMap", "ui.bootstrap"])
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
			
			// Выбрать список
			$scope.changeList = function(request_status, push_history) {
				//  Если нажимаем на один и тот же список -- ничего не делаем
				if (request_status.id == $scope.chosen_list) {
					return	
				}
				
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
				}, "json")
			}
			
		})
		.controller("EditCtrl", function ($scope, $log) {	
			// значение "Платежи" по умолчанию (иначе подставляет пустое значение)
			$scope.new_payment = {id_status : 0}
			
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
			$scope.new_contract_id = -1;
			
			// анимация загрузки RENDER ANGULAR
			angular.element(document).ready(function() {
				$("#request-edit").css("opacity", 1)
				$("#panel-loading").hide()
				
				console.log($scope.student)
			})
			
			
			/**
			 * Показывать в свободном времени только дни, где есть свободное время
			 * 
			 */
			$scope.freetimeControl = function(day_number) {
				return $.grep($scope.freetime, function(v, i) {
					return (v.day == (day_number + 1) && !v.deleted)
				}).length > 0
			}
			
			
			/**
			 * Склеить заявки
			 * 
			 */
			$scope.glue = function() {
				ajaxStart()
				$.post("requests/ajax/GlueRequest", {id_request: $scope.id_request, id_student: $scope.id_student_glue}, function(response) {
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
			 * Печать договора 
			 * 
			 */
			$scope.printContract = function(id_contract) {
				printDiv("contract-print-" + id_contract);
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
					count = count + parseInt(subject.count)
				})
				return count	
			}
			
			// Передаем функция numToText() в SCOPE
			$scope.numToText = numToText;
			
			// Склонять имя в дательном падеже
			// https://github.com/petrovich/petrovich-js		
			$scope.contractPrintName = function(person) {
				var person = {
					first	: person.first_name,
					last	: person.last_name,
					middle	: person.middle_name,
				};
				
				// склоняем в дательный падеж
				person = petrovich(person, 'instrumental');
				
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
/*				if (contract.deleted) {
					bootbox.confirm("Восстановить договор?", function(result) {
						if (result === true) {
							contract.deleted = 0
							$scope.$apply()
						}	
					})
				} else {
*/
				bootbox.confirm("Удалить договор?", function(result) {
					if (result === true) {
						contract.deleted = 1
						$scope.$apply()
					}	
				})
//				}	
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
		    
		    // Удалить файл из догавара
		    $scope.deleteContractFile = function (contract, id) {
			    contract.files.splice(id, 1)
		    }
		    
		    // проверить номер телефона
		    $scope.phoneCorrect = function(element) {
			    // пустой номер телефона – это тоже правильный номер телефона
			    if (!$("#" + element).val()) {
				    return false
			    }	
			    	    
			    // если есть нижнее подчеркивание, то номер заполнен не полностью
				not_filled = $("#" + element).val().match(/_/)
				console.log($("#" + element).val(), not_filled)
				return not_filled == null
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
					
					data = $("#request-edit").serializeArray()
					$.post("requests/ajax/Save", data)
						.success(function() {
							notifySuccess("Данные сохранены")
						})
						.error(function() {
							notifyError("Ошибка сохранения")
						})
						.complete(function() {
							$scope.saving = false
							$scope.$apply()
							ajaxEnd()
						})
				});
				
				// дополнительный apply on document.ready
				$scope.$apply()
			})
		})