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
		.filter 'hideZero', ->
			(item) ->
				if item > 0 then item else null

		.controller "FaqCtrl", ($scope) ->
			$scope.save = ->
				ajaxStart()
				$.post 'ajax/saveTeacherFaq',
					html: $scope.editor.getValue()
				, ->
					ajaxEnd()

			angular.element(document).ready ->
				$scope.editor = ace.edit("editor")
				$scope.editor.setOptions
					minLines: 43
					maxLines: 43
				$scope.editor.getSession().setMode("ace/mode/html")

		.controller "SalaryCtrl", ($scope) ->
			angular.element(document).ready ->
				set_scope "Teacher"
		.controller "EditCtrl", ($scope, $timeout, $http) ->
			$scope.enum = review_statuses

			menus = ['Groups', 'Reviews', 'Lessons', 'payments', 'Reports', 'Stats', 'Bars']

			$scope.setMenu = (menu) ->
				$.each menus, (index, value) ->
					_loadData(index, menu, value)
				$scope.current_menu = menu

			_postData = (menu) ->
				id_teacher: $scope.Teacher.id
				menu: menu

			_loadData = (menu, selected_menu, ngModel) ->
				if $scope[ngModel] is undefined and menu is selected_menu
					$.post "teachers/ajax/menu", _postData(menu), (response) ->
						$scope[ngModel] = response
						$scope.$apply()
					, "json"

			$scope.yearDifference = (year) ->
	            moment().format("YYYY") - year

			$scope.toggleFreetime = (day, time_id) ->
			  time_id++
			  if day >= 6
			    time_id += 2
			  mode = if $scope.Bars.Freetime[day][time_id] == 'green' then 'Delete' else 'Add'
			  $.post 'ajax/' + mode + 'Freetime', {
			    'id_entity': $scope.Teacher.id
			    'type_entity': 'teacher'
			    'day': day
			    'time_id': time_id
			  }, ->
			    $scope.Bars.Freetime[day][time_id] = if mode == 'Add' then 'green' else 'empty'
			    $scope.$apply()
			    return
			  return

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
				return if not $scope.Lessons.length

				lessons_sum = 0
				$.each $scope.Lessons, (index, value) ->
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
			      if result is null
			      else if hex_md5 result == payments_hash
			        payment.confirmed = if payment.confirmed then 0 else 1
			        $.post 'ajax/confirmPayment',
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
			    $scope.new_payment = angular.copy payment
			    $scope.$apply()
			    lightBoxShow 'addpayment'
			    return
			  bootbox.prompt
			    title: 'Введите пароль'
			    className: 'modal-password'
			    callback: (result) ->
			      if result is null
			      else if hex_md5 result == payments_hash
			        $scope.new_payment = angular.copy payment
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

			  $scope.handleKeyPress()
			  setTimeout ->
				  $($("#addpayment select")[0]).focus()
			  , 200

			  return
			$scope.handleKeyPress = ->
				$('#addpayment').on 'keydown', (e) ->
					if e.keyCode == 13
						if $('#payment-select').is(':focus')
							select_val = $('#payment-select').val();
							if select_val isnt '0'
								if select_val is '1'
									$('#payment-card').focus()
								else
									$('#payment-sum').focus()
						else
							if $('#payment-card').is(':focus')
								$('#payment-sum').focus()
							else
								if $('#payment-sum').is(':focus')
									$('#payment-date').focus()
								else
									if $('#payment-date').is(':focus')

										$scope.addPayment()
						e.preventDefault()

			# Добавить платеж
			$scope.addPayment = ->
			  # Получаем элементы (я знаю, что по-хорошему нужно получить их один раз вне функции
			  # а не каждый раз, когда функция вызывается, искать их заново. Но забей. Хочу их внутри когда
			  payment_date = $('#payment-date')
			  payment_sum = $('#payment-sum')
			  payment_select = $('#payment-select')
			  payment_type = $('#paymenttypes-select')
			  payment_card = $('#payment-card')
			  # Установлен ли способ оплаты
			  if !$scope.new_payment.id_status
			    payment_select.focus().parent().addClass 'has-error'
			    return
			  else
			    if parseInt($scope.new_payment.id_status) is 1 and !$scope.new_payment.card_number # если это карта
			      payment_card.focus().addClass 'has-error'
			      return
			    else
			      payment_select.parent().removeClass 'has-error'
			      payment_card.removeClass 'has-error'
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
			    $scope.new_payment.entity_type = 'TEACHER'
			    ajaxStart()
			    $.post 'ajax/PaymentEdit', $scope.new_payment, (response) ->
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
			    $scope.new_payment.entity_id = $scope.Teacher.id
			    $scope.new_payment.entity_type = 'TEACHER'
			    $scope.new_payment.id_type = 1 # тип платеж
			    $scope.new_payment.id_user = $scope.user.id
			    ajaxStart()
			    $.post 'ajax/PaymentAdd', $scope.new_payment, (response) ->
			      $scope.new_payment.id              = response.id
			      $scope.new_payment.document_number = response.document_number
			      # Инициализация если не установлено
			      $scope.payments = initIfNotSet($scope.payments)
			      $scope.payments.push $scope.new_payment
			      $scope.new_payment = id_status: 0
			      $scope.$apply()
			      ajaxEnd()
			      lightBoxHide()
			      return
			    , 'json'
			  return

			# Удалить платеж
			$scope.deletePayment = (index, payment) ->
			  if !payment.confirmed
			    bootbox.confirm 'Вы уверены, что хотите удалить платеж?', (result) ->
			      if result == true
			        console.log index
			        $.post 'ajax/deletePayment', 'id_payment': payment.id
			        $scope.payments = _.without($scope.payments, _.findWhere($scope.payments, {id: payment.id}))
			        $scope.$apply()
			      return
			  else
			    bootbox.prompt
			      title: 'Введите пароль'
			      className: 'modal-password'
			      callback: (result) ->
			        if result is null
			        else if hex_md5 result == payments_hash
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

			$scope.formatDateMonthName = (date) ->
				moment(date).format "D MMMM YY"

			$scope.formatDate = (date) ->
                dateOut = new Date(date)
                dateOut

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
					{"short" : "ПН", "full" : "Понедельник", 	"schedule": [$scope.time[1], $scope.time[2], $scope.time[7], $scope.time[8]]},
					{"short" : "ВТ", "full" : "Вторник", 		"schedule": [$scope.time[1], $scope.time[2], $scope.time[7], $scope.time[8]]},
					{"short" : "СР", "full" : "Среда", 			"schedule": [$scope.time[1], $scope.time[2], $scope.time[7], $scope.time[8]]},
					{"short" : "ЧТ", "full" : "Четверг", 		"schedule": [$scope.time[1], $scope.time[2], $scope.time[7], $scope.time[8]]},
					{"short" : "ПТ", "full" : "Пятница", 		"schedule": [$scope.time[1], $scope.time[2], $scope.time[7], $scope.time[8]]},
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

			# разбить "1 класс, 2 класс, 3 класс" на "1-3 классы"
			$scope.shortenGrades = ->
			    a = $scope.Teacher.grades
			    return if a.length < 1
			    limit = a.length - 1
			    combo_end = -1
			    pairs = []
			    i = 0
			    while i <= limit
			        combo_start = parseInt(a[i])

			        if combo_start > 11
			            i++
			            combo_end = -1
			            pairs.push $scope.Grades[combo_start]
			            continue

			        if combo_start <= combo_end
			            i++
			            continue

			        j = i
			        while j <= limit
			            combo_end = parseInt(a[j])
			            # если уже начинает искать по студентам
			            break if combo_end >= 11
			            break if parseInt(a[j + 1]) - combo_end > 1
			            j++
			        if combo_start != combo_end
			            pairs.push combo_start + '–' + combo_end + ' классы'
			        else
			            pairs.push combo_start + ' класс'
			        i++
			    $timeout ->
			        $('#public-grades').parent().find('.filter-option').html pairs.join ', '
			    return

			$(document).ready ->
				bindFileUpload()

				$("#subjects-select").selectpicker
					noneSelectedText: "предметы"
					multipleSeparator: "+"

				$('#public-grades').selectpicker
					noneSelectedText: "классы"
					multipleSeparator: ", "

				$scope.shortenGrades()

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

			$scope.emailDialog = (email) ->
				$('#email-history').html '<center class="text-gray">загрузка истории сообщений...</center>'
				$('.email-template-list').hide()
				html = ''
				$.post 'ajax/emailHistory'
				,  'email': email
				, (response) ->
					console.log response
					if response
						$.each response, (i, v) ->
							files_html = ''
							$.each v.files, (i, file) ->
								files_html += '<div class="sms-coordinates"><a target="_blank" href="files/email/' + file.name + '" class="link-reverse small">' + file.uploaded_name + '</a><span> (' + file.size + ')</span></div>'
							html += '<div class="clear-sms"><div class="from-them">' + v.message + '<div class="sms-coordinates">' + v.coordinates + '</div>' + files_html + '</div></div>'
						$('#email-history').html html
					else
						$('#email-history').html ''
				, 'json'
				$('#email-address').text email
				lightBoxShow 'email'

		.controller "ListCtrl", ($scope, $timeout) ->
			$scope.in_egecentr = localStorage.getItem('teachers_in_egecentr') or 0
			$scope.id_subject = localStorage.getItem('teachers_id_subject') or 0

			# The amount of hidden teachers
			$scope.othersCount = ->
				_.where($scope.Teachers, {had_lesson: 0}).length

			$scope.smsDialog = smsDialogTeachers

			$scope.changeState = ->
				localStorage.setItem('teachers_in_egecentr', $scope.in_egecentr)
				$scope.refreshCounts()

			$scope.changeSubjects = ->
				localStorage.setItem('teachers_id_subject', $scope.id_subject)
				$scope.refreshCounts()

			$scope.teachersFilter = (Teacher) ->
				subjects = [$scope.id_subject]
				(if not $scope.in_egecentr then true else Teacher.in_egecentr is (parseInt($scope.in_egecentr) or 1)) and (if not $scope.id_subject then true else _.intersection(Teacher.subjects, subjects.map(Number)).length)

			$scope.getCount = (state, id_subject) ->
				subjects = [id_subject]
				_.filter($scope.Teachers, (Teacher) ->
					(if not state then true else Teacher.in_egecentr is (parseInt(state) or 1)) and (if not id_subject then true else _.intersection(Teacher.subjects, subjects.map(Number)).length)
				).length

			$scope.refreshCounts = ->
				$timeout ->
					$('#state-select option, #subjects-select option').each (index, el) ->
		                $(el).data 'subtext', $(el).attr 'data-subtext'
		                $(el).data 'content', $(el).attr 'data-content'
		            $('#state-select, #subjects-select').selectpicker 'refresh'
		        , 100



			angular.element(document).ready ->
				set_scope 'Teacher'

				$("#subjects-select").selectpicker
					noneSelectedText: "предметы"
					multipleSeparator: "+"

				$("#state-select").selectpicker()

				smsMode 4

			$scope.totalHold = (grade) ->
				numerator = 0
				denominator = 0
				for Teacher in $scope.Teachers
					if grade
						if Teacher.loss_by_grade[grade]
							numerator += Teacher.total_lessons_by_grade[grade] - Teacher.loss_by_grade[grade]
							denominator += Teacher.total_lessons_by_grade[grade]
					else
						numerator += Teacher.total_lessons - Teacher.loss
						denominator += Teacher.total_lessons

				return 0 if not denominator
				Math.round 100 * numerator / denominator

			$scope.totalLessons = (grade) ->
				total_lessons = 0
				for Teacher in $scope.Teachers
					if grade
						if Teacher.fact_lesson_cnt_by_grade[grade]
							total_lessons += Teacher.fact_lesson_cnt_by_grade[grade]
					else
						total_lessons += Teacher.fact_lesson_total_cnt
				total_lessons
