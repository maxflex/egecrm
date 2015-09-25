angular.module "Templates", []
	.controller "ListCtrl", ($scope) ->
		$scope.toggle = (n, Template) ->
			index = Template.who.indexOf n
			
			if index is -1
				console.log index, 'here'
				Template.who.push n 
			else
				Template.who.splice index, 1
		$scope.inWho = (n, Template) ->
			index = Template.who.indexOf n 
			return index isnt -1
		
		$scope.save = ->
			ajaxStart()
			$.post "templates/ajax/save",
				templates: $scope.Templates
			, ->
				$scope.form_changed = false
				$scope.$apply()
				ajaxEnd()
		
		$scope.form_changed = false
		angular.element(document).ready ->
			set_scope "Templates"
			
			$("[ng-app='Templates']").on 'keyup change', 'input, select, textarea', ->
				$scope.form_changed = true
				$scope.$apply()