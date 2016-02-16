	angular.module "Teacher", ["ngMap"]
		.config [
		  '$compileProvider'
		  ($compileProvider) ->
		    $compileProvider.aHrefSanitizationWhitelist /^\s*(https?|ftp|mailto|chrome-extension|sip):/
		    # Angular before v1.2 uses $compileProvider.urlSanitizationWhitelist(...)
		    return
		]
		.filter 'to_trusted', ['$sce', ($sce) ->
	        return (text) ->
	            return $sce.trustAsHtml(text)
		]
		.filter 'reverse', ->
			(items) ->
				if items
					return items.slice().reverse()
		.filter 'range', () ->
			return (input, total) ->
				total = parseInt total
				for i in [0...total] by 1
					input.push i
				input
		.controller "SalaryCtrl", ($scope) ->
			angular.element(document).ready ->
				set_scope "Teacher"
		.controller "EditCtrl", ($scope) ->

			$scope.picture_version = 1;
			bindFileUpload = ->
				# загрузка файла договора
				$('#fileupload').fileupload({
					formData:
						id_teacher: $scope.Teacher.id
					dataType: 'json',
					maxFileSize: 10000000, # 10 MB
					# начало загрузки
					send: ->
						NProgress.configure({ showSpinner: true })
					,
					# во время загрузки
					progress: (e, data) ->
					    NProgress.set(data.loaded / data.total)
					,
					# всегда по окончании загрузки (неважно, ошибка или успех)
					always: ->
					    NProgress.configure({ showSpinner: false })
					    ajaxEnd()
					,
					done: (i, response) ->
						if response.result.status isnt "ERROR"
							$scope.Teacher.has_photo = true
							$scope.picture_version++
							$scope.$apply()
						else
							notifyError(response.result.error)
					,
					fail: (e, data) ->
						$.each data.messages, (index, error) ->
							notifyError error
				})

			# солько нужно выплатить репетитору
			$scope.toBePaid = ->
				return if not $scope.Data.length

				lessons_sum = 0
				$.each $scope.Data, (index, value) ->
					lessons_sum += parseInt(value.teacher_price)

				payments_sum = 0
				$.each $scope.payments, (index, value) ->
					payments_sum += parseInt(value.sum)

				lessons_sum - payments_sum

			$scope.sipNumber = (number) ->
				number = number.toString()
				return "sip:" + number.replace(/[^0-9]/g, '')

			$scope.callSip = (element) ->
				number = $("#" + element).val()
				number = $scope.sipNumber(number)
				location.href = number

			# форматировать дату
			$scope.formatDate2 = (date) ->
			  dateOut = new Date(date)
			  dateOut


			$scope.confirmPayment = (payment) ->
			  bootbox.prompt
			    title: 'Введите пароль'
			    className: 'modal-password'
			    callback: (result) ->
			      if result == '363'
			        payment.confirmed = if payment.confirmed then 0 else 1
			        $.post 'ajax/confirmTeacherPayment',
			          id: payment.id
			          confirmed: payment.confirmed
			        $scope.$apply()
			      else if result != null
			        $('.bootbox-form').addClass('has-error').children().first().focus()
			        $('.bootbox-input-text').on 'keydown', ->
			          $(this).parent().removeClass 'has-error'
			          return
			        return false
			      return
			    buttons:
			      confirm: label: 'Подтвердить'
			      cancel: className: 'display-none'
			  return

			# Окно редактирования платежа
			$scope.editPayment = (payment) ->
			  if !payment.confirmed
			    $scope.new_payment = angular.copy(payment)
			    $scope.$apply()
			    lightBoxShow 'addpayment'
			    return
			  bootbox.prompt
			    title: 'Введите пароль'
			    className: 'modal-password'
			    callback: (result) ->
			      if result == '363'
			        $scope.new_payment = angular.copy(payment)
			        $scope.$apply()
			        lightBoxShow 'addpayment'
			      else if result != null
			        $('.bootbox-form').addClass('has-error').children().first().focus()
			        $('.bootbox-input-text').on 'keydown', ->
			          $(this).parent().removeClass 'has-error'
			          return
			        return false
			      return
			    buttons:
			      confirm: label: 'Подтвердить'
			      cancel: className: 'display-none'
			  return

			# Показать окно добавления платежа
			$scope.addPaymentDialog = ->
			  $scope.new_payment = id_status: 0
			  lightBoxShow 'addpayment'
			  return

			# Добавить платеж

			$scope.addPayment = ->
			  # Получаем элементы (я знаю, что по-хорошему нужно получить их один раз вне функции
			  # а не каждый раз, когда функция вызывается, искать их заново. Но забей. Хочу их внутри когда
			  payment_date = $('#payment-date')
			  payment_sum = $('#payment-sum')
			  payment_select = $('#payment-select')
			  payment_type = $('#paymenttypes-select')
			  # Установлен ли способ оплаты
			  if !$scope.new_payment.id_status
			    payment_select.focus().parent().addClass 'has-error'
			    return
			  else
			    payment_select.parent().removeClass 'has-error'
			  # Установлена ли сумма платежа?
			  if !$scope.new_payment.sum
			    payment_sum.focus().parent().addClass 'has-error'
			    return
			  else
			    payment_sum.parent().removeClass 'has-error'
			  # Установлена ли дата платежа?
			  if !$scope.new_payment.date
			    payment_date.focus().parent().addClass 'has-error'
			    return
			  else
			    payment_date.parent().removeClass 'has-error'
			  # редактирование платежа, если есть ID
			  if $scope.new_payment.id
			    ajaxStart()
			    $.post 'ajax/TeacherPaymentEdit', $scope.new_payment, (response) ->
			      angular.forEach $scope.payments, (payment, i) ->
			        if payment.id == $scope.new_payment.id
			          $scope.payments[i] = $scope.new_payment
			          $scope.$apply()
			        return
			      ajaxEnd()
			      lightBoxHide()
			      return
			  else
			    # иначе сохранение плтежа
			    # Добавляем дополнительные данные в new_payment
			    $scope.new_payment.user_login = $scope.user.login
			    $scope.new_payment.first_save_date = moment().format('YYYY-MM-DD HH:mm:ss')
			    $scope.new_payment.id_teacher = $scope.Teacher.id
			    $scope.new_payment.id_user = $scope.user.id
			    ajaxStart()
			    $.post 'ajax/TeacherPaymentAdd', $scope.new_payment, (response) ->
			      $scope.new_payment.id = response
			      # Инициализация если не установлено
			      $scope.payments = initIfNotSet($scope.payments)
			      $scope.payments.push $scope.new_payment
			      $scope.new_payment = id_status: 0
			      $scope.$apply()
			      ajaxEnd()
			      lightBoxHide()
			      return
			  return

			# Удалить платеж
			$scope.deletePayment = (index, payment) ->
			  if !payment.confirmed
			    bootbox.confirm 'Вы уверены, что хотите удалить платеж?', (result) ->
			      if result == true
			        console.log index
			        $.post 'ajax/deleteTeacherPayment', 'id_payment': payment.id
			        $scope.payments = _.without($scope.payments, _.findWhere($scope.payments, {id: payment.id}))
			        $scope.$apply()
			      return
			  else
			    bootbox.prompt
			      title: 'Введите пароль'
			      className: 'modal-password'
			      callback: (result) ->
			        if result == '363'
			          bootbox.confirm 'Вы уверены, что хотите удалить платеж?', (result) ->
			            if result == true
			              $.post 'ajax/deletePayment', 'id_payment': payment.id
			              $scope.payments = _.without($scope.payments, _.findWhere($scope.payments, {id: payment.id}))
			              $scope.$apply()
			            return
			        else if result != null
			          $('.bootbox-form').addClass('has-error').children().first().focus()
			          $('.bootbox-input-text').on 'keydown', ->
			            $(this).parent().removeClass 'has-error'
			            return
			          return false
			        return
			      buttons:
			        confirm: label: 'Подтвердить'
			        cancel: className: 'display-none'
			  return

			$scope.formatDate = (date) ->
				moment(date).format "D MMMM YY"
			$scope.formatTime = (time) ->
				time.substr(0, 5)

			$scope.coordinate_time = (date) ->
				moment(date).format("YYYY.MM.DD в HH:mm")

			$scope.dateToStart = (date) ->
				date = date.split "."
				date = date.reverse()
				date = date.join "-"

				D = new Date(date)

				moment().to D

			$scope.phoneCorrect		= phoneCorrect
			$scope.isMobilePhone 	= isMobilePhone

			angular.element(document).ready ->
				set_scope "Teacher"

				$scope.weekdays = [
					{"short" : "ПН", "full" : "Понедельник", 	"schedule": ["", "", $scope.time[1], $scope.time[2]]},
					{"short" : "ВТ", "full" : "Вторник", 		"schedule": ["", "", $scope.time[1], $scope.time[2]]},
					{"short" : "СР", "full" : "Среда", 			"schedule": ["", "", $scope.time[1], $scope.time[2]]},
					{"short" : "ЧТ", "full" : "Четверг", 		"schedule": ["", "", $scope.time[1], $scope.time[2]]},
					{"short" : "ПТ", "full" : "Пятница", 		"schedule": ["", "", $scope.time[1], $scope.time[2]]},
					{"short" : "СБ", "full" : "Суббота", 		"schedule": [$scope.time[3], $scope.time[4], $scope.time[5], $scope.time[6]]},
					{"short" : "ВС", "full" : "Воскресенье",	"schedule": [$scope.time[3], $scope.time[4], $scope.time[5], $scope.time[6]]}
				]

				$.each $scope.Teacher.branches, (index, branch) ->
					$scope.Teacher.branches[index] = branch.toString()

				# Заходим в преподавателя и хотим отправить ему смс. Девочки говорят, что она может появится, но спустя вечность.
				# А нам нужно чтобы он появлялась мгновенно
				setTimeout ->
					$scope.$apply()
				, 100

			$scope.toggleBanned = ->
				$scope.Teacher.banned = !$scope.Teacher.banned
				$scope.form_changed = true

			$scope.goToTutor = ->
				window.open "https://crm.a-perspektiva.ru/repetitors/edit/?id=#{$scope.Teacher.id_a_pers}", "_blank"

			$(document).ready ->
				bindFileUpload()

				$("#subjects-select").selectpicker
					noneSelectedText: "предметы"
					multipleSeparator: ", "

				$('#public-grades').selectpicker
					noneSelectedText: "классы"
					multipleSeparator: ", "

				$("#teacher-branches").selectpicker
					noneSelectedText: "удобные филиалы для преподавателя"

				$("#teacher-edit").on 'keyup change', 'input, select, textarea', ->
					$scope.form_changed = true
					$scope.$apply()

			$(".save-button").on "click", ->
					has_errors = false

					$(".phone-masked").filter ->
						not_filled = $(this).val().match(/_/)

						if not_filled isnt null
							$(this).addClass("has-error").focus()
							notifyError("Номер телефона указан неполностью")
							has_errors = true
							return false
						else
							$(this).removeClass("has-error")

					if has_errors
						return false

					$scope.Teacher.subjects = []
					$("#subjects-select option:selected").each ->
						if $(@).val()
							$scope.Teacher.subjects.push $(@).val()

					$scope.Teacher.branches = []
					$("#teacher-branches option:selected").each ->
						if $(@).val()
							$scope.Teacher.branches.push $(@).val()

					$scope.Teacher.public_grades = []
					$("#public-grades option:selected").each ->
						if $(@).val()
							$scope.Teacher.public_grades.push $(@).val()

					ajaxStart()
					$scope.saving = true
					$scope.$apply()

					$scope.Teacher.freetime = $scope.freetime
					$.post "teachers/ajax/save", $scope.Teacher, (response) ->
						console.log response
						if $scope.Teacher.id
							ajaxEnd()
							$scope.saving = false
							$scope.form_changed = false
							$scope.$apply()
						else
							redirect "teachers/edit/#{response}"

		.controller "ListCtrl", ($scope) ->
			# The amount of hidden teachers
			$scope.othersCount = ->
				_.where($scope.Teachers, {had_lesson: 0}).length

			$scope.smsDialog = smsDialogTeachers

			$scope.showHidden = ->
				$scope.show_others = !$scope.show_others

				if ($scope.show_others)
					$('html, body').animate({
				        scrollTop: $("#hidden-teachers-button").offset().top
				    }, 400)
				else
					$('html, body').animate({
				        scrollTop: $("#teachers-list").prop("scrollHeight") - 420
				    }, 400)

			$scope.deleteTeacher = (id_teacher, index) ->
				bootbox.confirm "Вы уверены, что хотите удалить преподавателя №#{id_teacher}?", (result) ->
					if result is true
						$scope.Teachers.splice index, 1
						$scope.$apply()
						$.post "teachers/ajax/delete", {id_teacher: id_teacher}
						console.log "here", index, id_teacher

			angular.element(document).ready ->
				set_scope 'Teacher'
				smsMode 4
