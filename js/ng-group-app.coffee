	angular.module "Group", []
		.filter 'to_trusted', ['$sce', ($sce) ->
	        return (text) ->
	            return $sce.trustAsHtml(text)
		]
		.filter 'range', () ->
			return (input, total) ->
				total = parseInt total
				for i in [1...total + 1] by 1
					input.push i
				input
		.controller "ScheduleCtrl", ($scope) ->
			$scope.schedulde_loaded = false
			
			$scope.getLine1 = (Schedule) ->
				moment(Schedule.date).format "D MMMM YYYY г."
			
			$scope.setTimeFromGroup = (Group) ->
				$.each $scope.Group.Schedule, (i, v) ->
					if not v.time
						v.time = Group.start
				$.post "groups/ajax/TimeFromGroup", {id_group: Group.id, time: Group.start}
				$scope.$apply()
			
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
					if moment(d).format("YYYY-MM-DD") in $scope.vocation_dates 
						'vocation'
				
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
				{"short" : "ПН", "full" : "Понедельник"},
				{"short" : "ВТ", "full" : "Вторник"},
				{"short" : "СР", "full" : "Среда"},
				{"short" : "ЧТ", "full" : "Четверг"},
				{"short" : "ПТ", "full" : "Пятница"},
				{"short" : "СБ", "full" : "Суббота"},
				{"short" : "ВС", "full" : "Воскресенье"}
			]
			
			$scope.setStudentStatus = (Student, event) ->
				$(event.target).hide()
				$(".student-status-select-#{Student.id}").show 0, ->
					$(@).simulate 'mousedown'
					$("option[value^='?']").remove()
				return false
			
			$scope.teachersFilter = (Teacher) ->
				return (parseInt($scope.Group.id_branch) in Teacher.branches or not $scope.Group.id_branch) and
					(parseInt($scope.Group.id_subject) in Teacher.subjects or not $scope.Group.id_subject)
				
			
			$scope.countSubjects = (Contract) ->
				Object.keys(Contract.subjects).length
			
			# on dropdown close fix
			$(document).on "mouseup", ->
				$("select[class^='student-status-select']").hide()
				$(".s-s-s").show()
				
			$scope.bindGroupStudentStatusChange = ->
				$("select[class^='student-status-select']")
					.on "input", ->
						$(@).hide()
						id_student = $(@).data "id"
						$(".student-status-span-#{id_student}").show()
						$scope.Group.student_statuses[id_student] = $(@).val()
			
			$scope.addStudent = (id_student, event) ->
				if id_student not in $scope.Group.students
					el = $(event.target)
					el.hide()
					$("#student-adding-#{id_student}").show()
					$.post "groups/ajax/inGroup",
						id_student: id_student 
						id_group: $scope.Group.id 
						id_subject: $scope.Group.id_subject
					, (in_other_group) ->
						if not in_other_group
							console.log el
							el.show()
							$("#student-adding-#{id_student}").hide()
							$scope.Group.students.push(id_student)
							$scope.TmpStudents = initIfNotSet $scope.TmpStudents
							$scope.TmpStudents.push $scope.getStudent id_student
							$scope.form_changed = true
							$scope.$apply()
							$scope.bindGroupStudentStatusChange()
							justSave()
						else
							$("#student-adding-#{id_student}").html "в другой группе"
					, "json"
					
			$scope.removeStudent = (id_student) ->
				$.each $scope.Group.students, (index, data) ->
					if data is id_student
						$scope.Group.students.splice index, 1
						justSave()
						$scope.form_changed = true
						$scope.$apply()
				$.each $scope.TmpStudents, (index, data) ->
					if data.id is id_student
						$scope.TmpStudents.splice index, 1
							
			$scope.studentAdded = (id_student) ->
				id_student in $scope.Group.students
			
			$scope.getStudent = (id_student) ->
				Student = (i for i in $scope.Students when i.id is id_student)[0]
			
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
				$.post "groups/ajax/getCabinet", {id_branch: $scope.Group.id_branch}, (response) ->
					$scope.Cabinets = response
					$scope.$apply()
				, "json"
				$scope.Group.cabinet = undefined
				setTimeout ->
					$("option[value^='?']").remove()
				, 50
			
			$scope.addClientsPanel = ->
				$scope.add_clients_panel = not $scope.add_clients_panel
				$scope.search.grade = $scope.Group.grade if not $scope.search.grade and $scope.Group.grade
				$scope.search.id_subject = $scope.Group.id_subject if not $scope.search.id_subject and $scope.Group.id_subject
				if not $scope.search.id_branch and $scope.Group.id_branch
					$scope.search.id_branch = $scope.Group.id_branch
					$scope.$apply()
					$("#group-branch-filter").selectpicker 'render'
			
			$scope.loadStudents = ->
				return if not $scope.Group.id
				
				$scope.add_clients_panel = 0
				$scope.Students = false
				$.post "groups/ajax/getStudents", {id_group: $scope.Group.id, id_subject: $scope.Group.id_subject}, (response) ->
						$scope.Students = response
						$.each $scope.Group.student_statuses, (id_student, id_status) ->
							id_student = parseInt id_student
							Student = $scope.getStudent id_student
							if Student isnt undefined
								Student.id_status = id_status
								$scope.$apply()
						$scope.$apply()
				, "json"
			
			angular.element(document).ready ->
				set_scope "Group"
				
				$scope.loadStudents()
				
				$scope.bindGroupStudentStatusChange()
								
				if $scope.Group.Comments is false
					$scope.Group.Comments = []
				
				frontendLoadingEnd()
				
			$(document).ready ->
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
			$scope.search = 
				grade: ""
				id_branch: ""
				id_subject: ""
			
			$scope.search2 = 
				grades: []
				branches: []
				id_subject: ""
			
			$scope.groupsFilter = (Group) ->
				return (Group.grade is parseInt($scope.search.grade) or not $scope.search.grade) and 
					(parseInt($scope.search.id_branch) is Group.id_branch or not $scope.search.id_branch) and
					(parseInt($scope.search.id_subject) is Group.id_subject or not $scope.search.id_subject)
			
			$scope.groupsFilter2 = (Group) ->
				# return empty 
				return true if not Group.hasOwnProperty "grade"
				
				return (String(Group.grade) in $scope.search2.grades or $scope.search2.grades.length == 0) and 
					(String(Group.branch) in $scope.search2.branches or $scope.search2.branches.length == 0) and 
					(Group.subject is parseInt($scope.search2.id_subject) or not $scope.search2.id_subject)
			
			$scope.dateToStart = (date) ->
				date = date.split "."
				date = date.reverse()
				date = date.join "-"
				
				D = new Date(date)
				
				moment().to D
			
			$scope.weekdays = [
				{"short" : "ПН", "full" : "Понедельник"},
				{"short" : "ВТ", "full" : "Вторник"},
				{"short" : "СР", "full" : "Среда"},
				{"short" : "ЧТ", "full" : "Четверг"},
				{"short" : "ПТ", "full" : "Пятница"},
				{"short" : "СБ", "full" : "Суббота"},
				{"short" : "ВС", "full" : "Воскресенье"}
			]
			
			
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
			
			$(document).ready ->
				if $scope.mode is 2
					$("#group-branch-filter2").selectpicker
						noneSelectedText: "филиалы"
					
					$("#grades-select2").selectpicker
						noneSelectedText: "класс"
						multipleSeparator: ", "
			
			angular.element(document).ready ->
				set_scope "Group"
				
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
