angular.module "Clients", []
	.controller "ListCtrl", ($scope) ->

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