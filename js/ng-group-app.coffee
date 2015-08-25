	angular.module "Group", []
		.filter 'to_trusted', ['$sce', ($sce) ->
	        return (text) ->
	            return $sce.trustAsHtml(text)
		]
		.controller "ScheduleCtrl", ($scope) ->
			$scope.schedulde_loaded = false
			$scope.menu = 1
			
			$scope.getLine1 = (Schedule) ->
				moment(Schedule.date).format "D MMMM YYYY г."
			
			$scope.getLine2 = (Schedule) ->
				moment(Schedule.date).format "dddd"
			
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
					.timepicker
						timeFormat: 'H:i'
						scrollDefault: '09:30'
						selectOnBlur: true
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
					
					$(".table-condensed").first().children("thead").css("display", "table-caption")
					
					# schedule loaded after 500 ms
					setTimeout ->
						$scope.schedule_loaded = true
						$scope.$apply()
					, 500 
					
									
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
			
			$scope.countSubjects = (Contract) ->
				Object.keys(Contract.subjects).length
			
			$scope.addStudent = (id_student) ->
				if id_student not in $scope.Group.students
					$scope.Group.students.push(id_student)
					$scope.form_changed = true
			
			$scope.removeStudent = (id_student) ->
				$.each $scope.Group.students, (index, data) ->
					if data is id_student
						console.log data, index
						$scope.Group.students.splice index, 1
						$scope.form_changed = true
						$scope.$apply()
							
			$scope.studentAdded = (id_student) ->
				id_student in $scope.Group.students
			
			$scope.getStudent = (id_student) ->
				Student = (i for i in $scope.Students when i.id is id_student)[0]
				Student.last_name + " " + Student.first_name + " " + Student.middle_name
			
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
						$.post "groups/ajax/delete", {id_group: id_group}
						window.history.go -1
			
			angular.element(document).ready ->
				set_scope "Group"
				frontendLoadingEnd()
				
			$(document).ready ->
				$("#group-edit").on 'keyup change', 'input, select, textarea', ->
					$scope.form_changed = true
					$scope.$apply()
			
			$(".save-button").on "click", ->
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
			