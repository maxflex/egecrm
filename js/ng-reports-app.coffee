angular.module "Reports", []
	.filter 'range', () ->
		return (input, total) ->
			total = parseInt total
			for i in [1...total + 1] by 1
				input.push i
			input
	.controller "ListCtrl", ($scope) ->
		$scope.getReports = (id_student) ->
			_.where($scope.Reports, {id_student: id_student})
			
		angular.element(document).ready ->
			set_scope "Reports"
	.controller "AddCtrl", ($scope) ->
		$scope.setGrade = (prop, n) ->
			$scope.form_changed = true
			if !$scope.Report[prop] or $scope.Report[prop] isnt n
				$scope.Report[prop] = n
			else
				$scope.Report[prop] = 0
		
		$scope.countSymbols = (text) ->
			return if !text or text.length <= 0
			text.length
		
		$scope.addReport = ->
			return if textareasHaveErrors()
			ajaxStart()
			$scope.adding = true
			$.post "reports/ajaxAdd", 
				Report: $scope.Report
			, (response) ->
				console.log response
				redirect "teachers/reports"
			, "json"
		
		$scope.sendReport = ->
			bootbox.confirm "Отправить отчет родителю?", (result) ->
				if result is true
					ajaxStart()
					$.post "reports/ajaxSendEmail",
						Report: $scope.Report 
					, (response) ->
						ajaxEnd()
						$scope.Report.email_sent = true
						$scope.Report.date_sent = response
						$scope.$apply()
					, "json"
					
		$scope.formatDate = (date) ->
			moment(date).format "DD.MM.YY"
		
		$scope.editReport = ->
			return if textareasHaveErrors()			
			ajaxStart()
			$scope.saving = true
			$.post "reports/ajaxEdit", 
				Report: $scope.Report
			, (response) ->
				ajaxEnd()
				$scope.form_changed = false
				$scope.saving = false
				$scope.$apply()
				console.log response
		
		textareasHaveErrors = ->
			has_errors = false
			$(".teacher-review-textarea").each (i, element) ->
				if $(element).val().length < 10
					$(element).focus().addClass "has-error"
					notifyError 'Комментарий должен быть длиннее 10 символов'
					has_errors = true
					return false
				else
					$(element).removeClass "has-error"
			return has_errors
		
		angular.element(document).ready ->
			$(".form-change-control").on 'keyup change', 'input, select, textarea', ->
				$scope.form_changed = true
				$scope.$apply()
			set_scope "Reports"