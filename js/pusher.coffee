requestsLiveUpdate = ->
	pusher = new Pusher 'a9e10be653547b7106c0',
		encrypted: true
	channel = pusher.subscribe "requests"
	channel.bind 'incoming', (data) =>
		console.log(data)
		request_count = $('#request-count')
		request_counter = $('#request-counter')
		animate_speed = 7000
		request_counter.removeClass('text-success').removeClass('text-danger').css('opacity', 1)
		if data isnt null
		    request_count.text(parseInt(request_count.text()) - 1)
		    request_count.animate({'background-color': '#158E51'}, animate_speed / 2).animate({'background-color': '#777'}, animate_speed / 2)
		    request_counter.text('-1').addClass('text-success').animate({opacity: 0}, animate_speed)
		else
		    request_count.text(parseInt(request_count.text()) + 1)
		    request_count.animate({'background-color': '#A94442'}, animate_speed / 2).animate({'background-color': '#777'}, animate_speed / 2)
		    request_counter.text('+1').addClass('text-danger').animate({opacity: 0}, animate_speed)