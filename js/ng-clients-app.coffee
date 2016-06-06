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
		
		$scope.remainderSum = ->
			sum = 0
			$.each $scope.Students, (index, Student) ->
				if Student.Remainder.id
					sum += Student.Remainder.remainder
			sum
		
		$scope.clientsFilter = (Student) ->
			switch $scope.filter_cancelled
				when 0 then _.findWhere(Student.Contract.subjects, {status: 3}) isnt undefined
				when 1
					count = 0
					$.each Student.Contract.subjects, (index, subject) ->
						if subject.status is 1
							count++
					count isnt Object.keys(Student.Contract.subjects).length and count > 0
				when 2 
					count = 0
					$.each Student.Contract.subjects, (index, subject) ->
						if subject.status is 1
							count++
					count is Object.keys(Student.Contract.subjects).length
				when 3 then _.findWhere(Student.Contract.subjects, {status: 2}) isnt undefined
				when 4 then true
		
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