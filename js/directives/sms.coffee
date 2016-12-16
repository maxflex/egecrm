app.directive 'sms', ->
    restrict: 'E'
    templateUrl: 'directives/sms'
    scope:
        number:     '='
        templates:  '@'
        mode:       '@'
        counts:     '='
        groupId:    '='
        mass:       '='
    controller: ($scope, $http, $timeout, Sms, SmsService, UserService, PhoneService) ->
        bindArguments $scope, arguments

        $scope.smsCount = ->
            SmsCounter.count($scope.message || '').messages

        # @todo вынести в SmsService
        $scope.send = ->
            ajaxStart()
            $scope.sms_sending = true

            if promise = SmsService.send $scope.number, $scope.message
                promise.then (response) ->
                    ajaxEnd()
                    $scope.sms_sending = false

                    if $scope.mass
                        notifySuccess 'Отправлено ' + response.data + ' СМС'
                        lightBoxHide()
                    else
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
            _.extend $scope.SmsService.params,
                to_students: true
                to_representatives: false
                to_teachers: true
                mode: $scope.mode
                groupId: $scope.groupId

        init()