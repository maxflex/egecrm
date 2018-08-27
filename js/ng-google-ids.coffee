app = angular.module "GoogleIds", ["ui.bootstrap"]
	.controller "IndexCtrl", ($scope, $timeout, $http) ->
		bindArguments $scope, arguments
		$timeout -> set_scope "GoogleIds"

		$scope.disabled_payments = {}
		$scope.google_ids = ''

		$scope.show = ->
			$scope.loading = true
			$.post 'google-ids/show', {google_ids: $scope.google_ids}, (response) ->
				console.log(response)
				$scope.data = response
				$scope.loading = false
				$scope.$apply()
			, 'json'

		$scope.getTotalGoogleIds = -> Object.keys($scope.data).length

		$scope.getTotal = (field) ->
			total = 0
			Object.keys($scope.data).forEach (id_google) ->
				total += $scope.data[id_google][field].length if $scope.data[id_google]
			total

		$scope.getTotalPayments = (field) ->
			total = 0
			Object.keys($scope.data).forEach (id_google) ->
				if $scope.data[id_google]
					$scope.data[id_google].payments.forEach (payment) ->
						return if $scope.disabled_payments.hasOwnProperty(payment.id) && $scope.disabled_payments[payment.id]
						if payment.id_type == 1
							total += payment.sum
						else
							total -= payment.sum
			total
