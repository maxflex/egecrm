app.service 'PhoneService', ($rootScope) ->
    @call = (number) ->
        location.href = "sip:" + number.replace(/[^0-9]/g, '')

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
    @