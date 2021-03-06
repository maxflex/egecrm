	app = angular.module "Teacher", ["ngMap", 'angucomplete-alt']
		.config [
		  '$compileProvider'
		  ($compileProvider) ->
		    $compileProvider.aHrefSanitizationWhitelist /^\s*(https?|ftp|mailto|chrome-extension|sip|tel):/
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

		.filter 'toArray', ->
			(obj) ->
				arr = []
				$.each obj, (index, value) ->
					arr.push(value)
				return arr
		.controller 'JournalCtrl', ($scope, $timeout) ->
			$timeout -> $scope.loadData()
			$scope.grades = []

			$scope.loadData = ->
    			$scope.loading = true
    			$.post 'teachers/ajax/Journal', {
    				year: $scope.year
    				id_teacher: $scope.id_teacher
    				grades: $scope.grades
    			}, ((response) ->
    				$scope.dates = response.dates
    				$scope.students = response.students
    				$scope.result = response.result
    				$scope.name_colors = response.name_colors
    				$scope.loading = false
    				$scope.$apply()
    				return
    			), 'json'
    			return

  			$scope.formatDate = (d) ->
  				moment(d).format 'DD.MM.YY'

  			$scope.grayMonth = (date) ->
  				d = undefined
  				d = moment(date).format('M')
  				d = parseInt(d)
  				d % 2 == 1

  			$scope.yearLabel = (year) ->
  				year + '-' + parseInt(year) + 1 + ' уч. г.'

  			$scope.noMoreDates = (student_id, date) ->
  				date > Object.keys($scope.result[student_id]).sort().reverse()[0]

  			$scope.setYear = (year) ->
  				$scope.year = year
  				$scope.loadData()
  				return

  			$scope.emptyResult = -> !$scope.result or Object.keys($scope.result).length == 0

  			angular.element(document).ready ->
  				$('.watch-select').selectpicker()
  				set_scope 'Teacher'

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
			$scope.toBePaid = ->
				to_be_paid = 0
				$scope.Data.forEach (d) ->
					to_be_paid += (d.real_sum - d.payment_sum)
				to_be_paid.toFixed(2)

			angular.element(document).ready ->
				set_scope "Teacher"
		.controller "EditCtrl", ($scope, $timeout, PhoneService, GroupService, Workplaces, UserService) ->
			bindArguments $scope, arguments
			$scope.enum = review_statuses

			$scope.$watch "Teacher.id_head_teacher", (newVal, oldVal) ->
				if newVal isnt oldVal
					$.post "teachers/ajax/saveHeadTeacher",
						id_teacher: $scope.Teacher.id
						id_head_teacher: newVal

			$scope.getStudentsHint = (Lesson) ->
				student_names = Lesson.students.map (student_id) -> $scope.getStudentName(student_id)
				student_names.join("\n")

			# STATS
			$timeout ->
				$scope.stats_ec_loading = false
				$scope.search_stats =
					id_teacher: $scope.Teacher.id
					years: [($scope.academic_year - 2).toString(), ($scope.academic_year - 1).toString()]
					grades: ['9', '10', '11']
			$scope.filterStats = ->
				$scope.stats_ec_loading = true
				$.post "teachers/ajax/stats", $scope.search_stats, (response) ->
					$scope.stats_ec = response
					$scope.stats_ec_loading = false
					$scope.$apply()
				, 'json'

			# REPORTS
			_initReportsModule = ->
				$scope.search = if $.cookie("reports") then JSON.parse($.cookie("reports")) else {}
				$scope.search.id_teacher = $scope.Teacher.id
				$scope.filter()
				$(".single-select").selectpicker()

			$scope.loadReports = ->
				frontendLoadingStart()
				$.post "reports/AjaxGetReports",
					page: -1
					teachers: []
				, (response) ->
					frontendLoadingEnd()
					$scope.Reports  = response.data
					$scope.counts = response.counts
					$scope.$apply()
					$scope.refreshCounts()
				, "json"

			$scope.filter = ->
				delete $scope.Reports
				$.cookie("reports", JSON.stringify($scope.search), { expires: 365, path: '/' });
				$scope.loadReports()

			$scope.refreshCounts = ->
				$timeout ->
					$('.watch-select option').each (index, el) ->
		                $(el).data 'subtext', $(el).attr 'data-subtext'
		                $(el).data 'content', $(el).attr 'data-content'
		            $('.watch-select').selectpicker 'refresh'
		        , 100

			# REVIEWS
			$scope.enum = review_statuses
			$scope.enum_approved = review_statuses_approved

			_initReviewsModule = ->
				$scope.search_reviews = if $.cookie("reviews") then JSON.parse($.cookie("reviews")) else {}
				$scope.search_reviews.id_teacher = $scope.Teacher.id
				$scope.filterReviews()
				$(".single-select").selectpicker()

			$scope.loadReviews = ->
				frontendLoadingStart()
				$.post "ajax/GetReviews",
					page: -1
					teachers: []
				, (response) ->
					frontendLoadingEnd()
					$scope.Reviews  = response.data
					$scope.counts_review = response.counts
					$scope.$apply()
					$scope.refreshCounts()
				, "json"

			$scope.filterReviews = ->
				delete $scope.Reviews
				$.cookie("reviews", JSON.stringify($scope.search_reviews), { expires: 365, path: '/' });
				$scope.loadReviews()

			# AUTOCOMPLETE
			$scope.studentSelected = (Student) ->
				student_id = Student.originalObject.id
				return if $scope.modal_additional_lesson.students.indexOf(student_id) isnt -1
				$scope.modal_additional_lesson.students.push(student_id)
				console.log('selected student', Student)

			$scope.getStudentName = (id) ->
				student = _.findWhere($scope.Students, {id: id})
				fio = student.name.split(' ')
				fio[0] + ' ' + fio[1]

			$scope.deleteStudent = (index) -> $scope.modal_additional_lesson.students.splice(index, 1)

			#######

			$scope.reverseObjKeys = (obj) -> Object.keys(obj).reverse()

			$scope.yearLabel = (year) ->
				year + '-' + (parseInt(year) + 1) + ' уч. г.'

			$scope.setYear = (year) ->
				$scope.selected_year = year

			#
			# дополнительные занятия
			#

			$scope.addAdditionalLessonDialog = (additional_lesson = null) ->
				if additional_lesson is null
					$scope.modal_additional_lesson =
						students: []
						id_teacher: $scope.Teacher.id
						year: getYear()
						lesson_date: moment().format('DD.MM.YY')
				else
	                $scope.modal_additional_lesson = _.clone(additional_lesson)
	                $scope.modal_additional_lesson.lesson_date = moment($scope.modal_additional_lesson.lesson_date).format('DD.MM.YY')
				lightBoxShow('additional-lesson')

			$scope.saveAdditionalLesson = ->
				lightBoxHide()
				$scope.modal_additional_lesson.lesson_date = convertDate($scope.modal_additional_lesson.lesson_date)
				if $scope.modal_additional_lesson.id
					ajaxStart()
					$.post 'ajax/SaveAdditionalLesson', $scope.modal_additional_lesson, (response) ->
						index = _.findIndex($scope.AdditionalLessons, {id: $scope.modal_additional_lesson.id})
						$scope.AdditionalLessons[index] = response
						$scope.$apply()
						ajaxEnd()
					, 'json'
				else
					ajaxStart()
					$.post 'ajax/SaveAdditionalLesson', $scope.modal_additional_lesson, (response) ->
						$scope.AdditionalLessons.push(response)
						$scope.$apply()
						ajaxEnd()
					, 'json'

			$scope.deleteAdditionalLesson = ->
				bootbox.confirm 'Вы уверены, что хотите удалить доп. занятие?', (result) ->
					if result is true
						$.post 'ajax/deleteAdditionalLesson', 'id': $scope.modal_additional_lesson.id, ->
							$scope.AdditionalLessons = _.without($scope.AdditionalLessons, _.findWhere($scope.AdditionalLessons, {id: $scope.modal_additional_lesson.id}))
							$timeout -> $scope.$apply()
							lightBoxHide()

			$scope.getCabinet = (id) ->
	            _.findWhere($scope.all_cabinets, {id: parseInt(id)})



			#
			# / дополнительные занятия
			#

			$scope.addAdditionalPaymentDialog = ->
				$scope.new_additional_payment =
					id_teacher: $scope.Teacher.id
					year: getYear()
					date: moment().format('DD.MM.YY')
				lightBoxShow('additional-payment')

			menus = ['Groups', 'Reviews', 'Lessons', 'payments', 'Reports', 'Stats', 'Bars', 'TeacherAdditionalPayments']

			$scope.setMenu = (menu, complex_data) ->
				if menu == 1
					_initReviewsModule()
				if menu == 4
					_initReportsModule()
				else
					if menu == 5 then $scope.filterStats()
					$.each menus, (index, value) ->
						_loadData(index, menu, value, complex_data)
				$scope.current_menu = menu

			_postData = (menu) ->
				id_teacher: $scope.Teacher.id
				menu: menu

			_loadData = (menu, selected_menu, ngModel, complex_data) ->
				if $scope[ngModel] is undefined and menu is selected_menu
					$.post "teachers/ajax/menu", _postData(menu), (response) ->
						if complex_data
							_.each response, (value, field) ->
								$scope[field] = value
						else
							$scope[ngModel] = response
						 $timeout -> $('.watch-select').selectpicker()
						$scope.$apply()
					, "json"

			$scope.totalSum = (date) ->
				total_sum = 0
				$.each $scope.Lessons[$scope.selected_year], (d, items) ->
					return if d > date
					day_sum = 0
					items.forEach (item) -> day_sum += item.sum
					day_sum
					total_sum += day_sum
				total_sum

			$scope.yearDifference = (year) ->
				moment().format("YYYY") - year

			$scope.show_all_lessons = false
			$scope.getLessons = ->
				return $scope.Lessons if $scope.show_all_lessons
				_.filter $scope.Lessons, (Lesson) ->
					Lesson.date > $scope.academic_year + "-07-15"

			# @time-refactored @time-checked
			$scope.toggleFreetime = (day, id_time) ->
			  mode = if $scope.Bars.Freetime[day][id_time] == 'green' then 'Delete' else 'Add'
			  ajaxStart()
			  $.post 'ajax/' + mode + 'Freetime', {
			    'id_entity': $scope.Teacher.id
			    'type_entity': 'teacher'
			    'id_time': id_time
			  }, ->
			    ajaxEnd()
			    $scope.Bars.Freetime[day][id_time] = if mode == 'Add' then 'green' else 'empty'
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

			$scope.lessonsTotalSum = ->
				lessons_sum = 0
				if $scope.Lessons
					$.each $scope.Lessons, (index, value) ->
						lessons_sum += parseInt(value.price)
				lessons_sum

			$scope.lessonsTotalPaid = (from_lessons)->
				payments_sum = 0
				if from_lessons and $scope.Lessons
					$.each $scope.Lessons, (index, lesson) ->
						payments_sum += parseInt(payment.sum) for payment in lesson.payments
				else
					if $scope.payments
						$.each $scope.payments, (index, value) ->
							payments_sum += parseInt(value.sum)
				return payments_sum

			# солько нужно выплатить репетитору
			$scope.toBePaid = (from_lessons)->
				return if not ($scope.Lessons and $scope.Lessons.length)

				lessons_sum  = $scope.lessonsTotalSum()
				payments_sum = $scope.lessonsTotalPaid(from_lessons)

				lessons_sum - payments_sum

			# форматировать дату
			$scope.formatDate2 = (date) ->
			  dateOut = new Date(date)
			  dateOut

			$scope.dateFromCustomFormat = (date) ->
				date = date.split "."
				date = date.reverse()
				date = date.join "-"
				D = new Date(date)
				moment(D).format "D MMMM YYYY"

			$scope.confirmPayment = (payment) ->
				return if $scope.user_rights.indexOf(11) is -1
				payment.confirmed = if payment.confirmed then 0 else 1
				$.post 'ajax/confirmPayment',
					id: payment.id
					confirmed: payment.confirmed

			# Окно редактирования платежа
			$scope.editPayment = (payment) ->
				return if payment.confirmed and $scope.user_rights.indexOf(11) is -1
				$scope.new_payment = angular.copy payment
				loadMutualAccounts($scope.new_payment.id_status)
				lightBoxShow 'addpayment'

			$scope.editPaymentAdditional = (payment) ->
				$scope.new_additional_payment = angular.copy payment
				lightBoxShow 'additional-payment'

			$scope.$watch 'new_payment.id_status', (newVal, oldVal) -> loadMutualAccounts(newVal)

			loadMutualAccounts = (id_status) ->
				if parseInt(id_status) == 6
					$scope.mutual_accounts = undefined
					$.post "ajax/getLastAccounts",
						id_teacher: $scope.new_payment.entity_id
					, (response) ->
						$scope.mutual_accounts = response
						$scope.$apply()
					, 'json'

			# Показать окно добавления платежа
			$scope.addPaymentDialog = ->
				$scope.new_payment =
					id_status: 0
					year: getYear()
					entity_id: $scope.Teacher.id
				lightBoxShow 'addpayment'
				$scope.handleKeyPress()
				setTimeout ->
					$($("#addpayment select")[0]).focus()
				, 200

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
			$scope.addAdditionalPayment = ->
				if $scope.new_additional_payment.id
					ajaxStart()
					$.post 'ajax/PaymentAdditionalEdit', $scope.new_additional_payment, (response) ->
						angular.forEach $scope.TeacherAdditionalPayments, (payment, i) ->
							if payment.id == $scope.new_additional_payment.id
								$scope.TeacherAdditionalPayments[i] = $scope.new_additional_payment
								$scope.$apply()
						ajaxEnd()
						lightBoxHide()
				else
					ajaxStart()
					$.post 'ajax/PaymentAdditionalAdd', $scope.new_additional_payment, (response) ->
						$scope.TeacherAdditionalPayments = [] if not $.isArray($scope.TeacherAdditionalPayments)
						$scope.TeacherAdditionalPayments.push response
						$scope.$apply()
						ajaxEnd()
						lightBoxHide()
					, 'json'

			# Добавить платеж
			$scope.addPayment = ->
			  # Получаем элементы (я знаю, что по-хорошему нужно получить их один раз вне функции
			  # а не каждый раз, когда функция вызывается, искать их заново. Но забей. Хочу их внутри когда
			  payment_date = $('#payment-date')
			  payment_year	= $("#payment-year")
			  payment_category	= $("#payment-category")
			  payment_sum = $('#payment-sum')
			  payment_select = $('#payment-select')
			  payment_type = $('#paymenttypes-select')
			  payment_card = $('#payment-card-number')
			  payment_card_first_number = $("#payment-card-first-number")

			# Установлен ли способ оплаты
			  if not parseInt($scope.new_payment.id_status)
			    payment_select.focus().parent().addClass 'has-error'
			    return
			  else
			    payment_select.parent().removeClass 'has-error'
			    if parseInt($scope.new_payment.id_status) is 1
				    # if not $scope.new_payment.card_first_number
				    #     payment_card_first_number.focus().addClass 'has-error'
				    #     return
				    # else
				    #     payment_card_first_number.removeClass 'has-error'
				    if not $scope.new_payment.card_number
				        payment_card.focus().addClass 'has-error'
				        return
				    else
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
				# Установлен ли год платежа?
			  if not $scope.new_payment.year
			    payment_year.focus().parent().addClass("has-error")
			    return
			  else
			    payment_year.parent().removeClass("has-error")
				# Установлена ли категория платежа?
			  if not parseInt($scope.new_payment.category)
			    payment_category.focus().parent().addClass("has-error")
			    return
			  else
			    payment_category.parent().removeClass("has-error")

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
			    # иначе сохранение платежа
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
			      $scope.tobe_paid -= $scope.new_payment.sum if $scope.tobe_paid
			      $scope.new_payment = id_status: 0
			      $scope.$apply()
			      ajaxEnd()
			      lightBoxHide()
			      return
			    , 'json'
			  return

			deletePayment = ->
				bootbox.confirm 'Вы уверены, что хотите удалить платеж?', (result) ->
					if result is true
						$.post 'ajax/deletePayment', 'id_payment': $scope.new_payment.id, ->
							$scope.payments = _.without($scope.payments, _.findWhere($scope.payments, {id: $scope.new_payment.id}))
							$scope.tobe_paid += parseInt($scope.new_payment.sum) if $scope.tobe_paid
							$timeout -> $scope.$apply()
							lightBoxHide()

            # Удалить платеж
			$scope.deletePayment = ->
                return if $scope.new_payment.confirmed and $scope.user_rights.indexOf(11) is -1
                deletePayment()

            # Удалить платеж
			$scope.deletePaymentAdditional = ->
				bootbox.confirm 'Вы уверены, что хотите удалить доп. услугу?', (result) ->
					if result is true
						$.post 'ajax/deletePaymentAdditional', {'id_payment': $scope.new_additional_payment.id, 'id_teacher': $scope.Teacher.id}, ->
							$scope.TeacherAdditionalPayments = _.without($scope.TeacherAdditionalPayments, _.findWhere($scope.TeacherAdditionalPayments, {id: $scope.new_additional_payment.id}))
							$timeout -> $scope.$apply()
							lightBoxHide()

			$scope.formatDateMonthName = (date, full_year) ->
				moment(date).format "D MMMM YY" + (if full_year then 'YY' else '')

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

				switch window.location.hash
					when '#additional' then $scope.setMenu(7, true)

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

					$scope.Teacher.subjects_ec = []
					$("#subjects-select option:selected").each ->
						if $(@).val()
							$scope.Teacher.subjects_ec.push $(@).val()

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

			$scope.getGroupsYears = ->
				if $scope.Groups
					_.uniq _.pluck ang_scope.Groups, 'year'

			$scope.getReviewsYears = ->
				if $scope.Reviews
					_.uniq _.pluck ang_scope.Reviews, 'year'

		.controller "ListCtrl", ($scope, $timeout, $http, PhoneService, Workplaces) ->
			bindArguments $scope, arguments
			$scope.in_egecentr = localStorage.getItem('teachers_in_egecentr') or ''
			$scope.id_subject = localStorage.getItem('teachers_id_subject') or 0

			$timeout ->
				$("#filter-branches").selectpicker({noneSelectedText: "филиалы"}).selectpicker('refresh')
				$http.post("teachers/ajax/LoadAll").then (response) ->
					console.log(response.data)
					$scope.Teachers = response.data
					$timeout -> $scope.refreshCounts()
					# $timeout -> $('.filters select').selectpicker('refresh')

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
				subjects	= [$scope.id_subject]
				branches	= [$scope.filter_branch]
				(if $scope.in_egecentr is '' then true else Teacher.in_egecentr is parseInt($scope.in_egecentr)) and (if not $scope.id_subject then true else _.intersection(Teacher.subjects_ec, subjects.map(Number)).length) and (if not $scope.filter_branch then true else _.intersection(Teacher.branches, branches.map(Number)).length)

			$scope.getCount = (state, id_subject) ->
				subjects = [id_subject]
				branches = [$scope.filter_branch]
				_.filter($scope.Teachers, (Teacher) ->
					(if state is '' then true else Teacher.in_egecentr is parseInt(state)) and (if not id_subject then true else _.intersection(Teacher.subjects_ec, subjects.map(Number)).length) and (if not $scope.filter_branch then true else _.intersection(Teacher.branches, branches.map(Number)).length)
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
