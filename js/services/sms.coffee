app.service 'SmsService', ($rootScope, $http, Sms, PusherService) ->
    @updates = []
    @mode    = DEFUAULT_SMS_MODE
    @post_config =
        headers:
            'Content-Type': 'application/x-www-form-urlencoded'

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

            data = $.param
                message: message
                number:  number
                mass:    mass
            $http.post 'ajax/' + action, data, @post_config, 'json'

    @getTemplate = (id_template, entity) ->
        params = {}
        if entity
            params['entity_login'] = entity.login if entity.login
            params['entity_password'] = entity.password if entity.password
            params['phone'] = entity.phone if entity.phone

        data = $.param
            number: id_template
            params: params

        $http.post 'templates/ajax/get', data, @post_config

    @