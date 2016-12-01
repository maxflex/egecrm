app.directive 'sms', ->
    restrict: 'E'
    templateUrl: 'directives/sms'
    scope:
        number:     '='
        templates:  '@'
        mode:       '@'
        counts:     '='
    controller: ($scope, $http, $timeout, Sms, SmsService, UserService, PhoneService) ->
        bindArguments $scope, arguments

        $scope.smsCount = ->
            SmsCounter.count($scope.message || '').messages

        # @todo вынести в SmsService
        $scope.send = ->
            ajaxStart()
            $scope.sms_sending = true

            if promise = SmsService.send $scope.mode, $scope.number, $scope.message, $scope.mass
                promise.then (response) ->
                    ajaxEnd()
                    $scope.sms_sending = false
                    $scope.history.unshift response.data
                    $timeout ->
                        $scope.$apply()
                    scrollUp()
            else
                ajaxEnd()
            $scope.message = ''


        $scope.$watch 'number', (newVal, oldVal) ->
            $scope.history = SmsService.getHistory newVal
            scrollUp()

        scrollUp = ->
            $timeout ->
                $('#sms-history').animate({ scrollTop: 0 }, 'fast');

        $scope.setTemplate = (id_template) ->
            SmsService.getTemplate id_template, $scope.$parent.student || $scope.$parent.Teacher
            .then (response) ->
                $scope.message = response.data

        init = ->
            $scope.SmsService.mass = false
            $scope.SmsService.to_students = true
            $scope.SmsService.to_representatives = false
            $scope.SmsService.to_teachers = true
            $scope.SmsService.mode = $scope.mode if $scope.mode

        init()