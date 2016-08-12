$(document).ready ->
	vm = vueInit()

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
				$.post 'mango/getCaller',
					phone: this.mango.from.number
				, (request) =>
					this.caller = request
					this.determined = true
					this.setHideTimeout()
				, 'json'
			setHideTimeout: (seconds) ->
				seconds = 100 if not seconds
				clearTimeout this.timer.hide_timeout if this.timer.hide_timeout
				this.timer.hide_timeout = setTimeout this.endCall, seconds*1000
			startCall: ->
				this.connected = true
			endCall: ->
				clearTimeout this.timer.hide_timeout
				this.show_element = false
				this.connected = false
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
