angular.module "Stats", ["ui.bootstrap"]
	.controller "ListCtrl", ($scope) ->
		$scope.pageChanged = ->
			ajaxStart()
			redirect "stats/?page=#{$scope.currentPage}"
		
		$scope.pageStudentChanged = ->
			ajaxStart()
			redirect "stats/visits/students?page=#{$scope.currentPage}"
		
		$scope.pagePaymentChanged = ->
			ajaxStart()
			redirect "stats/payments?page=#{$scope.currentPage}"