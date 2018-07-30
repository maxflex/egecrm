app = angular.module "Stats", ["ui.bootstrap"]
	.config [
	  '$compileProvider'
	  ($compileProvider) ->
	    $compileProvider.aHrefSanitizationWhitelist /^\s*(https?|ftp|mailto|chrome-extension|sip):/
	    # Angular before v1.2 uses $compileProvider.urlSanitizationWhitelist(...)
	    return
	]
	.filter 'to_trusted', ['$sce', ($sce) ->
        return (text) ->
            return $sce.trustAsHtml(text)
	]

	.filter 'hideZero', ->
		(item) ->
			if item > 0 then item else null

	.controller "ListCtrl", ($scope, PhoneService) ->
		bindArguments $scope, arguments
		$scope.round1 = (n) ->
			Math.round(n)

		$scope.round2 = (n) ->
			return Math.round(n / 1000) * 1000

		$scope.goDates = ->
			ajaxStart()
			date_start 	= $("#date-start").val()
			date_end	= $("#date-end").val()
			redirect "stats/users?date_start=#{date_start}&date_end=#{date_end}"

		$scope.pageChanged = (group)->
			ajaxStart()
			redirect "stats/?group=#{group}&page=#{$scope.currentPage}"

		$scope.pageStudentChanged = ->
			ajaxStart()
			redirect "stats/visits/total?page=#{$scope.currentPage}"

		$scope.pagePaymentChanged = (group)->
			ajaxStart()
			redirect "stats/payments" + (if $scope.mode is 'teachers' then '/teachers' else '') + "?group=#{group}&page=#{$scope.currentPage}"

		$scope.Lessons = {}

		$scope.dateLoad = (date)->
			return false if !$scope.days_mode

			$("##{date}").toggle()

			if $scope.Lessons[date] is undefined
				$.post "ajax/loadStatsSchedule", {date: date}, (response) ->
					$scope.Lessons[date] = response
					$scope.$apply()
				, "json"

		$scope.clickControl = (Teacher, event) ->
			if event.shiftKey
				PhoneService.call(Teacher.phone)
			else
				redirect "teachers/edit/#{Teacher.id}"

		$scope.day = 2;
		$scope.plusDays = ->
			$.post "ajax/plusDays", {day: $scope.day++}, (response) ->
				console.log response
				$.each response, (date, stat) ->
					$scope.stats[date] = stat
				$scope.$apply()
			, "json"

		$scope.formatDate = (date)->
			moment(date).format "D MMM. YYYY"

		$scope.isToday = (date)->
			date is moment().format "YYYY-MM-DD"

		$scope.isFuture = (date) ->
			date >= moment().format "YYYY-MM-DD"

		$scope.isWeekend = (date) ->
			moment(date).isoWeekday() in [6, 7]

		$scope.sortByDate = (stats) ->
			tmp = []
			$.each stats, (date, obj) ->
				obj.date = date
				tmp.push obj

			_.sortBy(tmp, 'date').reverse()

		$scope.formatDay = (day) ->
			$scope.weekdays[day].short

		$scope.toggleDiv = (id)->
			$(".user-#{id}").slideToggle()

		angular.element(document).ready ->
			set_scope "Stats"
