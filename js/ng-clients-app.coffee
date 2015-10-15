angular.module "Clients", []
	.filter 'to_trusted', ['$sce', ($sce) ->
        return (text) ->
            return $sce.trustAsHtml(text)
	]
	.controller "ListCtrl", ($scope) ->
		$scope.filter_cancelled = 0
		
		$scope.clientsFilter = (Student) ->
			if $scope.filter_cancelled is 2
				return Student.Contract.pre_cancelled is 1
			else
				return Student.Contract.cancelled is $scope.filter_cancelled
		
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