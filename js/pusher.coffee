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
			connected: false 			# call in progress
			determined: false			# caller determined?
			timer:
				hide_timeout: undefined
				interval: undefined 	# call length in 01:30
				diff: 0
			mango: {}
			caller: false 				# caller info
			last_call_data: false
			answered_user: false
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
				this.show_element = true
				this.determined = false
				this.caller = false
				this.last_call_data = false
				$.post 'mango/getCaller',
					phone: this.mango.from.number
				, (response) =>
					this.caller = response
					this.determined = true
					this.setHideTimeout()
				, 'json'
				# асинхронно посылаем запрос на получение данных о последнем разговоре,
				# чтоб не тормозило основное определение звонящего
				$.post 'mango/getLastCallData',
					phone: this.mango.from.number
				, (response) =>
					this.last_call_data = response
				, 'json'
			setHideTimeout: (seconds) ->
				seconds = 100 if not seconds
				clearTimeout this.timer.hide_timeout if this.timer.hide_timeout
				this.timer.hide_timeout = setTimeout this.endCall, seconds*1000
			startCall: ->
				this.connected = true
			endCall: ->
				clearTimeout this.timer.hide_timeout
				$.post 'mango/getAnsweredUser',
					phone: this.mango.from.number
				, (response) =>
					this.answered_user = response
					this.last_call_data = false
					vue = this
					setTimeout ->
						vue.show_element = false
						vue.connected = false
						vue.answered_user = false
					, 2000
				, 'json'

			saveState: ->
				answered_user = this.mango.to.extension #необязательно. потом уберу.
				if answered_user == this.user_id
					caller_type = if this.caller.type then this.caller.type else ''
					$.post 'mango/saveCallState',
						phone: this.mango.from.number
						user_id: this.user_id

			initPusher: ->
				pusher = new Pusher 'a9e10be653547b7106c0',
					encrypted: true
				channel = pusher.subscribe "user_#{this.user_id}"
				channel.bind 'incoming', (data) =>
					console.log 'MANGO RECEIVED', data
					this.mango = data
					switch data.call_state
						when 'Appeared'
							this.callAppeared()
						when 'Connected'
							this.saveState()
							this.startCall()
						when 'Disconnected'
							this.endCall()
		computed:
			call_length: ->
				moment(parseInt(this.timer.diff) * 1000).format 'mm:ss'
			number: ->
				"+#{this.mango.from.number}"
		ready: ->
			this.initPusher()

	new Vue
		el: '.phone-app'
