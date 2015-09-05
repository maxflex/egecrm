	angular.module "Teacher", ["ngMap"]
		.filter 'to_trusted', ['$sce', ($sce) ->
	        return (text) ->
	            return $sce.trustAsHtml(text)
		]
		.controller "EditCtrl", ($scope) ->
			$scope.phoneCorrect = phoneCorrect
			$scope.isMobilePhone = isMobilePhone
			
			angular.element(document).ready ->
				set_scope "Teacher"
			
			$scope.goToTutor = ->
				window.open "https://crm.a-perspektiva.ru/repetitors/edit/?id=#{$scope.Teacher.id_a_pers}", "_blank"
			
			$(document).ready ->
				$("#subjects-select").selectpicker
					noneSelectedText: "предметы"
					multipleSeparator: ", "
				
				$("#teacher-branches").selectpicker
					noneSelectedText: "удобные филиалы для преподавателя"
				
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
					
					$scope.Teacher.subjects = []
					$("#subjects-select option:selected").each ->
						if $(@).val()
							$scope.Teacher.subjects.push $(@).val()
					
					$scope.Teacher.branches = []
					$("#teacher-branches option:selected").each ->
						if $(@).val()
							$scope.Teacher.branches.push $(@).val()
					
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
			$scope.deleteTeacher = (id_teacher, index) ->
				bootbox.confirm "Вы уверены, что хотите удалить преподавателя №#{id_teacher}?", (result) ->
					if result is true
						$scope.Teachers.splice index, 1
						$scope.$apply()
						$.post "teachers/ajax/delete", {id_teacher: id_teacher}
						console.log "here", index, id_teacher