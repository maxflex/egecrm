	testy = false

	angular.module "Group", ['ngAnimate', 'chart.js']
		.filter 'toArray', ->
			(obj) ->
				arr = []
				$.each obj, (index, value) ->
					arr.push(value)
				return arr
		.filter 'to_trusted', ['$sce', ($sce) ->
					return (text) ->
							return $sce.trustAsHtml(text)
		]
		.filter 'orderByDayNumber', () ->
			return (items, field, reverse) ->
				console.log items, field, reverse
				filtered = []
				angular.forEach items, (item) ->
					filtered.push(item)
				filtered.sort (a, b) ->
					return if a[field] > b[field] then 1 else -1
				if reverse then filtered.reverse()
				return filtered
		.filter 'range', () ->
			return (input, total) ->
				total = parseInt total
				for i in [0...total] by 1
					input.push i
				input
		.controller "JournalCtrl", ($scope) ->
			$scope.grayMonth = (date) ->
				d = moment(date).format("M")
				d = parseInt d
				return d % 2 is 1
			$scope.getInfo = (id_student,  date) ->
				_.findWhere($scope.LessonData, {id_entity: id_student, lesson_date: date})
			$scope.formatDate = (date) ->
				moment(date).format "DD.MM.YY"
			angular.element(document).ready ->
				set_scope "Group"
		.controller "LessonCtrl", ($scope) ->
			$scope.formatDate = (date) ->
				date = date.split "."
				date = date.reverse()
				date = date.join "-"
				D = new Date(date)
				moment(D).format "D MMMM YYYY г."

			$scope.timeUntilSave = ->
				date_now = new Date()
				date_lesson = new Date($scope.Schedule.date + " " + $scope.Schedule.time + ":00")
				diff = date_now.getTime() - date_lesson.getTime()
				data =
					seconds: 59 - (Math.floor(diff / 1000) - (Math.floor(diff / 1000 / 60) * 60))
					minutes: 40 - Math.floor(diff / 1000 / 60)

				if data.minutes < 0
					return true

				if data.minutes == 0 and data.seconds <=0
					return true
				else
					data

			until_save_interval = setInterval ->
				$scope.until_save = $scope.timeUntilSave()
				clearInterval(until_save_interval) if $scope.until_save is true
				$scope.$apply()
			, 1000


			$scope.editStudent = (Student) ->
				$scope.EditStudent = Student
				$scope.EditLessonData = angular.copy $scope.LessonData[$scope.EditStudent.id]
				clearSelect()
				lightBoxShow 'edit-student'

			$scope.saveStudent = ->
				$scope.LessonData[$scope.EditStudent.id] = $scope.EditLessonData
				$scope.students_not_filled = _.filter($scope.LessonData, (v) ->
												v and +(v.presence)
											).length isnt $scope.Group.Students.length
				lightBoxHide()

			$scope.registerInJournal = ->
				bootbox.confirm "Записать запись в журнал?", (result) ->
					if result is true
						if _.without($scope.LessonData, undefined).length isnt $scope.Group.Students.length
							bootbox.alert "Заполните данные по всем ученикам перед записью в журнал"
						else
							$scope.saving = true
							$scope.$apply()
							ajaxStart()
							$.post "groups/ajax/registerInJournal",
								id_group: 	$scope.id_group
								date:		$scope.date
								data:		$scope.LessonData
							, (response) ->
								ajaxEnd()
								$scope.saving = false
								$scope.registered_in_journal = true
								# $scope.form_changed = false
								$scope.$apply()

			$scope.changeRegisterInJournal = ->
				bootbox.confirm "Сохранить изменения?", (result) ->
					if result is true
						if _.without($scope.LessonData, undefined).length isnt $scope.Group.Students.length
							bootbox.alert "Заполните данные по всем ученикам перед записью в журнал"
						else
							$scope.saving = true
							$scope.$apply()
							ajaxStart()
							$.post "groups/ajax/registerInJournalWithoutSMS",
								id_group: 	$scope.id_group
								date:		$scope.date
								data:		$scope.LessonData
							, (response) ->
								ajaxEnd()
								$scope.saving = false
								$scope.registered_in_journal = true
								# $scope.form_changed = false
								$scope.$apply()

			angular.element(document).ready ->
				$scope.until_save = $scope.timeUntilSave()
				$scope.students_not_filled = true
				$scope.$apply()
				set_scope "Group"

		.controller "ScheduleCtrl", ($scope) ->
			$scope.schedulde_loaded = false

			$scope.formatDate = (date) ->
				moment(date).format "D MMMM YYYY г."

			$scope.getLine1 = (Schedule) ->
				moment(Schedule.date).format "D MMMM YYYY г."

			$scope.countNotCancelled = (Schedule) ->
				_.where(Schedule, { cancelled: 0 }).length

			$scope.studentsToLayeredScheduleTitle = (Schedule, students) ->
				title = ''
				[...,last] = students
				for student in students
					title += student.first_name + ' ' + student.last_name
					if last isnt student then title += ','
				Schedule.title = title
				$scope.$apply()

			# установка времени филиала и кабинета из настроек группы
			$scope.setParamsFromGroup = (Group) ->
				$.each $scope.Group.Schedule, (i, v) ->
					# if not v.time
					d = moment(v.date).format("d")
					d = parseInt d
					d = 7 if d is 0
					# если в этот день установлено расписание и время в группе. иначе не устанавливать
					# console.log Group.day_and_time, d, v.date, Group.day_and_time[d]
					if Group.day_and_time[d] isnt undefined
						key = Object.keys(Group.day_and_time[d])[0]
						v.time = Group.day_and_time[d][key]

					# устанавливаем филиалы и кабинеты для дат где не указаны филиал/кабинеты
					if Group.id_branch # and not v.id_branch
						v.id_branch = Group.id_branch
						$scope.changeBranch(v)

					if Group.id_branch and Group.cabinet
						v.cabinet = Group.cabinet if Group.cabinet

				$.post "groups/ajax/TimeFromGroup", {id_group: Group.id}
				, (response) ->
					if response
						$.each $scope.Group.Schedule, (i, v) ->
							if response[v.date]
								$scope.studentsToLayeredScheduleTitle(v, response[v.date])
				, "json"

			$scope.lessonCount = ->
				Object.keys($scope.Group.day_and_time).length

			$scope.changeFree = (Schedule) ->
				$.post "groups/ajax/changeScheduleFree",
					id: Schedule.id
					is_free: Schedule.is_free

			$scope.changeCabinet = (Schedule) ->
				$.post "groups/ajax/changeScheduleCabinet",
					date: Schedule.date
					id_group: $scope.Group.id
					cabinet: Schedule.cabinet

			$scope.changeBranch = (Schedule) ->
				$.post "groups/ajax/changeScheduleBranch",
					date: Schedule.date
					id_group: $scope.Group.id
					id_branch: Schedule.id_branch
					, (response) ->
						$scope.Cabinets[Schedule.id_branch] = response if not $scope.Cabinets[Schedule.id_branch]?
						$scope.$apply()
					, "json"


			$scope.setTime = (Schedule, event) ->
				$(event.target).hide()

				$(event.target)
					.parent()
					.children("input")
					.show()
					.on "changeTime, blur", (e) ->
						time = $(this).val()
						if time
							Schedule.time = time
							$.post "groups/ajax/AddScheduleTime", {time: time, date: Schedule.date, id_group: $scope.Group.id}
							, (response) ->
								if response
									$scope.studentsToLayeredScheduleTitle(Schedule, response)
							, "json"
							$scope.$apply()
						$(this)
							.hide()
							.parent()
							.children "span"
							.html if time then time else "не установлено"
							.show()
					.focus()


				return false

			$scope.inDate = (date, dates) ->
				moment(date).format("YYYY-MM-DD") in dates

			$scope.lessonStarted = (Schedule) ->
				lesson_time = new Date(Schedule.date + " " + Schedule.time).getTime()
				lesson_time < new Date().getTime()

			$scope.getInitParams = (el) ->
				month = parseInt $(el).attr "month"
				# year = if month >= 8 then parseInt moment().format "YYYY" else moment().add(1, "years").format "YYYY"
				# year = if month >= 8 then parseInt moment().subtract(1, "years").format "YYYY" else moment().format "YYYY"
				year = $scope.Group.year
				year++ if month <=8
				current_date = new Date "#{year}-#{month}-01"
				console.log(current_date)
				language: 'ru'
				startDate: current_date
				endDate: moment(current_date).endOf("month").toDate()
				multidate: true
				beforeShowDay: (d, inst) ->
					if $scope.inDate(d, $scope.past_lesson_dates)
						add_class = 'was-lesson disabled '
					if $scope.inDate(d, $scope.vocation_dates)
						add_class += ' vocation'
					if $scope.inDate(d, $scope.exam_dates.other_subject)
						add_class += ' exam'
					if $scope.inDate(d, $scope.exam_dates.this_subject)
						add_class += ' exam-subject'
					if $scope.inDate(d, $scope.cancelled_lesson_dates)
						add_class += ' cancelled'
					add_class

			$scope.monthName = (month) ->
				moment().month(month - 1).format "MMMM"

			$scope.dateChange = (e) ->
				return if not $scope.schedule_loaded
				# console.log clicked_date

				d = moment(clicked_date).format("YYYY-MM-DD")

				$scope.Group.Schedule = initIfNotSet $scope.Group.Schedule

				# check if date is in schedule
				t = $scope.Group.Schedule.filter (schedule) ->
					schedule.date is d

				if t.length is 0
					$scope.Group.Schedule.push
						date: d
						cancelled: 0
					$.post "groups/ajax/AddScheduleDate", {date: d, id_group: $scope.Group.id}
				else
