	angular.module "Group", []
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
		.controller "LessonCtrl", ($scope) ->
			$scope.formatDate = (date) ->
				date = date.split "."
				date = date.reverse()
				date = date.join "-"
				D = new Date(date)
				moment(D).format "D MMMM YYYY г."
				
			$scope.editStudent = (Student) ->
				$scope.EditStudent = Student
				$scope.EditLessonData = angular.copy $scope.LessonData[$scope.EditStudent.id]
				clearSelect()
				lightBoxShow 'edit-student'
			
			$scope.saveStudent = ->
				$scope.LessonData[$scope.EditStudent.id] = $scope.EditLessonData
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
			
			angular.element(document).ready ->
				set_scope "Group"
				
		.controller "ScheduleCtrl", ($scope) ->
			$scope.weekdays = [
				{"short" : "ПН", "full" : "Понедельник", 	"schedule": ["", "", "16:15", "18:40"]},
				{"short" : "ВТ", "full" : "Вторник", 		"schedule": ["", "", "16:15", "18:40"]},
				{"short" : "СР", "full" : "Среда", 			"schedule": ["", "", "16:15", "18:40"]},
				{"short" : "ЧТ", "full" : "Четверг", 		"schedule": ["", "", "16:15", "18:40"]},
				{"short" : "ПТ", "full" : "Пятница", 		"schedule": ["", "", "16:15", "18:40"]},
				{"short" : "СБ", "full" : "Суббота", 		"schedule": ["11:00", "13:30", "16:00", "18:30"]},
				{"short" : "ВС", "full" : "Воскресенье",	"schedule": ["11:00", "13:30", "16:00", "18:30"]}
			]
			
			
			$scope.updateCache = ->
				ajaxStart()
				$.post "groups/ajax/updateCache", {id_group: $scope.Group.id}, () ->
					$scope.can_update_cache = false
					$scope.$apply()
					ajaxEnd()
			
			$scope.schedulde_loaded = false
			
			$scope.formatDate = (date) ->
				moment(date).format "D MMMM YYYY г."
			
			$scope.getLine1 = (Schedule) ->
				moment(Schedule.date).format "D MMMM YYYY г."
			
			$scope.setTimeFromGroup = (Group) ->
				$.each $scope.Group.Schedule, (i, v) ->
					#if not v.time
					d = moment(v.date).format("d")
					key = Object.keys(Group.day_and_time[d])[0]
					v.time = Group.day_and_time[d][key]
				$.post "groups/ajax/TimeFromGroup", {id_group: Group.id}
				$scope.$apply()
			
			$scope.lessonCount = ->
				Object.keys($scope.Group.day_and_time).length
			
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
							$scope.$apply()
						$(this)
							.hide()
							.parent()
							.children "span"
							.html if time then time else "не установлено"
							.show()
					.focus()
				
				
				return false
			
			$scope.getInitParams = (el) ->
				month = parseInt $(el).attr "month"
				year = if month >= 8 then parseInt moment().format "YYYY" else moment().add(1, "years").format "YYYY"
				current_date = new Date "#{year}-#{month}-01"
				language: 'ru'
				startDate: current_date
				endDate: moment(current_date).endOf("month").toDate()
				multidate: true
				beforeShowDay: (d) ->
					if moment(d).format("YYYY-MM-DD") in $scope.past_lesson_dates 
						add_class = 'was-lesson'
					if moment(d).format("YYYY-MM-DD") in $scope.vocation_dates 
						add_class += ' vocation'
					add_class
					
			$scope.monthName = (month) ->
				moment().month(month - 1).format "MMMM"
				
			$scope.can_update_cache = false
			$scope.dateChange = (e) ->
				return if not $scope.schedule_loaded
				# console.log clicked_date
				
				$scope.can_update_cache = true
				d = moment(clicked_date).format("YYYY-MM-DD")
				
				$scope.Group.Schedule = initIfNotSet $scope.Group.Schedule
				
				# check if date is in schedule
				t = $scope.Group.Schedule.filter (schedule) ->
					schedule.date is d 
				
				if t.length is 0
					$scope.Group.Schedule.push 
						date: d
					$.post "groups/ajax/AddScheduleDate", {date: d, id_group: $scope.Group.id}
				else
