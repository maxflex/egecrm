angular.module "Sms", ["ui.bootstrap"]
	.controller "Main", ($scope) ->
		
		$scope.pageChanged = ->
			ajaxStart()
			redirect_string = "sms/#{$scope.currentPage}"
			redirect_string += "?search=#{$scope.search}" if $scope.search
			redirect redirect_string
		angular.element(document).ready ->
			set_scope "Sms"