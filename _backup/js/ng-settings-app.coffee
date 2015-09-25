testy = 1

angular.module "Settings", []
	.filter 'to_trusted', ['$sce', ($sce) ->
        return (text) ->
            return $sce.trustAsHtml(text)
	]
	.controller "LessonsCtrl", ($scope) ->
		$scope.formatDate = (date) ->
			date = date.split "."
			date = date.reverse()
			date = date.join "-"
			D = new Date(date)
			moment(D).format "D MMMM YYYY г."
			
		
		$scope.isFirstLesson = (Schedule) ->
			GroupSchedule = _.where $scope.Schedule, 
				id_group: Schedule.id_group
			
			first_lesson_date = _.sortBy GroupSchedule, 'date'
			first_lesson_date = first_lesson_date[0].date
			
			return first_lesson_date is Schedule.date
		
		angular.element(document).ready ->
			set_scope "Settings"
			
			$.post "settings/ajax/GetLessons", {}, (Schedule) ->
				console.log Schedule
				$scope.Schedule = Schedule
				$scope.$apply()
			, "json"
	.controller "CabinetsPageCtrl", ($scope) ->
		$scope.weekdays = [
			{"short" : "ПН", "full" : "Понедельник"},
			{"short" : "ВТ", "full" : "Вторник"},
			{"short" : "СР", "full" : "Среда"},
			{"short" : "ЧТ", "full" : "Четверг"},
			{"short" : "ПТ", "full" : "Пятница"},
			{"short" : "СБ", "full" : "Суббота"},
			{"short" : "ВС", "full" : "Воскресенье"}
		]
		$scope.dateToStart = (date) ->
				date = date.split "."
				date = date.reverse()
				date = date.join "-"
				
				D = new Date(date)
				
				moment().to D
	.controller "CabinetsCtrl", ($scope) ->
		
		resetAdd = ->
			$scope.cabinet_add =
				id_branch: ""
				number: ""
			$scope.$apply()
			$("#add-branch").selectpicker 'render'
		
		resetAdd()
		
		$scope.getBranch = (id_branch) ->
			Branch = (i for i in $scope.Branches when i.id is parseInt(id_branch))[0]
		
		$scope.addCabinet = ->
			if not $scope.cabinet_add.id_branch
				$(".selectpicker").addClass("has-error").focus()
				return false
			else
				$(".selectpicker").removeClass "has-error"
			
			if not $scope.cabinet_add.number
				$("#add-number").addClass("has-error").focus()
				return false
			else
				$("#add-number").removeClass "has-error"
			
			$.post "settings/ajax/addCabinet", $scope.cabinet_add
			Branch = $scope.getBranch($scope.cabinet_add.id_branch)
			Branch.Cabinets = initIfNotSet Branch.Cabinets
			Branch.Cabinets.push
				id_branch: $scope.cabinet_add.id_branch
				number: $scope.cabinet_add.number
			resetAdd()
		
		$scope.removeCabinet = (id_branch, index) ->
			$scope.getBranch(id_branch).Cabinets.splice index, 1
			$.post "settings/ajax/removeCabinet", {id_branch: id_branch, index: index}
				
	.controller "StudentsWithNoGroupCtrl", ($scope) ->
		$scope.search = 
			grades: []
			branches: []
			id_subject: ""
			
		$scope.groupsFilter = (Group) ->
			# return empty 
			return true if not Group.hasOwnProperty "grade"
			
			return (String(Group.grade) in $scope.search.grades or $scope.search.grades.length == 0) and 
				(String(Group.branch) in $scope.search.branches or $scope.search.branches.length == 0) and 
				(Group.subject is parseInt($scope.search.id_subject) or not $scope.search.id_subject)
		
		$scope.$watchCollection "search", (newValue, oldValue) ->
			$scope.Groups = if newValue.branches.length > 0 then $scope.GroupsFull else $scope.GroupsShort
			if $scope.Groups isnt undefined and $scope.Groups.length > 0
				if $scope.Groups[$scope.Groups.length - 1].hasOwnProperty "grade"
					$scope.Groups.push
						Students: []
			setTimeout ->
				bindDraggable()
			, 100
		
