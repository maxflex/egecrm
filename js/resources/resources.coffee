app
.factory 'Sms', ($resource) ->
    $resource ':url', {id: '@id'},
        query:
            method:  'GET'
            url:     'get/sms/:number'
            isArray: true

.factory 'SmsTemplate', ($resource) ->
    $resource 'templates/ajax/get', {},
        get:
            method: 'POST'
            params: {id_template: ':id_template'}



.factory 'User', ($resource) ->
    $resource 'get/users', {id: '@id'},
        create:
            method: 'POST'