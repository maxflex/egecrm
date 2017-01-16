app.service 'SmsService', ($rootScope, $http, Sms, PusherService) ->
    @updates        = []
    @mode           = 'default'
    @params         = {}
    @post_config    =
        headers:
            'Content-Type': 'application/x-www-form-urlencoded'

    PusherService.bind 'sms', (data) =>
        @updates[parseInt data.id] = parseInt data.status
        $rootScope.$apply()

    @getStatus = (sms) ->
        switch @updates[sms.id] or sms.id_status
            when 103 then status_class = 'delivered'
            when 102 then status_class = 'inway'
            else status_class = 'not-delivered'
        status_class

    @getHistory = (number) ->
        Sms.query
            number: number

    @send = (number, message) ->
        if message
            switch @params.mode
                when 'group'
                    action = 'sendGroupSms'
                when 'client'
                    action = 'sendGroupSmsClients'
                when 'teacher'
                    action = 'sendGroupSmsTeachers'
                else
                    action = 'sendSms'

            _.extend @params,
                message: message
                number:  number

            data = $.param @params

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