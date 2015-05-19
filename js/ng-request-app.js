	angular.module("Request", ["ngAnimate"])
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
			$scope.new_payment = {id_status : 0}
			
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
			        done: function (i, response) {
				        console.log(i, response)
						if (response.result == "OK") {
							bootbox.alert("Договор загружен")
							$scope.contract_loaded = 1
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