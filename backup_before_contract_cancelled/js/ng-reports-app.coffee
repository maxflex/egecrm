angular.module "Reports", []
	.filter 'to_trusted', ['$sce', ($sce) ->
        return (text) ->
            return $sce.trustAsHtml(text)
	]
	.filter 'range', () ->
		return (input, total) ->
			total = parseInt total
			for i in [1...total + 1] by 1
				input.push i
			input
	.controller "UserListCtrl", ($scope) ->
		angular.element(document).ready ->
			set_scope "Reports"
			
			
			
			$scope.list = 1
			$scope.$watch 'list', (newValue, oldValue) ->
				if newValue is 2
					if $scope.SentReports is undefined
						$.post "reports/AjaxGetReports", 
							available_for_parents: 1
						, (response) ->
							$scope.SentReports = response
							$scope.SelectedReports = $scope.SentReports
							$scope.$apply()
						, "json"
					$scope.SelectedReports = $scope.SentReports
				else
					$scope.SelectedReports = $scope.NotSentReports
					
			$.post "reports/AjaxGetReports", 
				available_for_parents: 0
			, (response) ->
				$scope.NotSentReports = response
				$scope.SelectedReports = $scope.NotSentReports
				$scope.$apply()
			, "json"
		
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
		
		$scope.getByGrade = (grade, id_group) ->
			_.where $scope.Students, 
				grade: grade
				id_group: id_group						
		
		angular.element(document).ready ->
			$scope.weekdays = [
				{"short" : "ПН", "full" : "Понедельник", 	"schedule": ["", "", $scope.time[1], $scope.time[2]]},
				{"short" : "ВТ", "full" : "Вторник", 		"schedule": ["", "", $scope.time[1], $scope.time[2]]},
				{"short" : "СР", "full" : "Среда", 			"schedule": ["", "", $scope.time[1], $scope.time[2]]},
				{"short" : "ЧТ", "full" : "Четверг", 		"schedule": ["", "", $scope.time[1], $scope.time[2]]},
				{"short" : "ПТ", "full" : "Пятница", 		"schedule": ["", "", $scope.time[1], $scope.time[2]]},
				{"short" : "СБ", "full" : "Суббота", 		"schedule": [$scope.time[3], $scope.time[4], $scope.time[5], $scope.time[6]]},
				{"short" : "ВС", "full" : "Воскресенье",	"schedule": [$scope.time[3], $scope.time[4], $scope.time[5], $scope.time[6]]}
			]
			
			setTimeout ->
				$scope.$apply()
			, 50
			
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
						$scope.Report.email_sent= true
						$scope.Report.date_sent = response
						$scope.$apply()
					, "json"
					
		$scope.formatDate = (date) ->
			moment(date).format "DD.MM.YY"
		
		$scope.formatDate2 = (date) ->
				date = date.split "."
				date = date.reverse()
				date = date.join "-"
				D = new Date(date)
				moment(D).format "D MMMM YYYY года"

		
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
			if $(".teacher-rating.active").length < 5
# 				error_field = $(".teacher-rating").not('.active').first().parent().parent().parent().parent().find("b").text()
				notifyError('Не все оценки не установлены!')
				return true
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