angular.module "Stats", ["ui.bootstrap"]
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

	.controller "ListCtrl", ($scope) ->

		$scope.round1 = (n) ->
			Math.round(n)

		$scope.round2 = (n) ->
			return Math.round(n / 1000) * 1000

		$scope.goDates = ->
			ajaxStart()
			date_start 	= $("#date-start").val()
			date_end	= $("#date-end").val()
			redirect "stats/users?date_start=#{date_start}&date_end=#{date_end}"

		$scope.pageChanged = ->
			ajaxStart()
			redirect "stats/?page=#{$scope.currentPage}"

		$scope.pageStudentChanged = ->
			ajaxStart()
			redirect "stats/visits/total?page=#{$scope.currentPage}"

		$scope.pagePaymentChanged = ->
			ajaxStart()
			redirect "stats/payments?page=#{$scope.currentPage}"

		$scope.Schedules = {}

		$scope.dateLoad = (date)->
			return false if !$scope.days_mode

			$("##{date}").toggle()

			if $scope.Schedules[date] is undefined
				$.post "ajax/loadStatsSchedule", {date: date}, (response) ->
					for d1 in response
						if !d1.layered and !d1.cancelled
							for d2 in response
								if (d1.id isnt d2.id) and (d1.time is d2.time) and (d1.id_branch is d2.id_branch) and (d1.cabinet is d2.cabinet) and !d2.cancelled
									d1.cabinetLayered = true
									d2.cabinetLayered = true

					$scope.Schedules[date] = response
					$scope.$apply()
				, "json"


		$scope.sipNumber = (number) ->
			number = number.toString()
			return "sip:" + number.replace(/[^0-9]/g, '')

		$scope.callSip = (number) ->
			number = $scope.sipNumber(number)
			location.href = number



		$scope.clickControl = (Teacher, event) ->
			if event.shiftKey
				$scope.callSip(Teacher.phone)
			else
				redirect "teachers/edit/#{Teacher.id}"

		$scope.day = 0;
		$scope.plusDays = ->
			$.post "ajax/plusDays", {day: $scope.day++}, (response) ->
				console.log response
				$.each response, (date, stat) ->
					$scope.stats[date] = stat
				$scope.$apply()
			, "json"

		$scope.formatDate = (date)->
			moment(date).format "D MMM. YYYY"

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

		$scope.isFutureLesson = (Schedule) ->
			time_now = new Date().getTime()
			time_lesson = new Date("#{Schedule.date} #{Schedule.time}").getTime()
			return time_lesson > time_now

		$scope.isMissingLesson = (Schedule) ->
			# если урок присутствует или будет в будущем
			# (то он не считается отсутствующим)
			return false if Schedule.was_lesson or $scope.isFutureLesson(Schedule) or Schedule.cancelled


			time_now 	= new Date().getTime()
			time_lesson = new Date("#{Schedule.date} #{Schedule.time}").getTime()

			# разница в минутах между началом занятия и текущим временем
			time_difference_minutes = Math.round((time_now - time_lesson) / 1000 / 60)
			console.log "Group #{Schedule.id_group}", time_difference_minutes, new Date(), new Date("#{Schedule.date} #{Schedule.time}"), time_now, time_lesson
			# если уже больше 1:45 минут c начала занятия и до сих пор нет записи в журнале
			return true if time_difference_minutes >= 165

		angular.element(document).ready ->
			set_scope "Stats"