# 					index = $scope.schedule.indexOf d
# 					$scope.schedule.splice index, 1
					$.each $scope.Group.Schedule, (i, v) ->
						if v isnt undefined
							if v.date is d
								if not v.cancelled
									v.title = false
									v.cancelled = 1
									$.post "groups/ajax/CancelScheduleDate", {date: d, id_group: $scope.Group.id}
								else
									$scope.Group.Schedule.splice i, 1
									$.post "groups/ajax/DeleteScheduleDate", {date: d, id_group: $scope.Group.id}
				$scope.$apply()

			angular.element(document).ready ->
				set_scope 'Group'
				$scope.weekdays = [
					{"short" : "ПН", "full" : "Понедельник", 	"schedule": [$scope.time[1], $scope.time[2], $scope.time[7], $scope.time[8]]},
					{"short" : "ВТ", "full" : "Вторник", 		"schedule": [$scope.time[1], $scope.time[2], $scope.time[7], $scope.time[8]]},
					{"short" : "СР", "full" : "Среда", 			"schedule": [$scope.time[1], $scope.time[2], $scope.time[7], $scope.time[8]]},
					{"short" : "ЧТ", "full" : "Четверг", 		"schedule": [$scope.time[1], $scope.time[2], $scope.time[7], $scope.time[8]]},
					{"short" : "ПТ", "full" : "Пятница", 		"schedule": [$scope.time[1], $scope.time[2], $scope.time[7], $scope.time[8]]},
					{"short" : "СБ", "full" : "Суббота", 		"schedule": [$scope.time[3], $scope.time[4], $scope.time[5], $scope.time[6]]},
					{"short" : "ВС", "full" : "Воскресенье",	"schedule": [$scope.time[3], $scope.time[4], $scope.time[5], $scope.time[6]]}
				]
				init_dates = []
				for schedule_date in $scope.Group.Schedule
					init_dates.push new Date schedule_date.date
