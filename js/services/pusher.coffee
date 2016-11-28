app.service 'PusherService', ($http, $q, UserService) ->
    @inited = $q.defer()

    @bind = (channel, callback) ->
        init() if @pusher is undefined

        @inited.promise.then =>
            @channel.bind "#{ channel }", callback

    init = =>
        @pusher = new Pusher 'a9e10be653547b7106c0',
            encrypted: true
        UserService.current_loaded.promise.then =>
            @channel = @pusher.subscribe 'user_' + UserService.current_user.id
            @inited.resolve true

    @