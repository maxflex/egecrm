app
.factory 'Sms', ($resource) ->
    $resource ':url', {id: '@id'},
        query:
            method:  'GET'
            url:     'get/sms/:number'
            isArray: true

.factory 'User', ($resource) ->
    $resource 'get/users', {id: '@id'},
        create:
            method: 'POST'