# 					index = $scope.schedule.indexOf d
# 					$scope.schedule.splice index, 1
					$.each $scope.Group.Schedule, (i, v) ->
						if v isnt undefined
							if v.date is d 
								$scope.Group.Schedule.splice i, 1
					$.post "groups/ajax/DeleteScheduleDate", {date: d, id_group: $scope.Group.id}
				$scope.$apply()
			
			angular.element(document).ready ->
				set_scope 'Group'
				init_dates = []
				for schedule_date in $scope.Group.Schedule
					init_dates.push new Date schedule_date.date
# 					$scope.schedule.push 
				console.log init_dates
				$(".calendar-month").each ->
					$(this)
						.datepicker $scope.getInitParams this
						.on "changeDate", $scope.dateChange
					m = $(this).attr "month"
					
					# loading schedule on calendar
					for d in init_dates
						month_number = moment(d).format("M")
						if month_number is m
							$(this).datepicker "_setDate", d
					
					# schedule loaded after 500 ms
					setTimeout ->
						$scope.schedule_loaded = true
						$scope.$apply()
					, 500
					 
				$(".table-condensed").first().children("thead").css "display", "table-caption"
				# hack
				$(".table-condensed").eq(15).children("tbody").children("tr").first().remove()					
									
		.controller "EditCtrl", ($scope) ->
			$scope.weekdays = [
				{"short" : "ПН", "full" : "Понедельник", 	"schedule": ["", "", "16:15", "18:40"]},
				{"short" : "ВТ", "full" : "Вторник", 		"schedule": ["", "", "16:15", "18:40"]},
				{"short" : "СР", "full" : "Среда", 			"schedule": ["", "", "16:15", "18:40"]},
				{"short" : "ЧТ", "full" : "Четверг", 		"schedule": ["", "", "16:15", "18:40"]},
				{"short" : "ПТ", "full" : "Пятница", 		"schedule": ["", "", "16:15", "18:40"]},
				{"short" : "СБ", "full" : "Суббота", 		"schedule": ["11:00", "13:30", "16:00", "18:30"]},
				{"short" : "ВС", "full" : "Воскресенье",	"schedule": ["11:00", "13:30", "16:00", "18:30"]}
			]
			
			$scope.$watch 'Group.open', (newValue, oldValue) ->
				console.log newValue
				if parseInt(newValue) is 0
					$(".selectpicker").first().css "background", "#eee"
				else
					$(".selectpicker").first().css "background", "white"
			
			$scope.smsDialog2 = smsDialog2
			
			$scope.getGroup = (id_group) ->
				Group = (i for i in $scope.Groups when i.id is id_group)[0]
				
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
							$.post "groups/ajax/AddStudentDnd", {id_group: id_group, id_student: id_student}
							Group.students.push id_student
							$scope.removeStudent id_student
							$scope.$apply()
			
			$scope.dateToStart = (date) ->
				date = date.split "."
				date = date.reverse()
				date = date.join "-"
				
				D = new Date(date)
				
				moment().to D
			
			$scope.search_groups = 
				grade: ""
				id_branch: ""
				id_subject: ""
			
			$scope.groupsFilter = (Group) ->
				console.log $scope.search_groups.id_teacher, Group, Group.id_teacher
				return (Group.grade is parseInt($scope.search_groups.grade) or not $scope.search_groups.grade) and 
					(parseInt($scope.search_groups.id_branch) is Group.id_branch or not $scope.search_groups.id_branch) and
					(parseInt($scope.search_groups.id_subject) is Group.id_subject or not $scope.search_groups.id_subject)
			
			bindDraggable = ->
				$(".student-line").draggable
					helper: 'clone'
					revert: 'invalid'
						# return if $scope.Group.open == "0"
