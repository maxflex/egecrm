app.directive 'sms', ->
    restrict: 'E'
    templateUrl: 'directives/sms'
    scope:
        number:     '='
        templates:  '@'
        mode:       '@'
    controller: ($scope, $http, $timeout, Sms, SmsService, UserService, PhoneService, SmsTemplate) ->
        bindArguments $scope, arguments
        $scope.mass = false

        $scope.smsCount = ->
            SmsCounter.count($scope.message || '').messages

        # @todo вынести в SmsService
        $scope.send = ->
            ajaxStart()
            $scope.sms_sending = true

            $scope.SmsService.mode = $scope.mode if $scope.mode
            if promise = SmsService.send $scope.mode, $scope.number, $scope.message, $scope.mass
                promise.then (data) ->
                    ajaxEnd()
                    $scope.sms_sending = false
                    $scope.history.push(data)
                    scrollDown()
            else
                ajaxEnd()
            $scope.message = ''


        $scope.$watch 'number', (newVal, oldVal) ->
            $scope.history = SmsService.getHistory(newVal)
            scrollDown()

        scrollDown = ->
            $timeout ->
                $('#sms-history').animate({ scrollTop: $(window).height() }, 'fast');

        $scope.setTemplate = (id_template) ->
            $http.post 'templates/ajax/get',
                number: id_template
            .then (response) ->
                console.log response
                $scope.message = response