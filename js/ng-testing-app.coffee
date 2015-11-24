	angular.module "Testing", []
		.filter 'range', () ->
			return (input, total) ->
				total = parseInt total
				for i in [1...total + 1] by 1
					input.push i
				input
		.controller "ListCtrl", ($scope) ->
			angular.element(document).ready ->
				set_scope "Testing"
		.controller "AddCtrl", ($scope) ->
			
			$scope.formatDay = (date) ->
				day = moment(date).format "ddd"
				month = moment(date).format ", DD MMMM"
				
				day.toUpperCase() + month
			
			$scope.subjectChecked = (grade, id_subject) ->
				
				if grade is 11
					arr = $scope.Testing.subjects_11
				else  
					arr = $scope.Testing.subjects_9
				
				return $.inArray(parseInt(id_subject), arr) >= 0
				
				
			$scope.changeDate = ->
				$.post "testing/ajaxChangeDate", 
					id: $scope.Testing.id
					date: $scope.Testing.date
				, (response) ->
					$scope.cabinet_load = response
					$scope.$apply()
				, "json"
			
			$scope.notEnoughTime = (minutes) ->
				return true if !$scope.Testing || !$scope.Testing.start_time or !$scope.Testing.end_time
				
				date_start = new Date('2015-09-01 ' + $scope.Testing.start_time)
				date_end = new Date('2015-09-01 ' + $scope.Testing.end_time)
				
				minutes_start 	= (date_start.getHours() * 60) + date_start.getMinutes()
				minutes_end 	= (date_end.getHours() * 60) + date_end.getMinutes()
				
# 				console.log (minutes_end - minutes_start), minutes
				(minutes_end - minutes_start) < minutes
			
			$scope.saveTesting = ->
				$.post "testing/ajaxSave", 
					Testing: $scope.Testing
				, (response) ->
					console.log response
				, "json"
			
			$scope.addTesting = ->
				ajaxStart()
				$.post "testing/ajaxAdd", 
					Testing: $scope.Testing
				, (response) ->
					console.log response
					redirect "testing/"
				, "json"
					
			angular.element(document).ready ->
				$scope.changeDate()	if $scope.Testing isnt undefined
				set_scope "Testing"