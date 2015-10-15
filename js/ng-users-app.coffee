angular.module "Users", []
	.controller "ListCtrl", ($scope) ->
		$scope.save = ->
			ajaxStart()
			$.post "users/ajax/save", 
				Users: $scope.Users
			, (response) ->
				ajaxEnd()
				$scope.form_changed = false
				$scope.$apply()
		angular.element(document).ready ->
			set_scope "Users"
			$("table").on 'keyup change', 'input, select, textarea', ->
				$scope.form_changed = true
				$scope.$apply()