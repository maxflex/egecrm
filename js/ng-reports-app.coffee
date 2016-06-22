angular.module "Reports", ["ui.bootstrap"]
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
	.controller "UserListCtrl", ($scope, $timeout) ->
		$scope.counts = 
			year: {2015: 20, 2016: 30}
		
		$scope.helper_updating = false
		
		$scope.forceNoreport = (d) ->
			$.post "reports/AjaxForceNoreport",
				id_student: d.id_entity,
				id_teacher: d.id_teacher,
				id_subject: d.id_subject,
				year: d.year
			, (response) ->
				 d.force_noreport = not d.force_noreport
				 $scope.$apply()

		$scope.yearLabel = (year) ->
			year + '-' + (parseInt(year) + 1) + ' уч. г.'
		
		$scope.refreshCounts = ->
			$timeout ->
				$('.watch-select option').each (index, el) ->
	                $(el).data 'subtext', $(el).attr 'data-subtext'
	                $(el).data 'content', $(el).attr 'data-content'
	            $('.watch-select').selectpicker 'refresh'
	        , 100
	    
		$scope.updateHelperTable = ->
			frontendLoadingStart()
			$scope.helper_updating = true
			$.post "reports/AjaxRecalcHelper", {}, (response) ->
				frontendLoadingEnd()
				$scope.helper_updating = false
				$scope.$apply()
				
		$scope.filter = ->
			$.cookie("reports", JSON.stringify($scope.search), { expires: 365, path: '/' });
			$scope.current_page = 1
			$scope.getByPage($scope.current_page)
		
		# Страница изменилась
		$scope.pageChanged = ->
			console.log $scope.currentPage
			window.history.pushState {}, '', 'reports/?page=' + $scope.current_page if $scope.current_page > 1
			# Получаем задачи, соответствующие странице и списку
			$scope.getByPage($scope.current_page)
		
		$scope.getByPage = (page) ->
			frontendLoadingStart()
			$.post "reports/AjaxGetReports",
				page: page
			, (response) ->
				frontendLoadingEnd()
				$scope.data  = response.data
				$scope.count = response.count
				$scope.$apply()
			, "json"
						 
		angular.element(document).ready ->
			set_scope "Reports"
			$scope.search = if $.cookie("reports") then JSON.parse($.cookie("reports")) else {}
			$scope.current_page = $scope.currentPage
			$scope.pageChanged()
			
			$(".single-select").selectpicker()
			
			$("#subjects-select").selectpicker
					noneSelectedText: "предметы"
					multipleSeparator: "+"
			
			$timeout ->
				$scope.refreshCounts()

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

		$scope.getDay = (date) ->
			moment(date).format "dddd"

		$scope.formatTime = (time) ->
			time.slice(0, 5)

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
