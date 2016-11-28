app.service 'SmsService', ($rootScope, $http, Sms, PusherService) ->
    @updates = []
    @mode    = DEFUAULT_SMS_MODE

    PusherService.bind 'sms', (data) =>
        @updates[data.id] = data.status
        $rootScope.$apply()

    @getStatus = (sms) ->
        switch @updates[sms.id] or sms.id_status
            when 103 then status_class = 'delivered'
            when 102 then status_class = 'inway'
            else status_class = 'not-delivered'
        status_class

    @getHistory = (number) ->
        if number
            Sms.query
                number: number

    @send = (mode, number, message, mass) ->
        if message
            switch @mode
                when 2
                    action = 'sendGroupSms'
                when 3
                    action = 'sendGroupSmsClients'
                when 4
                    action = 'sendGroupSmsTeachers'
                else
                    action = 'sendSms'

            console.log action
            $http.post 'ajax/sendSms',
                message: message
                number:  number
                mass:    mass
            , 'json'

    @