# 						if not valid
# 							id_student = $(this).data "id"
# 							$scope.removeStudent id_student
# 							this.remove()
					start: (event, ui) ->
						$scope.is_student_dragging = true
						$scope.$apply()
						$(this).css "visibility", "hidden"
						$(ui.helper).addClass "tr-helper"
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
						
				
# 				$(".table-students").droppable
# 					tolerance: 'pointer'
# 					hoverClass: "request-status-drop-hover",
# 					drop: (event, ui) ->
# 						console.log ""
# 						ui.draggable.remove();
# 						id_group	 = $(this).data("id")
# 						id_student	 = $(ui.draggable).data("id")
# 						
# 						Group = $scope.getGroup id_group
# 						
# 						if id_student in Group.students
# 							notifySuccess "Ученик уже в группе"
# 						else
# 							$.post "groups/ajax/AddStudentDnd", {id_group: id_group, id_student: id_student}
# 							Group.students.push id_student
# 							$scope.$apply()
						
						
			$scope.dayAndTime = ->
				lightBoxShow "freetime"
			
			rebindBlinking = ->
				blinking = $(".blink")
				blinking.removeClass "blink"
				setTimeout ->
					blinking.addClass "blink"
				, 50
				
			$scope.dayAndTimeClick = (index, n) ->
				index++
				$scope.form_changed = true
				$scope.Group.day_and_time[index] = initIfNotSet $scope.Group.day_and_time[index]
				if 	$scope.Group.day_and_time[index][n] isnt true
# 					console.log "here", $scope.Group.day_and_time[index][n]
					$scope.Group.day_and_time[index][n] = ""	
				else
# 					console.log "here2", $scope.Group.day_and_time[index][n]
					# rebind blinking
					$scope.Group.day_and_time[index][n] = $scope.weekdays[index - 1].schedule[n]
			
			$scope.saveDayAndTime = ->
				lightBoxHide()
				rebindBlinking()
				$(".save-button").mousedown()
			
			initDayAndTime = (day) ->
				$scope.Group.day_and_time = initIfNotSet $scope.Group.day_and_time
				$scope.Group.day_and_time[day] = initIfNotSet $scope.Group.day_and_time[day]
			
			$scope.inDayAndTime = (day, value) ->
				initDayAndTime day
				return $.inArray(value, objectToArray($scope.Group.day_and_time[day])) >= 0
			
			$scope.inDayAndTime2 = (time, freetime) ->
				return false if freetime is undefined
				freetime = objectToArray freetime
				return $.inArray(time, freetime) >= 0
			
			$scope.inCabinetFreetime = (time, freetime) ->
				return false if freetime is undefined
				freetime = objectToArray freetime
				return $.inArray(time, freetime) >= 0
			
			$scope.justInDayFreetime = (day, time, freetime) ->
				return false if freetime is undefined or freetime is null
				return $.inArray(time, freetime[day]) >= 0
			
			
			$scope.isOrangeBrick = (day, time) ->
				current_index = $.inArray(time, $scope.weekdays[day - 1].schedule)
			
			$scope.changeCabinet = ->
				$("#group-cabinet").attr "disabled", "disabled"
				ajaxStart()
				$.post "groups/ajax/GetCabinetFreetime", {id_group: $scope.Group.id, cabinet: $scope.Group.cabinet}, (freetime) ->
					ajaxEnd()
					$("#group-cabinet").removeAttr "disabled"
					$scope.cabinet_freetime = freetime
					$scope.$apply()
				, "json"
			
			$scope.changeTeacher = ->
# 				$("#group-cabinet").attr "disabled", "disabled"
				return if $scope.Group.id_teacher is "0"
				ajaxStart()
				$.post "groups/ajax/GetTeacherFreetime", {id_group: $scope.Group.id, id_teacher: $scope.Group.id_teacher, id_branch: $scope.Group.id_branch}, (freetime) ->
					console.log freetime
					ajaxEnd()
