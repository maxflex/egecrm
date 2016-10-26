vm = false

$(document).ready ->
	vm = vueInit()

vueInit = ->
	Vue.config.debug = true
	Vue.config.async = false
	Vue.component 'phone',
		props: ['user_id', 'type', 'key', 'cluster']
		data: ->
			show_element: false 		# show <phone>
			#connected: false 			# call in progress
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
				moment.utc(seconds * 1000).format("mm:ss")
			formatDateTime: (date) ->
				moment(new Date(date * 1000)).format "DD.MM.YY в HH:mm"
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
				pusher = new Pusher this.key,
					encrypted: true
					cluster: this.cluster
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
				# 						    this.endCall()

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
