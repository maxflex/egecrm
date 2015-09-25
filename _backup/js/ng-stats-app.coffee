angular.module "Stats", []
	.controller "ListCtrl", ($scope) ->
		$scope.payment_status = $.cookie "stats_payment_status"
		
		$scope.setPayment = (payment_status) ->
			# $scope.payment_status = payment_status
			$.cookie "stats_payment_status", payment_status, { expires: 365, path: '/' }
			location.reload()