# 					$("#group-cabinet").removeAttr "disabled"
					$scope.teacher_freetime 		= freetime.red
					$scope.teacher_freetime_green 	= freetime.green
					$scope.teacher_freetime_red	 	= freetime.red_full
					
					$scope.teacher_freetime_orange_half	= freetime.orange
					$scope.teacher_freetime_orange_full	= freetime.orange_full
					
					$scope.teacher_freetime_doubleblink	= freetime.red_doubleblink
					
					$scope.Group.teacher_status = freetime.teacher_status
					
					$scope.$apply()
				, "json"
			
			$scope.selectAllWorking = (id_branch) ->
				$.each $scope.weekdays, (index, weekday) ->
					return if index > 4
					if  $scope.freetime_selected_all_working
						$scope.Group.day_and_time[index + 1][2] = ""
						$scope.Group.day_and_time[index + 1][3] = ""
					else
						$scope.Group.day_and_time[index + 1][2] = weekday.schedule[2]
						$scope.Group.day_and_time[index + 1][3] = weekday.schedule[3]
				$scope.freetime_selected_all_working = !$scope.freetime_selected_all_working
			
			$scope.selectAllWeek = ->
				$.each $scope.weekdays, (index, weekday) ->
					if $scope.freetime_selected_all_week
						$scope.Group.day_and_time[index + 1][0] = ""
						$scope.Group.day_and_time[index + 1][1] = ""
						$scope.Group.day_and_time[index + 1][2] = ""
						$scope.Group.day_and_time[index + 1][3] = ""  
					else
						$scope.Group.day_and_time[index + 1][0] = weekday.schedule[0]
						$scope.Group.day_and_time[index + 1][1] = weekday.schedule[1]
						$scope.Group.day_and_time[index + 1][2] = weekday.schedule[2]
						$scope.Group.day_and_time[index + 1][3] = weekday.schedule[3]
				$scope.freetime_selected_all_week = !$scope.freetime_selected_all_week
			
			$scope.selectAllIndex = (index) ->
				$scope.freetime_selected_all_index = initIfNotSet($scope.freetime_selected_all_index)
				$.each $scope.weekdays, (i, weekday) ->
					if $scope.freetime_selected_all_index[index]
						$scope.Group.day_and_time[i + 1][index] = ""
					else
						$scope.Group.day_and_time[i + 1][index] = weekday.schedule[index]
				$scope.freetime_selected_all_index[index] = !$scope.freetime_selected_all_index[index]
			
			$scope.to_students = true
			$scope.to_representatives = false
			$scope.to_teacher = false
			
			# disable "send" button if nothing selected
			$scope.$watch "[to_students, to_representatives]", (newValue, oldValue) ->
				if not newValue[0] and not newValue[1]
					$(".ajax-email-button").attr "disabled", "disabled"
				else
					$(".ajax-email-button").removeAttr "disabled"
			
			$scope.emailDialog = ->
				$("#email-history").html "<center class='text-gray'>загрузка истории сообщений...</center>"
				html = ""
				
				$.post "ajax/emailHistory", {place: "GROUP", id_place: $scope.Group.id}, (response) ->
					console.log(response);
					if response isnt false
						$.each response, (i, v) ->
							files_html = ""
							$.each v.files, (i, file) ->
								files_html += '<div class="sms-coordinates">\
									<a target="_blank" href="files/email/' + file.name + '" class="link-reverse small">' + file.uploaded_name + '</a>\
									<span> (' + file.size + ')</span>\
									</div>'
							html += '<div class="clear-sms">		\
										<div class="from-them">		\
											' + v.message + ' 		\
											<div class="sms-coordinates">' + v.coordinates + '</div>' + files_html + '\
									    </div>						\
									</div>';	
						$("#email-history").html(html)
					else
						$("#email-history").html("")
				, "json"
				
				$("#email-address").text "Группа #{$scope.Group.id} " + (if $scope.Group.is_special then "(спецгруппа)" else "")
				lightBoxShow('email')
			
			initFreetime = (freetime, day) ->
				freetime								= initIfNotSet freetime
				freetime[$scope.Group.id_branch] 		= initIfNotSet freetime[$scope.Group.id_branch]
				freetime[$scope.Group.id_branch][day] 	= initIfNotSet freetime[$scope.Group.id_branch][day]
			
			
			$scope.inFreetime = (time, Student, day) ->
				return false if Student.freetime is undefined or Student.freetime is null
				if Student.freetime[$scope.Group.id_branch] is undefined
					return false if Student.freetime[0] is undefined
					freetime = Student.freetime[0]
				else
					freetime = Student.freetime[$scope.Group.id_branch]
				$.inArray(time, freetime[day]) >= 0
			
			$scope.inRedFreetime = (time, Student, day) ->
				return false if Student.freetime_red is null
				$.inArray(time, Student.freetime_red[day]) >= 0	
			
				
			$scope.setStudentStatus = (Student, event) ->
				return false if parseInt($scope.Group.open) is 0
				$(event.target).hide()
				$(".student-status-select-#{Student.id}").show 0, ->
					$(@).simulate 'mousedown'
					$("option[value^='?']").remove()
				return false
			
			$scope.setTeacherStatus = (Teacher, event) ->
				return false if parseInt($scope.Group.open) is 0
				$(event.target).hide()
				$(".teacher-status-select-#{Teacher.id}").show 0, ->
					$(@).simulate 'mousedown'
					$("option[value^='?']").remove()
				return false
			
			$scope.teachersFilter = (Teacher) ->
				return (parseInt($scope.Group.id_branch) in Teacher.branches or not $scope.Group.id_branch) and
					(parseInt($scope.Group.id_subject) in Teacher.subjects or not $scope.Group.id_subject)
			
			$scope.emptyDayFilter = (day_and_time) ->
				return _.filter day_and_time, (d) ->
					d.length isnt 0
				
			
			$scope.countSubjects = (Contract) ->
				Object.keys(Contract.subjects).length
			
			# on dropdown close fix
			$(document).on "mouseup", ->
				$("select[class^='student-status-select'], select[class^='teacher-status-select']").hide()
				$(".s-s-s, .t-s-s").show()
		
			
			$scope.smsNotify = (id_student) ->
				$scope.Group.student_statuses[id_student] = {id_status: 0, notified: 0} if $scope.Group.student_statuses[id_student] is undefined
				return false if $scope.Group.student_statuses[id_student].notified
				$scope.Group.student_statuses[id_student].notified = 1
				$scope.getStudent(id_student).notified = 1
				$.post "groups/ajax/smsNotify", {id_student: id_student, id_group: $scope.Group.id}
				justSave()
				
			$scope.bindGroupStudentStatusChange = ->
				$("select[class^='student-status-select']")
					.on "input", ->
						$(@).hide()
						id_student = $(@).data "id"
						$(".student-status-span-#{id_student}").show()
						$scope.Group.student_statuses[id_student] = {id_status: 0, notified: 0} if $scope.Group.student_statuses[id_student] is undefined
						$scope.Group.student_statuses[id_student].id_status = $(@).val()
				$("select[class^='teacher-status-select']")
					.on "input", ->
						$(@).hide()
						id_teacher = $(@).data "id"
						$(".teacher-status-span-#{id_teacher}").show()
						$scope.Group.teacher_status = $(@).val()
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
					
			$scope.removeStudent = (id_student) ->
				$.each $scope.Group.students, (index, data) ->
					if data is id_student
						$scope.Group.students.splice index, 1
						justSave()
						$scope.form_changed = true
						$scope.$apply()
				$.each $scope.TmpStudents, (index, data) ->
					if data isnt undefined and data.id is id_student
						$scope.TmpStudents.splice index, 1
							
			$scope.studentAdded = (id_student) ->
				id_student in $scope.Group.students
			
			$scope.getStudent = (id_student) ->
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
				$("#group-cabinet").attr "disabled", "disabled"
				ajaxStart()
				$.post "groups/ajax/getCabinet", {id_branch: $scope.Group.id_branch}, (cabinets) ->
					ajaxEnd()
					$scope.Cabinets = cabinets
					if cabinets isnt undefined and cabinets.length
						$scope.Group.cabinet = cabinets[0].id
						
					if cabinets.length isnt 1 
						$("#group-cabinet").removeAttr "disabled"
					
					$scope.$apply()
					clearSelect()
				, "json"
			
			$scope.addClientsPanel = ->
				$scope.loadStudents() if not $scope.Students
				$scope.add_clients_panel = not $scope.add_clients_panel
				$scope.search.grade = $scope.Group.grade if not $scope.search.grade and $scope.Group.grade
				$scope.search.id_subject = $scope.Group.id_subject if not $scope.search.id_subject and $scope.Group.id_subject
				if not $scope.search.id_branch and $scope.Group.id_branch
					$scope.search.id_branch = $scope.Group.id_branch
					$scope.$apply()
					$("#group-branch-filter").selectpicker 'render'
					
			$scope.addGroupsPanel = ->
				$scope.loadGroups() if not $scope.Groups
				$scope.add_groups_panel = not $scope.add_groups_panel
				$scope.search_groups.grade = $scope.Group.grade if not $scope.search_groups.grade and $scope.Group.grade
				$scope.search_groups.id_subject = $scope.Group.id_subject if not $scope.search_groups.id_subject and $scope.Group.id_subject
