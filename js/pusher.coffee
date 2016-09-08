vm = false

$(document).ready ->
	vm = vueInit()
	# requestsLiveUpdate()

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

# Init Vue
vueInit = ->
	Vue.config.debug = true
	Vue.config.async = false
	Vue.component 'phone',
		props: ['user_id']
		data: ->
			show_element: false 		# show <phone>
# 			connected: false 			# call in progress
			timer:
				hide_timeout: undefined
				interval: undefined 	# call length in 01:30
				diff: 0
			mango: {}
			caller: false 				# caller info
			last_call_data: false		# last call info, including user, time etc.
			answered_user: false		# answered user
		template: '#phone-template'
		methods:
			time: (seconds) ->
				moment({}).seconds(seconds).format("mm:ss")
			formatDateTime: (date) ->
				moment(date).format "DD.MM.YY в HH:mm" 
			hangup: ->
				$.post 'mango/hangup',
					call_id: this.mango.call_id
				this.endCall()
			callAppeared: ->
				this.answered_user = false
				this.show_element = true
				this.caller = this.mango.caller
				this.last_call_data = this.mango.last_call_data
				this.setHideTimeout() # disappear after
			setHideTimeout: (seconds = 15) ->
				clearTimeout this.timer.hide_timeout if this.timer.hide_timeout
				this.timer.hide_timeout = setTimeout this.endCall, seconds * 1000
			startCall: ->
# 				this.connected = true
			endCall: ->
				clearTimeout this.timer.hide_timeout
				this.show_element = false
# 				this.connected = false
			initPusher: ->
				pusher = new Pusher 'a9e10be653547b7106c0',
					encrypted: true
				channel = pusher.subscribe "user_#{this.user_id}"
				
				channel.bind 'incoming', (data) =>
					this.mango = data
					this.$log 'mango'
					switch data.call_state
						when 'Appeared'
							this.callAppeared()
						when 'Connected'
							this.startCall()
# 						when 'Disconnected'
# 							this.endCall()
							
				channel.bind 'answered', (data) =>
					console.log data
					# if current call answered
					if this.show_element
						console.log 'setting answered user to', data.answered_user
						this.answered_user = data.answered_user
						setTimeout =>
							this.endCall()
						, 2000
		computed:
			call_length: ->
				moment(parseInt(this.timer.diff) * 1000).format 'mm:ss'
			number: ->
				"+#{this.mango.from.number}"
		ready: ->
			this.initPusher()

	new Vue
		el: '.phone-app'
