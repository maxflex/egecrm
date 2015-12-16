	angular.module("Payments", [])
		.filter('reverse', function() {
			return function(items) {
				if (items) {
					return items.slice().reverse();
				}
			};
		})
		.controller("LkTeacherCtrl", function($scope, $http) {
			
			$scope.formatDate = function(date) {
				return moment(date).format("D MMMM YYYY")
			}
			
			$scope.formatTime = function(time) {
				return time.substr(0, 5)
			}
			
			$scope.totalPaid = function() {
				sum = 0;
				$.each($scope.payments, function(i, payment){
					sum += payment.sum
				})
				return sum
			}
			
			$scope.totalEarned = function() {
				sum = 0;
				$.each($scope.Data, function(i, data){
					sum += data.teacher_price
				})
				return sum
			}
			
			$scope.toBePaid = function() {
				return $scope.totalEarned() - $scope.totalPaid()
			}
			
			angular.element(document).ready(function() {
				bootbox.prompt({
					title: "Для доступа к странице введите ваш пароль",
					className: "modal-password-bigger",
					callback: function(result) {
						$.ajax({
							url: "ajax/checkTeacherPass", 
							data: {password: result}, 
							dataType: "json",
							method: "post",
							success: function(response) {
								if (response == true) {
									$scope.password_correct = true;
									$.post("payments/ajaxLkTeacher", {}, function(response) {
										console.log(response)
										$scope.payments = response.payments 
										$scope.Data 	= response.Data
										$scope.loaded	= true; // data loaded
										$scope.$apply()
									}, "json")
								} else {
									$scope.password_correct = false;
								}
								$scope.$apply();
							},
							async: false,
						})
							
					},
					buttons: {
						confirm: {
							label: "Подтвердить"
						},
						cancel: {
							className: "display-none"
						}		
					}
				})
			})
		})
		.controller("ListCtrl", function($scope) {
			$scope.filter = 6;
			
			$scope.paymentsFilter = function(payment) {
				switch ($scope.filter) {
					case 1: {
						return payment.id_status == 5
					}
					case 2: {
						return payment.id_status == 4
					}
					case 3: {
						return payment.id_status == 2
					}
					case 4: {
						return payment.id_status == 1
					}
					case 5: {
						return !payment.confirmed
					}
					default: {
						return payment
					}
				}
			}
			
			$scope.confirmPayment = function(payment) {
				bootbox.prompt({
					title: "Введите пароль",
					className: "modal-password",
					callback: function(result) {
						if (result == "363") {
							payment.confirmed = payment.confirmed ? 0 : 1
							$.post("ajax/confirmPayment", {id: payment.id, confirmed: payment.confirmed})	
							$scope.$apply()
						} else if (result != null) {
							$('.bootbox-form').addClass('has-error').children().first().focus()
							$('.bootbox-input-text').on('keydown', function() {
								$(this).parent().removeClass('has-error')	
							})
							return false
						}
					},
					buttons: {
						confirm: {
							label: "Подтвердить"
						},
						cancel: {
							className: "display-none"
						}		
					}
				})
			}
			
			// Окно редактирования платежа
			$scope.editPayment = function(payment) {
				if (!payment.confirmed) {
					$scope.new_payment = angular.copy(payment)
					$scope.$apply()
					lightBoxShow('addpayment')
					return
				}
				bootbox.prompt({
					title: "Введите пароль",
					className: "modal-password",
					callback: function(result) {
						if (result == "363") {
							$scope.new_payment = angular.copy(payment)
							$scope.$apply()
							lightBoxShow('addpayment')		
						} else if (result != null) {
							$('.bootbox-form').addClass('has-error').children().first().focus()
							$('.bootbox-input-text').on('keydown', function() {
								$(this).parent().removeClass('has-error')	
							})
							return false
						}
					},
					buttons: {
						confirm: {
							label: "Подтвердить"
						},
						cancel: {
							className: "display-none"
						}		
					}
				})
			}

			// Показать окно добавления платежа
			$scope.addPaymentDialog = function() {
				$scope.new_payment = {id_status : 0}
				lightBoxShow('addpayment')	
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
			
			// Удалить платеж
			$scope.deletePayment = function(index, payment) {
				if (!payment.confirmed) {
					bootbox.confirm("Вы уверены, что хотите удалить платеж?", function(result) {
						if (result === true) {
							$.post("ajax/deletePayment", {"id_payment": payment.id})
							$scope.payments.splice(index, 1)
							$scope.$apply()
						}
					})
				} else {
					bootbox.prompt({
						title: "Введите пароль",
						className: "modal-password",
						callback: function(result) {
							if (result == "363") {
								bootbox.confirm("Вы уверены, что хотите удалить платеж?", function(result) {
									if (result === true) {
										$.post("ajax/deletePayment", {"id_payment": payment.id})
										$scope.payments.splice(index, 1)
										$scope.$apply()
									}
								})
							} else if (result != null) {
								$('.bootbox-form').addClass('has-error').children().first().focus()
								$('.bootbox-input-text').on('keydown', function() {
									$(this).parent().removeClass('has-error')	
								})
								return false
							}
						},
						buttons: {
							confirm: {
								label: "Подтвердить"
							},
							cancel: {
								className: "display-none"
							}		
						}
					})
				}
				
			}
						
			// форматировать дату
			$scope.formatDate = function(date){
		        var dateOut = new Date(date);
		        return dateOut;
		    };
		    
		    // неподтвержденные платежи по умолчанию
		    $scope.filter = 5
		    
			angular.element(document).ready(function() {
				set_scope("Payments")
				
				$.post("payments/AjaxGetPayments", {}, function(response) {
					$scope.payments = response 
					$scope.$apply()
				}, "json");
			})
		})