# 				$("html, body").animate { scrollTop: $(document).height() }, 1000
# 				if not $scope.search_groups.id_branch and $scope.Group.id_branch
# 					$scope.search_groups.id_branch = $scope.Group.id_branch
# 					$scope.$apply()
# 					$("#groups-branch-filter").selectpicker 'render'
			
			
			
			$scope.subjectChange = ->
				$scope.loadStudents()
				$scope.Group.id_teacher = 0
				clearSelect()
			
			$scope.loading_students = false
			$scope.loadStudents = ->
				return if not $scope.Group.id
				
# 				$scope.add_clients_panel = 0
				$scope.Students = false
				$scope.loading_students = true
				$.post "groups/ajax/getStudents", {id_group: $scope.Group.id, id_subject: $scope.Group.id_subject}, (response) ->
						$scope.loading_students = false
						$scope.Students = response
						$.each $scope.Group.student_statuses, (id_student, data) ->
							id_student = parseInt id_student
							Student = $scope.getStudent id_student
							if Student isnt undefined
								Student.id_status 	= data.id_status
								Student.notified 	= data.notified
								$scope.$apply()
						$scope.$apply()
				, "json"
				
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
				
# 				$scope.loadStudents()
				
				$scope.bindGroupStudentStatusChange()
								
				if $scope.Group.Comments is false
					$scope.Group.Comments = []
				
				frontendLoadingEnd()
				
			$(document).ready ->
				emailMode 2
				smsMode 2
				bindDraggable()
				$("#group-edit").on 'keyup change', 'input, select, textarea', ->
					$scope.form_changed = true
					$scope.$apply()
			
			# save without notice
			justSave = ->
				$.post "groups/ajax/save", $scope.Group
				
			$(".save-button").on "mousedown", ->
					ajaxStart()
					$scope.saving = true
					$scope.$apply()
					
					$.post "groups/ajax/save", $scope.Group, (response) ->
						if $scope.Group.id
							ajaxEnd()
							$scope.saving = false
							$scope.form_changed = false
							$scope.$apply()
						else
							redirect "groups/edit/#{response}"	
						
		.controller "ListCtrl", ($scope) ->
			$scope.weekdays = [
				{"short" : "ПН", "full" : "Понедельник", 	"schedule": ["", "", "16:15", "18:40"]},
				{"short" : "ВТ", "full" : "Вторник", 		"schedule": ["", "", "16:15", "18:40"]},
				{"short" : "СР", "full" : "Среда", 			"schedule": ["", "", "16:15", "18:40"]},
				{"short" : "ЧТ", "full" : "Четверг", 		"schedule": ["", "", "16:15", "18:40"]},
				{"short" : "ПТ", "full" : "Пятница", 		"schedule": ["", "", "16:15", "18:40"]},
				{"short" : "СБ", "full" : "Суббота", 		"schedule": ["11:00", "13:30", "16:00", "18:30"]},
				{"short" : "ВС", "full" : "Воскресенье",	"schedule": ["11:00", "13:30", "16:00", "18:30"]}
			]
			
			$scope.updateStatsCache = ->
				ajaxStart()
				$.post "groups/ajax/updateStatsCache", {}, ->
					redirect "groups"
			
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
			
			$scope.orderByFirstLesson = ->
				$scope.Groups.sort (a, b) ->
					a.first_schedule - b.first_schedule
					
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
				return (Group.grade is parseInt($scope.search.grade) or not $scope.search.grade) and 
					(parseInt($scope.search.id_branch) is Group.id_branch or not $scope.search.id_branch) and
					(Group.id_subject.toString() in $scope.search.subjects or $scope.search.subjects.length is 0) and
					(parseInt($scope.search.id_teacher) is parseInt(Group.id_teacher) or not $scope.search.id_teacher) and
					(parseInt($scope.search.cabinet) is parseInt(Group.cabinet) or not parseInt($scope.search.cabinet))
			
			$scope.groupsFilter2 = (Group) ->
				return true if not Group.hasOwnProperty "grade"
				
				return (String(Group.grade) in $scope.search2.grades or $scope.search2.grades.length == 0) and 
					(String(Group.branch) in $scope.search2.branches or $scope.search2.branches.length == 0) and 
					(Group.subject is parseInt($scope.search2.id_subject) or not $scope.search2.id_subject)
			
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
			
			bindDraggable = ->
				$(".request-main-list").draggable
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
			
			$scope.changeMode = ->
				$scope.change_mode = parseInt $scope.change_mode
				switch $scope.change_mode
					when 2
						redirect "groups"
						ajaxStart()
					else
						redirect "groups/?mode=students"
						ajaxStart()
			
			$scope.students_picker = false
			$scope.loadStudentPicker = ->
				$scope.students_picker = true
				$("html, body").animate { scrollTop: $(document).height() }, 1000
				switch $scope.mode
					when 1
						$.post "settings/ajax/getStudents", {}, (response) ->
							$scope.Students = response
							$scope.$apply()
							bindDraggable()
						, "json"
						
						$scope.$watchCollection "search_student", (newValue, oldValue) ->
							console.log newValue
							setTimeout ->
								bindDraggable()
							, 100	
					when 2
						$.post "settings/ajax/StudentsWithNoGroup", {}, (response) ->
							$scope.Groups2 = response.GroupsShort
							$scope.Groups2.push
								Students: []
							$scope.GroupsShort	= response.GroupsShort
							$scope.GroupsFull	= response.GroupsFull
							$scope.$apply()
							bindDraggable2()
						, "json"
			
			$(document).ready ->
				# branch mulitiselect
				if $("#subjects-select").length
					$("#subjects-select").selectpicker
						noneSelectedText: "предметы"
				if $scope.mode is 2
					$("#group-branch-filter2").selectpicker
						noneSelectedText: "филиалы"
					
					$("#grades-select2").selectpicker
						noneSelectedText: "класс"
						multipleSeparator: ", "
			
			
			
			angular.element(document).ready ->
				set_scope "Group"
				frontendLoadingEnd()