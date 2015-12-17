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
		
		$scope.getSubjects = (Visits) ->
			Object.keys(Visits)
		
		$scope.noReports = (Visits) ->
			return true if Visits is false or not Visits.length
			
			has_reports = false
			$.each Visits, (index, Visit) ->
				if Visit.hasOwnProperty('id_student')
					has_reports = true
					return 
			!has_reports
		
		$scope.formatDate = (date) ->
			moment(date).format "DD.MM.YY"
			
		$scope.isReport = (Report) ->
			Report.hasOwnProperty 'homework_grade'
		
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
		
		$scope.deleteReport = () ->
				bootbox.confirm "Вы уверены, что хотите удалить отчет №#{$scope.Report.id}?", (result) ->
					if result is true
						ajaxStart()
						$.post "reports/ajaxDelete", {id_report: $scope.Report.id}, ->
							history.back()

		$scope.with_email = true		
			
		$scope.addReport = (with_email) ->
			return if textareasHaveErrors()
			ajaxStart()
			$scope.adding = true
			$.post "reports/ajaxAdd", 
				Report: $scope.Report
				with_email: with_email
			, (response) ->
				console.log response
				history.back()
				#redirect "teachers/reports"
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