app = angular.module "Sms", ["ui.bootstrap"]
	.controller "Main", ($scope, $element, PhoneService) ->
		bindArguments $scope, arguments
		$scope.pageChanged = ->
			ajaxStart()
			redirect_string = "sms/#{$scope.currentPage}"
			redirect_string += "?search=#{$scope.search}" if $scope.search
			redirect_string += if $scope.search and $scope.phone then '&' else if $scope.phone then '?' else ''
			redirect_string += "&phone=#{$scope.phone}" if $scope.phone
			redirect redirect_string
		angular.element(document).ready ->
			set_scope "Sms"