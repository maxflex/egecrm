angular.module "Clients", []
	.filter 'to_trusted', ['$sce', ($sce) ->
        return (text) ->
            return $sce.trustAsHtml(text)
	]
	.controller "ErrorsCtrl", ($scope) ->
		angular.element(document).ready ->
			set_scope "Clients"
			$.post "clients/ajax/getErrorStudents", {mode: window.location.search}, (response) ->
				$scope.Response = response
				$scope.$apply()
			, "json"
	.controller "ListCtrl", ($scope) ->
		$scope.filter_cancelled = 0
		
		$scope.clientsFilter = (Student) ->
			if $scope.filter_cancelled is 2
				return _.findWhere(Student.Contract.subjects, {status: 1}) isnt undefined and Student.Contract.cancelled is 0
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
		
		$scope.to_students = true
		$scope.to_representatives = false
		$scope.smsDialog3 = ->
			$scope.sms_students = $scope.$eval "Students | filter:clientsFilter"
			$scope.sms_students_ids = _.pluck($scope.sms_students, 'id')
			smsDialog3()
		
		angular.element(document).ready ->
			$.post "clients/ajax/GetStudents", {}, (response) ->
				$scope.Students = response
				$scope.$apply()
			, "json"
			set_scope "Clients"
			smsMode 3