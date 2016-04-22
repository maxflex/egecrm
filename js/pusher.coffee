$(document).ready ->
	vueInit() if $('.phone-app').length

# Init Vue
vueInit = ->
	Vue.config.debug = true
	Vue.component 'phone',
		props: ['user_id']
		data: ->
			show_element: false 		# show <phone>
			hide_element: false			# hide <phone>
			connected: false 			# call in progress
			determined: false			# caller determined?
			timer:
				hide_timeout: undefined
				interval: undefined 	# call length in 01:30
				diff: 0
			mango: {}
			caller: false 				# caller info
		template: '#phone-template'
		methods:
			hangup: ->
				$.post 'mango/hangup',
					call_id: this.mango.call_id
				this.endCall()
			callAppeared: ->
				this.show_element = true
				this.determined = false
				this.caller = false
				$.post 'mango/getCaller',
					phone: this.mango.from.number
				, (request) =>
					this.caller = request
					this.determined = true

					clearTimeout this.timer.hide_timeout if this.timer.hide_timeout
					this.timer.hide_timeout = setTimeout this.endCall, 10*1000

				, 'json'
			startCall: ->
				this.connected = true
				this.timer.interval = setInterval =>
					now = Math.floor(Date.now() / 1000)
					this.timer.diff = now - this.mango.timestamp
					console.log now, this.mango.timestamp, this.timer.diff
				, 1000
			endCall: ->
				clearInterval(this.timer.interval) if this.connected
				clearTimeout this.timer.hide_timeout
				this.show_element = false
				this.hide_element = false
				this.connected = false
			initPusher: ->
				pusher = new Pusher 'a9e10be653547b7106c0',
					encrypted: true
				channel = pusher.subscribe "user_#{this.user_id}"
				channel.bind 'incoming', (data) =>
					this.mango = data
					switch data.call_state
						when 'Appeared'
							this.callAppeared()
						when 'Connected'
							this.startCall()
#						when 'Disconnected'
#							this.endCall()
		computed:
			call_length: ->
				moment(parseInt(this.timer.diff) * 1000).format 'mm:ss'
			number: ->
				"+#{this.mango.from.number}"
#				"+#{n[0]} (#{n.slice(1, 4)}) #{n.slice(4, 7)}-#{n.slice(7, 9)}-#{n.slice(9, 11)}"
		ready: ->
			this.initPusher()


	new Vue
		el: '.phone-app'
