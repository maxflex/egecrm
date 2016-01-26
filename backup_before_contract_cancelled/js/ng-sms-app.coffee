angular.module "Sms", ["ui.bootstrap"]
	.controller "Main", ($scope) ->
		
		$scope.pageChanged = ->
			ajaxStart()
			redirect "sms/#{$scope.currentPage}"
		angular.element(document).ready ->
			set_scope "Sms"