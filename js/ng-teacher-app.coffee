	angular.module "Teacher", ["ngMap"]
		.controller "EditCtrl", ($scope) ->
			$scope.phoneCorrect = phoneCorrect
			$scope.isMobilePhone = isMobilePhone
			
			angular.element(document).ready ->
				set_scope "Teacher"
			
			$(document).ready ->
				$("#teacher-edit").on 'keyup change', 'input, select, textarea', ->
					$scope.form_changed = true
					$scope.$apply()
			
			$(".save-button").on "click", ->
					has_errors = false
					
					$(".phone-masked").filter ->
						not_filled = $(this).val().match(/_/)
						
						if not_filled isnt null
							$(this).addClass("has-error").focus()
							notifyError("Номер телефона указан неполностью")
							has_errors = true
							return false
						else
							$(this).removeClass("has-error")
						
					if has_errors
						return false
						
					ajaxStart()
					$scope.saving = true
					$scope.$apply()
					
					$.post "teachers/ajax/save", $scope.Teacher, (response) ->
						console.log response
						if $scope.Teacher.id
							ajaxEnd()
							$scope.saving = false
							$scope.form_changed = false
							$scope.$apply()
						else
							redirect "teachers/edit/#{response}"	
						
		.controller "ListCtrl", ($scope) ->
			