	angular.module("Payments", [])
		.controller("ListCtrl", function($scope) {
			$scope.confirmPayment = function(payment) {
				bootbox.prompt({
					title: "Введите пароль",
					className: "modal-password",
					callback: function(result) {
						if (result == "363") {
							payment.confirmed = 1
							$.post("ajax/confirmPayment", {id: payment.id})	
							$scope.$apply()
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
		})