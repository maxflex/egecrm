app.service 'PhoneService', ($rootScope) ->
	@fields = ['phone', 'phone2', 'phone3']

	@call = (number) ->
		number = '' + number if typeof number isnt 'string'
		# protocol = if typeof window.orientation isnt 'undefined' then 'tel' else 'sip'
		protocol = 'tel'
		location.href = "#{protocol}:" + number.replace(/[^0-9]/g, '')

	@isMobile = (number) ->
		number = '' + number if typeof number isnt 'string'
		number and (parseInt(number[4]) is 9 or parseInt(number[1]) is 9)

	@clean = (number) ->
		number = '' + number if typeof number isnt 'string'
		number.replace /[^0-9]/gim, "";

	@format = (number) ->
		return if not number
		number = @clean number
		'+'+number.substr(0,1)+' ('+number.substr(1,3)+') '+number.substr(4,3)+'-'+number.substr(7,2)+'-'+number.substr(9,2)

	@sms = (number) ->
		$rootScope.sms_number = @clean(number)
		lightBoxShow 'sms'

	@isFull = (number) ->
		@clean(number).length is 11

	@level = (entity) ->
		level = 0
		if entity
			for field in @fields
				level++ if entity[field]
		level

	@info = (number) ->
		$.post 'mango/stats',
			number:  @clean number
		, (result) ->
			result
		, 'json'

	@isSame = (a, b) ->
		@clean(a) is @clean(b)

	@checkDublicate = (number, id_request) ->
		$.post 'ajax/checkPhone',
			phone:      number
			id_request: id_request
		, (result) ->
			result

	@addMask = (context) ->
		$ ".phone-masked", context
			.mask '+7 (999) 999-99-99',
				autoclear: false

	@