# 					$scope.schedule.push
# 				console.log init_dates
				$(".calendar-month").each ->
					$(this)
						.datepicker $scope.getInitParams this
						.on "changeDate", $scope.dateChange
					m = $(this).attr "month"

					# loading schedule on calendar
					for d in init_dates
						month_number = moment(d).format("M")
						if month_number is m
							year 	= parseInt moment(d).format("YYYY")
							month 	= parseInt moment(d).format("M") - 1 # fix. because months are zero-based
							day 	= parseInt moment(d).format("D")
							# console.log year, month, day
							$(this).datepicker "_setDate", new Date(Date.UTC.apply(Date, [year, month, day]))
					# schedule loaded after 500 ms
					setTimeout ->
						$scope.schedule_loaded = true
						$scope.$apply()
					, 500

				$(".table-condensed").first().children("thead").css "display", "table-caption"
				# hack пустые строки
				$('tr:has(td:first.day.disabled.new),tr:has(td:last.day.disabled.old)').hide()

		.controller "EditCtrl", ($scope, $timeout) ->
			$timeout ->
				ajaxEnd()

			######## ВЫБОР ВРЕМЕНИ ########
			$scope.timeClick = (day, time) ->
				if $scope.timeChecked(day, time)
					$scope.Group.day_and_time[day] = _.reject $scope.Group.day_and_time[day], (t) ->
						t.id_time is time.id
					delete $scope.Group.day_and_time[day] if not $scope.Group.day_and_time[day].length
				else
					$scope.Group.day_and_time[day] = [] if $scope.Group.day_and_time[day] is undefined
					$scope.Group.day_and_time[day].push
						time: time
						id_time: time.id

			$scope.timeChecked = (day, time) ->
				$scope.Group.day_and_time[day] and $scope.getGroupTime(day, time) isnt undefined

			# найти конкретное время из Group.day_and_time по day, time.id
			$scope.getGroupTime = (day, time) ->
				_.findWhere($scope.Group.day_and_time[day], {id_time: time.id})

			$scope.dayAndTime = ->
				lightBoxShow "freetime"

			$scope.saveDayAndTime = ->
				lightBoxHide()
				justSave()
				$scope.updateCabinetBar(false)
				$scope.day_and_time_object = $scope.dayAndTimeObject()
				checkFreeCabinets()

			$scope.hasDayAndTime = ->
				Object.keys($scope.Group.day_and_time).length
			######## /ВЫБОР ВРЕМЕНИ ########

			rebindBlinking = ->
				blinking = $(".blink")
				blinking.removeClass "blink"
				setTimeout ->
					blinking.addClass "blink"
					, 50

			$scope.smsDialog2 = smsDialog2

			$scope.getSubject = (subjects, id_subject) ->
				_.findWhere subjects, {id_subject: id_subject}

			bindGroupsDroppable = ->
				$(".group-list").droppable
					tolerance: 'pointer',
					hoverClass: "request-status-drop-hover",
					drop: (event, ui) ->
						id_group	 = $(this).data("id")
						id_student	 = $(ui.draggable).data("id")

						Group = $scope.getGroup id_group

						if id_student in Group.students
							notifySuccess "Ученик уже в группе"
						else
							old_id_group = if $scope.Group and ($scope.Group.id isnt id_group) then $scope.Group.id else false
							$.post "groups/ajax/AddStudentDnd", {id_group: id_group, id_student: id_student, old_id_group: old_id_group}
							Group.students.push id_student
							$scope.removeStudent id_student, true
							$scope.$apply()

			$scope.search_groups =
				grade: ""
				id_branch: ""
				id_subject: ""
				year: ""

			$scope.groupsFilter = (Group) ->
				return false if Group.id is $scope.Group.id
				return (Group.grade is parseInt($scope.search_groups.grade) or not $scope.search_groups.grade) and
					(parseInt($scope.search_groups.id_branch) is Group.id_branch or not $scope.search_groups.id_branch) and
					(parseInt($scope.search_groups.year) is Group.year or not $scope.search_groups.year) and
					(parseInt($scope.search_groups.id_subject) is Group.id_subject or not $scope.search_groups.id_subject)

			bindDraggable = ->
				$(".student-line").draggable
					helper: 'clone'
					revert: 'invalid'
					start: (event, ui) ->
						$scope.is_student_dragging = true
						$scope.$apply()
						$(this).css "visibility", "hidden"
						$(ui.helper).addClass "single-dragging"
					stop: (event, ui) ->
						$scope.is_student_dragging = false
						$scope.$apply()
						$(this).css "visibility", "visible"

				$(".student-dragout").droppable
					tolerance: 'pointer'
					hoverClass: 'student-dragout-hover'
					drop: (event, ui) ->
						ui.draggable.remove()
						id_student	 = $(ui.draggable).data("id")
						$scope.removeStudent id_student
						$scope.$apply()


			checkFreeCabinets = ->
				$.post 'groups/ajax/checkFreeCabinets',
					id_group: $scope.Group.id
					day_and_time: $scope.day_and_time_object
					year: $scope.Group.year
				, (response) ->
					$scope.free_cabinets = response
					$scope.$apply()
					$timeout ->
						$('#group-branch').selectpicker('refresh')
				, 'json'

			$scope.changeYear = ->
				$scope.changeTeacher()
				$scope.reloadSmsNotificationStatuses()
				$scope.updateGroup
					year: $scope.Group.year

			$scope.enoughSmsParams = ->
				($scope.Group.year > 0 and $scope.Group.id_subject > 0 and $scope.Group.id_branch > 0 and $scope.Group.cabinet > 0 and $scope.Group.first_schedule and $scope.Group.id_subject > 0)

			$scope.changeCabinet = ->
				return if not $scope.Group.id
				$scope.reloadSmsNotificationStatuses()
				$scope.updateGroup
					cabinet: $scope.Group.cabinet
				$scope.updateCabinetBar()

			$scope.changeTeacher = ->
				return if not $scope.Group.id
				console.log 'changin teacher'
				$.post "groups/ajax/changeTeacher",
					id_group: $scope.Group.id
					id_subject: $scope.Group.id_subject
					id_branch: $scope.Group.id_branch
					day_and_time: $scope.Group.day_and_time
					id_teacher: $scope.Group.id_teacher
					year: $scope.Group.year
					students: $scope.Group.students
				, (response) ->
					console.log 'teacher changed', response
					$.each response.teacher_like_statuses, (id_student, id_status)->
						console.log 'hiiaa'
						$scope.getStudent(id_student).teacher_like_status = id_status
					$scope.getTeacher($scope.Group.id_teacher).agreement = response.agreement if $scope.Group.id_teacher
					$scope.$apply()
				, "json"

				$scope.updateTeacherBar()

			$scope.updateTeacherBar = ->
				return if $scope.Group.id_teacher is "0"
				ajaxStart()
				$.post "groups/ajax/GetTeacherBar",
					id_teacher: $scope.Group.id_teacher
					id_group: $scope.Group.id
				, (bar) ->
					ajaxEnd()
					$scope.getTeacher($scope.Group.id_teacher).bar = bar
					$scope.$apply()
					rebindBlinking()
				, "json"

			$scope.updateCabinetBar = (ajax_animation = true) ->
				ajaxStart() if ajax_animation
				$.post "groups/ajax/GetCabinetBar", {cabinet: $scope.Group.cabinet, id_group: $scope.Group.id}, (bar) ->
					ajaxEnd() if ajax_animation
					$scope.cabinet_bar = bar
					$scope.$apply()
					rebindBlinking()
				, "json"

			$scope.updateStudentBars = ->
				$.post "groups/ajax/GetStudentBars",
					student_ids: $scope.Group.students
					id_group: $scope.Group.id
				, (response) ->
					console.log response, 'students'
					$.each response, (id_student, bar) ->
						$scope.getStudent(id_student).bar = bar
					$scope.$apply()
					rebindBlinking()
				, "json"

			$scope.updateGroup = (data) ->
				$.post "groups/ajax/updateGroup",
					id_group: $scope.Group.id
					data: data

			$scope.to_students = true
			$scope.to_representatives = false
			$scope.to_teacher = false

			# disable "send" button if nothing selected
			$scope.$watch "[to_students, to_representatives]", (newValue, oldValue) ->
				if not newValue[0] and not newValue[1]
					$(".ajax-email-button").attr "disabled", "disabled"
				else
					$(".ajax-email-button").removeAttr "disabled"

			$scope.teachersFilter = (Teacher) ->
				return (parseInt($scope.Group.id_branch) in Teacher.branches or not $scope.Group.id_branch) and
					(parseInt($scope.Group.id_subject) in Teacher.subjects or not $scope.Group.id_subject)

			$scope.emptyDayFilter = (day_and_time) ->
				return _.filter day_and_time, (d) ->
					d.length isnt 0


			$scope.countSubjects = (Contract) ->
				Object.keys(Contract.subjects).length

			$scope.reloadSmsNotificationStatuses = ->
				$.post "groups/ajax/ReloadSmsNotificationStatuses",
					students: $scope.Group.students
					id_branch: $scope.Group.id_branch
					id_subject: $scope.Group.id_subject
					cabinet: $scope.Group.cabinet
					first_schedule: $scope.Group.first_schedule
				, (response) ->
					$.each response.sms_notification_statuses, (id_student, id_status)->
						$scope.getStudent(id_student).sms_notified = id_status
					$scope.$apply()
				, "json"

			$scope.reloadTests = ->
				$.post "groups/ajax/ReloadTests",
					students: $scope.Group.students
					id_subject: $scope.Group.id_subject
					grade: $scope.Group.grade
				, (response) ->
					$.each response, (id_student, Test)->
						$scope.getStudent(id_student).Test = Test
					$scope.$apply()
				, "json"

			$scope.smsNotify = (Student, event) ->
				$(event.target)
					.html "отправка..."
					.removeAttr "ng-click"
					.removeClass "pointer"
					.addClass "default"
				$.post "groups/ajax/smsNotify",
					id_student: Student.id
					id_branch: $scope.Group.id_branch
					id_subject: $scope.Group.id_subject
					cabinet: $scope.Group.cabinet
					first_schedule: $scope.Group.first_schedule
					id_group: $scope.Group.id
				, (response) ->
					Student.sms_notified = true
					$scope.$apply()

			$scope.addStudent = (Student, event) ->
				if Student.id not in $scope.Group.students
					el = $(event.target)
					el.hide()
					$("#student-adding-#{Student.id}").show()
					$.post "groups/ajax/inGroup",
						id_student: Student.id
						id_group: $scope.Group.id
						id_subject: $scope.Group.id_subject
					, (in_other_group) ->
						if not in_other_group
							console.log el
							el.show()
							$("#student-adding-#{Student.id}").hide()
							$scope.Group.students.push(Student.id)
							$scope.TmpStudents = initIfNotSet $scope.TmpStudents
							$scope.TmpStudents.push Student
							$scope.form_changed = true
							$scope.$apply()
							$scope.bindGroupStudentStatusChange()
							bindDraggable()
							justSave()
						else
							$("#student-adding-#{Student.id}").html "в другой группе"
					, "json"

			$scope.removeStudent = (id_student, remove_without_saving) ->
				$.each $scope.Group.students, (index, data) ->
					if data is id_student
						$scope.Group.students.splice index, 1
						$timeout ->
							justSave() if not remove_without_saving
						$scope.form_changed = true
						$scope.$apply()
				$.each $scope.TmpStudents, (index, data) ->
					if data isnt undefined and data.id is id_student
						$scope.TmpStudents.splice index, 1

			$scope.getStudent = (id_student) ->
				id_student = parseInt id_student
				Student = (i for i in $scope.TmpStudents when i.id is id_student)[0]

			$scope.getTeacher = (id_teacher) ->
				id_teacher = parseInt id_teacher
				Teacher = (i for i in $scope.Teachers when i.id is id_teacher)[0]

			$scope.search =
				grade: ""
				id_branch: ""
				id_subject: ""

			$scope.clientsFilter = (Student) ->
				return (Student.Contract.grade is parseInt($scope.search.grade) or not $scope.search.grade) and
					(parseInt($scope.search.id_branch) in Student.branches or not $scope.search.id_branch) and
					(Student.Contract.subjects and (parseInt($scope.search.id_subject) of Student.Contract.subjects or not $scope.search.id_subject))

			$scope.deleteGroup = (id_group) ->
				bootbox.confirm "Вы уверены, что хотите удалить группу №#{id_group}?", (result) ->
					if result is true
						ajaxStart()
						$.post "groups/ajax/delete", {id_group: id_group}, ->
							redirect "groups"

			$scope.changeBranch = ->
				if $scope.id_branch_cabinet
					arr = $scope.id_branch_cabinet.split('-')
					console.log('split', arr)
					$scope.Group.id_branch = arr[0]
					$scope.Group.cabinet = arr[1]
				else
					$scope.Group.id_branch = ''
					$scope.Group.cabinet = ''

				$scope.reloadSmsNotificationStatuses()
				$scope.updateGroup
					id_branch: $scope.Group.id_branch
					cabinet: $scope.Group.cabinet
				$scope.updateTeacherBar()
				$scope.updateCabinetBar(false)
				$scope.updateStudentBars()

			$scope.toggleReadyToStart = ->
				ready_to_start = if $scope.Group.ready_to_start then 0 else 1
				$.post "groups/ajax/toggleReadyToStart",
					id: $scope.Group.id
					ready_to_start: ready_to_start
				, ->
					$scope.Group.ready_to_start = ready_to_start
					$scope.$apply()



			$scope.addGroupsPanel = ->
				$scope.loadGroups() if not $scope.Groups
				$scope.add_groups_panel = not $scope.add_groups_panel
				$scope.search_groups.grade = $scope.Group.grade if not $scope.search_groups.grade and $scope.Group.grade
				$scope.search_groups.year = $scope.Group.year if not $scope.search_groups.year and $scope.Group.year
				$scope.search_groups.id_branch = $scope.Group.id_branch if not $scope.search_groups.id_branch and $scope.Group.id_branch
				$scope.search_groups.id_subject = $scope.Group.id_subject if not $scope.search_groups.id_subject and $scope.Group.id_subject
				$timeout ->
					$('#groups-branch-filter').selectpicker('refresh')


			$scope.subjectChange = ->
				return if not $scope.Group.id

				$scope.reloadSmsNotificationStatuses()
				$scope.reloadTests()
				$scope.updateGroup
					id_subject: $scope.Group.id_subject

				$scope.Group.id_teacher = 0
				$scope.changeTeacher()
				clearSelect()

			$scope.$watch "Group.grade", (newVal, oldVal) ->
				return if not $scope.Group.id

				if newVal isnt oldVal
					$scope.updateGroup
						grade: newVal

			$scope.$watch "Group.teacher_price", (newVal, oldVal) ->
				return if not $scope.Group.id

				if newVal isnt oldVal
					$scope.updateGroup
						teacher_price: newVal

			$scope.$watch "Group.level", (newVal, oldVal) ->
				return if not $scope.Group.id

				if newVal isnt oldVal
					$scope.updateGroup
						level: newVal

			$scope.$watch "Group.ended", ->
				return if not $scope.Group.id

				$scope.updateGroup
					ended: $scope.Group.ended

					$scope.updateTeacherBar()
					$scope.updateCabinetBar(false)
					$scope.updateStudentBars()


			$scope.loading_groups = false
			$scope.loadGroups = ->
				return if not $scope.Group.id
				$scope.Groups = false
				$scope.loading_groups = true
				$.post "groups/ajax/getGroups", {}, (response) ->
						$scope.loading_groups = false
						$scope.Groups = response
						$scope.$apply()
						bindGroupsDroppable()
				, "json"

			angular.element(document).ready ->
				set_scope "Group"
				$scope.$apply()

				if $scope.Group.Comments is false
					$scope.Group.Comments = []
				frontendLoadingEnd()

			$scope.form_changed = false
			$scope.saving = false
			$(document).ready ->
				emailMode 2
				smsMode 2
				bindDraggable()
				$("#group-cabinet").selectpicker()
				set_scope "Group"

			# save without notice
			justSave = ->
				$.post "groups/ajax/save", $scope.Group
		.controller "ListCtrl", ($scope, $timeout) ->
			$scope.updateCache = ->
				ajaxStart()
				$.post "groups/ajax/UpdateCacheAll", {}, ->
					redirect "groups"

			# from newer version of angular
			angular.merge = (s1,s2) ->
				$.extend(true, s1, s2)

			$scope.series = ["договоров"]
			$scope.datasetOverride = [
		        type: 'bar'
		        backgroundColor: 'rgba(51,122,183,.75)'
		        borderColor: 'rgba(51,122,183,.75)'
		        borderWidth: 0
			]
			$scope.options =
				scaleOverride: true
				scaleIntegersOnly: true
				scales:
					yAxes:
						[
							ticks:
								min: 0
								stepSize: 1
						]
			# #337ab7
			$scope.createHelper = ->
				lightBoxShow 'contract-stats'
				$scope.create_helper_data = null
				$.post "ajax/GroupCreateHelper",
					year: $scope.search.year
					subjects: $scope.search.subjects
					grade: $scope.search.grade
				, (response) ->
					$scope.create_helper_data = response
					# chart data
					$scope.labels = _.keys(response)
					$scope.data = [_.values(response)]
					$scope.$apply()
				, "json"

			$scope.getMonthByNumber = (n) ->
				moment().month(n - 1).format("MMMM")

			$scope.getTeacher = (id) ->
				_.find($scope.Teachers, {id: parseInt(id)})

			$scope.changeBranch = ->
				$("#group-cabinet").attr "disabled", "disabled"
				ajaxStart()
				$.post "groups/ajax/getCabinet", {id_branch: $scope.search.id_branch}, (cabinets) ->
					ajaxEnd()
					$scope.Cabinets = cabinets
					$scope.search.cabinet = 0
					$("#group-cabinet").removeAttr "disabled"

					$scope.$apply()
					clearSelect()
				, "json"


			$scope.order_reverse = false
			$scope.orderByTime = ->
				$scope.Groups.sort (a, b) ->
					day_index_1 = Object.keys(a.day_and_time)[0]
					day_index_2 = Object.keys(b.day_and_time)[0]

					if day_index_1 is undefined
						day_index_1 = -1

					if day_index_2 is undefined
						day_index_2 = -1

					if day_index_1 > day_index_2
						return 1
					else if day_index_2 > day_index_1
						return -1
					else
						a.day_and_time[day_index_1] = initIfNotSet a.day_and_time[day_index_1]
						b.day_and_time[day_index_2] = initIfNotSet b.day_and_time[day_index_2]

						a.day_and_time[day_index_1] = objectToArray a.day_and_time[day_index_1]
						b.day_and_time[day_index_2] = objectToArray b.day_and_time[day_index_2]

						if a.day_and_time[day_index_1] > b.day_and_time[day_index_2]
							return 1
						else
							return -1

				if $scope.order_reverse
					$scope.Groups.reverse()

				$scope.order_reverse = !$scope.order_reverse

			$scope.orderByStudentCount = ->
				$scope.Groups.sort (a, b) ->
					a.students.length - b.students.length

				if $scope.order_reverse
					$scope.Groups.reverse()

				$scope.order_reverse = !$scope.order_reverse

			$scope.orderByCabinet = ->
				$scope.Groups.sort (a, b) ->
					a.CabinetInfo.number - b.CabinetInfo.number

				if $scope.order_reverse
					$scope.Groups.reverse()

				$scope.order_reverse = !$scope.order_reverse

			$scope.orderByFirstLesson = ->
				$scope.Groups.sort (a, b) ->
					a.first_schedule - b.first_schedule

				if $scope.order_reverse
					$scope.Groups.reverse()

				$scope.order_reverse = !$scope.order_reverse

			$scope.orderByDaysBeforeExam = ->
				$scope.Groups.sort (a, b) ->
					a.days_before_exam - b.days_before_exam

				if $scope.order_reverse
					$scope.Groups.reverse()

				$scope.order_reverse = !$scope.order_reverse

			$scope.inDayAndTime2 = (time, freetime) ->
				return false if freetime is undefined
				freetime = objectToArray freetime
				return $.inArray(time, freetime) >= 0

			$scope.search =
				grade: ""
				id_branch: ""
				subjects: []
				id_teacher: ""
				cabinet: 0

			$scope.search2 =
				grades: []
				branches: []
				id_subject: ""

			$scope.groupsFilter = (Group) ->
				#time filtering
				if $scope.search.ntime and $scope.search.ntime.length
					time_correct = false
					$.each $scope.search.ntime, (index, time) ->
						t = time.split "-"
						day = t[0]
						time_index = t[1]
						if Group.day_and_time[day] isnt undefined and Group.day_and_time[day][time_index] isnt undefined
							time_correct = true
							return
				else
					time_correct = true
				Group.id_subject = Group.id_subject || 0
				return time_correct and (Group.grade is parseInt($scope.search.grade) or not $scope.search.grade) and
					(parseInt($scope.search.id_branch) is Group.id_branch or not $scope.search.id_branch) and
					($scope.search.year is Group.year or not $scope.search.year) and
					((Group.id_subject.toString() in $scope.search.subjects) or $scope.search.subjects.length is 0) and
					(parseInt($scope.search.id_teacher) is parseInt(Group.id_teacher) or not $scope.search.id_teacher) and
					(parseInt($scope.search.cabinet) is parseInt(Group.cabinet) or not parseInt($scope.search.cabinet))

			$scope.groupsFilter2 = (Group) ->
				return true if not Group.hasOwnProperty "grade"

				return (String(Group.grade) in $scope.search2.grades or $scope.search2.grades.length == 0) and
					(String(Group.branch) in $scope.search2.branches or $scope.search2.branches.length == 0) and
					(Group.subject is parseInt($scope.search2.id_subject) or not $scope.search2.id_subject)

			filterBranches = (Student) ->
				_.intersection($scope.search2.branches.map(Number), Student.branches).length > 0

			$scope.studentsWithNoGroupFilter = (Student) ->
				return (String(Student.grade) in $scope.search2.grades or $scope.search2.grades.length == 0) and
					($scope.search2.branches.length is 0 or filterBranches(Student)) and
					(Student.id_subject is parseInt($scope.search2.id_subject) or not $scope.search2.id_subject) and
					(Student.year is parseInt($scope.search2.year) or not $scope.search2.year) and
					(Student.level is parseInt($scope.search2.level) or not $scope.search2.level)


			$scope.inGroupDay = (weekday, Group) ->
				weekday++
				days = []
				group_days = Object.keys(Group.day_and_time)
				$.each group_days, (index, day) ->
					day = parseInt(day)
					days.push day if days.indexOf(day) is -1
				return if days.indexOf(weekday) is -1 then false else true

			# группировка по дням недели
			$scope.inGroupDays = (weekday) ->
				weekday++
				Groups = $scope.$eval 'Groups | filter:groupsFilter'
				days = []
				$.each Groups, (index, Group) ->
					group_days = Object.keys(Group.day_and_time)
					$.each group_days, (index, day) ->
						day = parseInt(day)
						days.push day if days.indexOf(day) is -1
				return if days.indexOf(weekday) is -1 then false else true

			$scope.dateToStart = (date) ->
				return "" if date is null
				date = date.split "."
				date = date.reverse()
				date = date.join "-"

				D = new Date(date)

				moment().to D

			$scope.$watchCollection "search2", (newValue, oldValue) ->
				$scope.Groups2 = if newValue.branches.length > 0 then $scope.GroupsFull else $scope.GroupsShort
				if $scope.Groups2 isnt undefined and $scope.Groups2.length > 0
					if $scope.Groups2[$scope.Groups2.length - 1].hasOwnProperty "grade"
						$scope.Groups2.push
							Students: []
				setTimeout ->
					bindDraggable2()
				, 100

			$scope.search_student =
				grade: ""
				id_branch: ""
				id_subject: ""

			$scope.clientsFilter = (Student) ->
				return (Student.Contract.grade is parseInt($scope.search_student.grade) or not $scope.search_student.grade) and
					(parseInt($scope.search_student.id_branch) in Student.branches or not $scope.search_student.id_branch) and
					(Student.Contract.subjects and (parseInt($scope.search_student.id_subject) of Student.Contract.subjects or not $scope.search_student.id_subject))

			$scope.getGroup = (id_group) ->
				Group = (i for i in $scope.Groups when i.id is id_group)[0]

			$scope.getSubject = (subjects, id_subject) ->
				_.findWhere subjects, {id_subject: id_subject}


			bindDraggable2 = ->
				$(".student-line").draggable
					helper: 'clone'
					revert: 'invalid'
					start: (event, ui) ->
						$(this).css "visibility", "hidden"
						$(ui.helper).addClass "tr-helper"
					stop: (event, ui) ->
						$(this).css "visibility", "visible"

				$(".group-list").droppable
					tolerance: 'pointer',
					hoverClass: "request-status-drop-hover",
					drop: (event, ui) ->
						id_group	 = $(this).data("id")
						id_student	 = $(ui.draggable).data("id")

						Group = $scope.getGroup id_group

						if id_student in Group.students
							notifySuccess "Ученик уже в группе"
						else
							$.post "groups/ajax/AddStudentDnd", {id_group: id_group, id_student: id_student}
							Group.students.push id_student
							$scope.$apply()

							student_group_index = $(ui.draggable).data "group-index"

							ui.draggable.remove()
							table = $("#group-index-#{student_group_index}")
							if table.find("tr").length <= 1
								table.remove()


				$(".group-list-2").droppable
					tolerance: 'pointer',
					hoverClass: "border-dashed-droppable-hover",
					activeClass: "border-dashed-droppable",
					drop: (event, ui) ->
						group_index	 = $(this).data "index"
						student_group_index = $(ui.draggable).data "group-index"

						console.log group_index, student_group_index

						# if dropping to self
						return if group_index is student_group_index

						Student		 = $(ui.draggable).data "student"


						Groups = $scope.$eval "Groups2 | filter:groupsFilter2"
						Group = Groups[group_index]

						# check if student is already in group
						in_group = false
						Group.Students = initIfNotSet Group.Students
						$.each Group.Students, (index, S) ->
							in_group = true if S.id is Student.id

						if in_group
							notifySuccess "Ученик уже в группе"
						else
	# 						console.log Group, Group.Students
							Group.Students = objectToArray Group.Students
							Group.Students.push Student
							ui.draggable.remove()
							table = $("#group-index-#{student_group_index}")
							testy = table
							if table.find("tr").length <= 1
								table.remove()
						$scope.$apply()
						bindDraggable2()

			$scope.students_picker = false
			$scope.search2 =
				grades: ""
				branches: ""
				id_subject: ""
				year: ""
				level: ""

			$scope.loadStudentPicker = ->
				$scope.students_picker = true
				$scope.search2.grades = [$scope.search.grade] if not $scope.search2.grades and $scope.search.grade
				$scope.search2.year = $scope.search.year if not $scope.search2.year and $scope.search.year
				$scope.search2.branches = [$scope.search.id_branch] if not $scope.search2.branches and $scope.search.id_branch
				$scope.search2.id_subject = $scope.search.subjects[0] if not $scope.search2.id_subject and $scope.search.subjects and $scope.search.subjects.length
				if not $scope.search2.level and $scope.search.level
					$scope.search2.level = if $scope.search.level is '5' then '1' else '0'
				$("html, body").animate { scrollTop: $(document).height() }, 1000
				$timeout ->
					$('#group-branch-filter2').selectpicker('refresh')
					$('#grades-select2').selectpicker('refresh')
					$('#external-filter').selectpicker('refresh')

				$.post "ajax/StudentsWithNoGroup", {}, (response) ->
					$scope.StudentsWithNoGroup = response
					$scope.$apply()
					bindDraggable2()
				, "json"

			$scope.yearLabel = (year) ->
				year + '-' + (parseInt(year) + 1) + ' уч. г.'

			$scope.refreshCounts = ->
				$timeout ->
					$('.watch-select option').each (index, el) ->
		                $(el).data 'subtext', $(el).attr 'data-subtext'
		                $(el).data 'content', $(el).attr 'data-content'
		            $('.watch-select').selectpicker 'refresh'
		        , 100

			$scope.branchCabinetFilter = ->
				ids = $scope.search.branch_cabinet.split('-')
				$scope.search.id_branch = ids[0]
				$scope.search.cabinet = ids[1]
				$scope.$apply()
				$scope.filter()

			$scope.filter = ->
				$.cookie("groups", JSON.stringify($scope.search), { expires: 365, path: '/' });
				$scope.current_page = 1
				$scope.getByPage($scope.current_page)

			# Страница изменилась
			$scope.pageChanged = ->
				console.log $scope.currentPage
				window.history.pushState {}, '', 'groups/?page=' + $scope.current_page if $scope.current_page > 1
				# Получаем задачи, соответствующие странице и списку
				$scope.getByPage($scope.current_page)

			$scope.getByPage = (page) ->
				$scope.Groups = undefined
				frontendLoadingStart()
				$.post "groups/ajax/get",
					page: page
				, (response) ->
					frontendLoadingEnd()
					$scope.Groups  = response.data
					$scope.teacher_ids = response.teacher_ids
					$scope.counts = response.counts
					$scope.$apply()
					bindDraggable2() if $scope.students_picker
					$scope.refreshCounts()
				, "json"

			$scope.teachersFilter2 = (Teacher) ->
				return true if $scope.teacher_ids is undefined
				return true if (Teacher.id in $scope.teacher_ids or Teacher.id is parseInt($scope.search.id_teacher))
				return false

			$scope.getGrades = (Grades) ->
				console.log 'grades', Grades
				return Grades

			$(document).ready ->
				try
					if $("#subjects-select").length
						$("#subjects-select").selectpicker
							noneSelectedText: "предметы"
							multipleSeparator: '+'

					if $("#time-select").length
						$("#time-select").selectpicker
							noneSelectedText: "время занятия"

					$("#group-branch-filter2").selectpicker
						noneSelectedText: "филиалы"

					$("#grades-select2").selectpicker
						noneSelectedText: "класс"
						multipleSeparator: ", "
				catch error

			angular.element(document).ready ->
				set_scope "Group"
				$scope.weekdays = [
					{"short" : "ПН", "full" : "Понедельник", 	"schedule": [$scope.time[1], $scope.time[2], $scope.time[7], $scope.time[8]]},
					{"short" : "ВТ", "full" : "Вторник", 		"schedule": [$scope.time[1], $scope.time[2], $scope.time[7], $scope.time[8]]},
					{"short" : "СР", "full" : "Среда", 			"schedule": [$scope.time[1], $scope.time[2], $scope.time[7], $scope.time[8]]},
					{"short" : "ЧТ", "full" : "Четверг", 		"schedule": [$scope.time[1], $scope.time[2], $scope.time[7], $scope.time[8]]},
					{"short" : "ПТ", "full" : "Пятница", 		"schedule": [$scope.time[1], $scope.time[2], $scope.time[7], $scope.time[8]]},
					{"short" : "СБ", "full" : "Суббота", 		"schedule": [$scope.time[3], $scope.time[4], $scope.time[5], $scope.time[6]]},
					{"short" : "ВС", "full" : "Воскресенье",	"schedule": [$scope.time[3], $scope.time[4], $scope.time[5], $scope.time[6]]}
				]
				$scope.search = if $.cookie("groups") then JSON.parse($.cookie("groups")) else {}
				$scope.current_page = $scope.currentPage
				$scope.pageChanged()
				$(".single-select").selectpicker()
				setTimeout ->
					$scope.$apply()
				, 25
				frontendLoadingEnd()
		.controller "StudentListCtrl", ($scope) ->
			console.log 'init'
		.controller "TeacherListCtrl", ($scope) ->
			console.log 'init'
			$scope.weekdays = [
				{"short" : "ПН"},
				{"short" : "ВТ"},
				{"short" : "СР"},
				{"short" : "ЧТ"},
				{"short" : "ПТ"},
				{"short" : "СБ"},
				{"short" : "ВС"}
			]