# 		$scope.$watch "search.branches", (newValue, oldValue) ->
# 			if newValue.length > 0 
# 				console.log "full"
# 			else
# 				console.log "short"
# 			$scope.Groups = if newValue.length > 0 then $scope.GroupsFull else $scope.GroupsShort
		
		$scope.getGroup = (id_group) ->
			Group = (i for i in $scope.Groups when i.id is id_group)[0]
		
		bindDraggable = ->
			$(".student-line").draggable
				helper: 'clone'
				revert: 'invalid'
				start: (event, ui) ->
					$(this).css "visibility", "hidden"
					$(ui.helper).addClass "tr-helper"
				stop: (event, ui) ->
					$(this).css "visibility", "visible"
			
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
					
							
					Groups = $scope.$eval "Groups | filter:groupsFilter"
					
					# Group TO which student was dropped
					Group = Groups[group_index]
					
					# Group FROM which student was dragged
					GroupFrom = Groups[student_group_index]
					
					# check if student is already in group
					in_group = false
					Group.Students = initIfNotSet Group.Students
					$.each Group.Students, (index, S) ->
						in_group = true if S.id is Student.id
					
					if in_group
						notifySuccess "Ученик уже в группе"
					else
# 						console.log Group, Group.Students
						# Add student to new group
						Group.Students = objectToArray Group.Students
						Group.Students.push Student
						# Remove student from old group
						GroupFrom.Students = objectToArray GroupFrom.Students
						$.each GroupFrom.Students, (index, GroupFromStudent) ->
							if GroupFromStudent isnt undefined and GroupFromStudent.id is Student.id
								GroupFrom.Students.splice index, 1
						ui.draggable.remove()
						table = $("#group-index-#{student_group_index}")
						testy = table
						if table.find("tr").length <= 1
							table.remove()
					$scope.$apply()
					bindDraggable()
					
					
					
					
		$(document).ready ->
			$("#group-branch-filter").selectpicker
				noneSelectedText: "филиалы"
			
			$("#grades-select").selectpicker
				noneSelectedText: "класс"
				multipleSeparator: ", "
							
		angular.element(document).ready ->
			set_scope "Settings"
			
			$.post "settings/ajax/StudentsWithNoGroup", {}, (response) ->
				$scope.Groups = response.GroupsShort
				$scope.Groups.push
					Students: []
				$scope.GroupsShort	= response.GroupsShort
				$scope.GroupsFull	= response.GroupsFull
				$scope.$apply()
				bindDraggable()
			, "json"
	.controller "StudentsCtrl", ($scope) ->
		$scope.weekdays = [
			{"short" : "ПН", "full" : "Понедельник"},
			{"short" : "ВТ", "full" : "Вторник"},
			{"short" : "СР", "full" : "Среда"},
			{"short" : "ЧТ", "full" : "Четверг"},
			{"short" : "ПТ", "full" : "Пятница"},
			{"short" : "СБ", "full" : "Суббота"},
			{"short" : "ВС", "full" : "Воскресенье"}
		]
		
		$scope.search = 
			grade: ""
			id_branch: ""
			id_subject: ""
			
		$scope.clientsFilter = (Student) ->
			return (Student.Contract.grade is parseInt($scope.search.grade) or not $scope.search.grade) and 
				(parseInt($scope.search.id_branch) in Student.branches or not $scope.search.id_branch) and
				(Student.Contract.subjects and (parseInt($scope.search.id_subject) of Student.Contract.subjects or not $scope.search.id_subject))
		
		angular.element(document).ready ->
			set_scope "Settings"
			
			$.post "settings/ajax/getStudents", {}, (response) ->
				$scope.Students = response
				$scope.$apply()
			, "json"
			
	.controller "VocationsCtrl", ($scope) ->
		$scope.schedulde_loaded = false
		$scope.menu = 1
		
		$scope.getLine1 = (Schedule) ->
			moment(Schedule.date).format "D MMMM YYYY г."
		
		$scope.getLine2 = (Schedule) ->
			moment(Schedule.date).format "dddd"
		
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