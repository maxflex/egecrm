testy = 1

angular.module "Settings", ["ui.bootstrap"]
	.filter 'to_trusted', ['$sce', ($sce) ->
        return (text) ->
            return $sce.trustAsHtml(text)
	]
	.controller "CabinetsCtrl", ($scope) ->
		$scope.weekdays = [
			{"short" : "ПН", "full" : "Понедельник", 	"schedule": ["", "", "16:15", "18:40"]},
			{"short" : "ВТ", "full" : "Вторник", 		"schedule": ["", "", "16:15", "18:40"]},
			{"short" : "СР", "full" : "Среда", 			"schedule": ["", "", "16:15", "18:40"]},
			{"short" : "ЧТ", "full" : "Четверг", 		"schedule": ["", "", "16:15", "18:40"]},
			{"short" : "ПТ", "full" : "Пятница", 		"schedule": ["", "", "16:15", "18:40"]},
			{"short" : "СБ", "full" : "Суббота", 		"schedule": ["11:00", "13:30", "16:00", "18:30"]},
			{"short" : "ВС", "full" : "Воскресенье",	"schedule": ["11:00", "13:30", "16:00", "18:30"]}
		]
			
		$scope.inCabinetFreetime = (time, freetime) ->
			return false if freetime is undefined
			freetime = objectToArray freetime
			return $.inArray(time, freetime) >= 0
		$scope.getBranchCabinets = (id_branch) ->
			_.where($scope.Cabinets, {id_branch: id_branch})
		angular.element(document).ready ->
			set_scope "Settings"
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
		
		$scope.pageChanged = ->
			ajaxStart()
			redirect "settings/lessons/#{$scope.currentPage}"
		
		angular.element(document).ready ->
			set_scope "Settings"
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