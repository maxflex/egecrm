angular.module "Clients", []
	.filter 'to_trusted', ['$sce', ($sce) ->
        return (text) ->
            return $sce.trustAsHtml(text)
	]
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
		
		$scope.inFreetime = (time, Group, day) ->
			return false if Group.freetime is undefined or Group.freetime is null
			if Group.freetime[Group.id_branch] is undefined
				return false if Group.freetime[0] is undefined
				freetime = Group.freetime[0]
			else
				freetime = Group.freetime[Group.id_branch]
			$.inArray(time, freetime[day]) >= 0
		
		$scope.inRedFreetime = (time, Group, day) ->
			return false if Group.freetime_red is null
			$.inArray(time, Group.freetime_red[day]) >= 0	
		
		$scope.justInDayFreetime = (day, time, freetime) ->
			return false if freetime is undefined or freetime is null
			freetime[day] = objectToArray freetime[day]
			console.log day, time, freetime[day], $.inArray(time, freetime[day]) >= 0
			return $.inArray(time, freetime[day]) >= 0
		
		$scope.justInDayFreetimeObject = (day, time, freetime) ->
			return false if freetime is undefined or freetime is null or freetime[day] is undefined or freetime[day] is null
			freetime[day] = objectToArray freetime[day]
			return $.inArray(time, freetime[day]) >= 0
		
		$scope.getScore = (subjects) ->
			ar = []
			$.each subjects, (i, v) ->
				if v.score isnt null and v.score isnt ""
					ar.push v.score
			return ar.join " + "
			
		$scope.filter_cancelled = 0
		$scope.order = 2
		
		$scope.setOrder = (order) ->
			console.log order, $scope.asc
			if $scope.order isnt order
				$scope.order = order
				$scope.asc = true
			else
				$scope.asc = !$scope.asc
		
		$scope.asc = true
		$scope.orderStudents = ->
			switch $scope.order
				when 1 then 'last_name'
				when 2 then 'Contract.id'
				else 'date_formatted'
		
		$scope.getSubjectsCount = (Contract) ->
			if Contract.subjects
				Object.keys(Contract.subjects).length
			else
				0
		
		angular.element(document).ready ->
			set_scope "Clients"