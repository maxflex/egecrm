app.service 'UserService', ($rootScope, $http, $timeout)->
    @current_user = $rootScope.$$childTail.user
    $http.get 'get/users', {}
    .then (response) =>
        @users = response.data

    system_user =
        color: '#999999'
        login: 'system'
        id: 0
        banned: 0

    this.get = (user_id) ->
        this.getUser(user_id)

    this.getUser = (user_id) ->
        _.findWhere(this.users, {id:parseInt(user_id)}) or system_user

    this.getLogin = (user_id) ->
        this.getUser(parseInt(user_id)).login

    this.getColor = (user_id, system_color) ->
        user = this.getUser(parseInt(user_id))
        if user is system_user and system_color
            system_color
        else
            user.color

    this.getWithSystem = (only_active = true) ->
        users = this.getAll(only_active)
        users.unshift system_user
        users

    this.getAll = (only_active = true) ->

        if only_active
            _.where(this.users, {banned: 0})
        else
            this.users

    this.getBannedUsers = ->
        _.where this.users, {banned : 1}

    this.getBannedHaving = (condition_obj) ->
        _.filter this.users, (user) ->
            user.banned is 1 and condition_obj[user.id]

    this
