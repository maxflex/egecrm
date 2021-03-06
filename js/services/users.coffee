app.service 'UserService', ($rootScope, $q, $http, $timeout, User)->
    @loaded = $q.defer()
    @current_loaded = $q.defer()

    @users = User.query =>
        @loaded.resolve true

    $timeout =>
        @current_user = user if user = $rootScope.$$childTail.user
        @current_loaded.resolve true

    system_user =
        color: '#999999'
        login: 'system'
        id: 0
        banned: 0

    @get = (user_id) ->
        @getUser(user_id)

    @getUser = (user_id) ->
        _.findWhere(@users, {id:parseInt(user_id)}) or system_user

    @getLogin = (user_id) ->
        @getUser(parseInt(user_id)).login

    @getColor = (user_id, system_color) ->
        user = @getUser parseInt user_id
        if user is system_user and system_color
            system_color
        else
            user.color

    @getWithSystem = (only_active = true) ->
        users = @getAll only_active
        users.unshift system_user
        users

    @getAll = (only_active = true) ->
        if only_active
            _.filter @users, (user) ->
                user.rights.indexOf(34) is -1
        else
            @users

    @getBannedUsers = ->
        _.filter @users, (user) ->
            user.rights.indexOf(34) isnt -1

    @getBannedHaving = (condition_obj) ->
        _.filter @users, (user) ->
            user.rights.indexOf(34) isnt -1 and condition_obj and condition_obj[user.id]

    @getActiveInAnySystem = (with_system = true)->
        users = _.chain(@users).filter (user) ->
            user.rights.indexOf(35) is -1 or user.rights.indexOf(34) is -1
        .sortBy('login').value()
        users.unshift system_user if with_system
        users

    @getBannedInBothSystems = ->
        _.chain(@users).filter (user) ->
            user.rights.indexOf(35) isnt -1 and user.rights.indexOf(34) isnt -1
        .sortBy('login').value()

    @
