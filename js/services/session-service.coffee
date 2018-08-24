logout_interval = false

window.logoutCountdownClose = ->
	clearInterval(logout_interval)
	logout_interval = false
	$('#logout-modal').modal('hide')

window.logoutCountdown = ->
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

window.continueSession = ->
	$.post "ajax/ContinueSession"
	logoutCountdownClose()

window.listenToSession = (app_key, user_id) ->
	pusher = new Pusher(app_key, {cluster: 'eu'})
	channel = pusher.subscribe('session.' + user_id)
	channel.bind "App\\Events\\LogoutSignal", (data) ->
		switch data.action
			when 'notify' then logoutCountdown()
			when 'destroy' then redirect('/logout')
