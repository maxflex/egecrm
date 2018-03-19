	logout_interval = false

	# проверка времени сессии
	listenToLogout = (user_id) ->
		pusher = new Pusher 'a9e10be653547b7106c0',
            encrypted: true
		channel = pusher.subscribe 'user_' + user_id
		channel.bind 'logout_notify', -> logoutCountdown()
		channel.bind 'continue_session', -> logoutCountdownClose()

	logoutCountdownClose = ->
		clearInterval(logout_interval)
		logout_interval = false
		$('#logout-modal').modal('hide')

	logoutCountdown = ->
		seconds = 60
		$('#logout-seconds').html(seconds)
		$('#logout-modal').modal('show')
		logout_interval = setInterval ->
			seconds--
			$('#logout-seconds').html(seconds)
			if seconds <= 1
				clearInterval(logout_interval)
				# перезагружаем страницу, к этому времени должно выбить
				setTimeout ->
					location.reload()
				, 1000
		, 1000

	continueSession = ->
		$.post "ajax/ContinueSession"
		logoutCountdownClose()

	set_scope = (app_name) ->
		@ang_scope = angular.element("[ng-app='#{app_name}']").scope()

	phoneCorrect = (element) ->
		# пустой номер телефона – это тоже правильный номер телефона
		return false if not $("#" + element).val()

	    # если есть нижнее подчеркивание, то номер заполнен не полностью
		not_filled = $("#" + element).val().match(/_/)
		not_filled is null

	deleteTeacher = (id_teacher) ->
		bootbox.confirm "Вы уверены, что хотите удалить преподавателя №#{id_teacher}?", (result) ->
			if result is true
				ajaxStart()
				$.post "teachers/ajax/delete", {id_teacher: id_teacher}
				window.history.go -1

	objectToArray = (Obj) ->
		$.map Obj, (value, index) ->
		    return [value]

	isMobilePhone = (element) ->
		phone = $("#" + element).val()

		return false if not phone

		not phone.indexOf "+7 (9"

	emailMode = (mode) ->
		$("#email-mode").val mode
		switch mode
			when 2
				$(".email-group-controls").show()
				$(".email-template-list").hide()

	ajaxStart = (element = false) ->
		if element isnt false
			$(".ajax-#{element}-button").attr("disabled", "disabled")
		NProgress.start()

	ajaxEnd = (element = false) ->
		if element isnt false
			#setTimeout ->
			$(".ajax-#{element}-button").removeAttr("disabled")
			#, 500
		NProgress.done()

	clearSelect = (ms = 50, callback = undefined) ->
		setTimeout ->
			$("option[value^='?']").remove()
			if callback isnt undefined
				callback()
		, ms