angular.module "Payments", ["ui.bootstrap"]
    .filter 'reverse', () ->
        (items) ->
            if items
                items.slice().reverse()

    .controller "LkTeacherCtrl", ($scope, $http) ->

        $scope.formatDate = (date) ->
            moment(date).format("D MMMM YYYY")

        $scope.formatTime = (time) ->
            return time.substr(0, 5)

        $scope.totalPaid = ->
            sum = 0
            $.each $scope.payments, (i, payment) ->
                sum += payment.sum
            sum

        $scope.totalEarned = ->
            sum = 0
            $.each $scope.Data, (i, data) ->
                sum += data.teacher_price
            sum

        $scope.toBePaid = ->
            $scope.totalEarned() - $scope.totalPaid()

        angular.element(document).ready ->
            bootbox.prompt {
                title: "Для доступа к странице введите ваш пароль",
                className: "modal-password-bigger",
                callback: (result) ->
                    $.ajax {
                        url: "ajax/checkTeacherPass",
                        data: {password: result},
                        dataType: "json",
                        method: "post",
                        success: (response) ->
                            if response == true
                                $scope.password_correct = true;
                                $.post "payments/ajaxLkTeacher", {}, (response) ->
                                    console.log response
                                    $scope.payments = response.payments
                                    $scope.Data 	= response.Data
                                    $scope.loaded	= true # data loaded
                                    $scope.$apply()
                                , "json"
                            else
                                $scope.password_correct = false
                            $scope.$apply();
                        ,
                        async: false,
                    }

                ,
                buttons: {
                    confirm: {
                        label: "Подтвердить"
                    },
                    cancel: {
                        className: "display-none"
                    }
                }
            }

    .controller "ListCtrl", ($scope, $timeout) ->
        $scope.filter = ->
            $scope.search = mode : 'clientclient', payment_type : '', confirmed : '' if not $scope.search
            $.cookie "payments", JSON.stringify($scope.search), { expires: 365, path: '/' }
            $scope.current_page = 1
            $scope.getByPage()

        $scope.pageChanged = ->
            console.log $scope.current_page
            window.history.pushState {}, '', 'payments/?page=' + $scope.current_page if $scope.current_page > 1
            $scope.getByPage()

        $scope.getByPage = ->
            frontendLoadingStart()

            $scope.search = if $.cookie("payments") then JSON.parse($.cookie("payments")) else {}
            $scope.search.current_page = $scope.current_page

            $.post "payments/AjaxGetPayments",
                search: $scope.search
            , (response) ->
                frontendLoadingEnd()
                $scope.payments = response.payments
                $scope.counts = response.counts
                $scope.refreshCounts()
                $scope.$apply()
            , "json"

        $scope.refreshCounts = ->
            $timeout ->
                $('.watch-select option').each (index, el) ->
                    $(el).data 'subtext', $(el).attr 'data-subtext'
                    $(el).data 'content', $(el).attr 'data-content'
                $('.watch-select').selectpicker 'refresh'
                , 100

        angular.element(document).ready ->
            set_scope "Payments"
            $scope.getByPage()
            $(".single-select").selectpicker()

        $scope.paymentsFilter = (payment) ->
            switch $scope.filter
                when 1 then payment.id_status == 5
                when 2 then payment.id_status == 4
                when 3 then payment.id_status == 2
                when 4 then payment.id_status == 1
                when 5 then not payment.confirmed
                else payment

        # done
        $scope.confirmPayment = (payment) ->
            bootbox.prompt {
                title: "Введите пароль",
                className: "modal-password",
                callback: (result) ->
                    if result == "363"
                        payment.confirmed = payment.confirmed + 1 % 2
                        $.post "ajax/confirmPayment",
                            id:        payment.id
                            type:      payment.Entity.type
                            confirmed: payment.confirmed
                        $scope.$apply()
                    else if result != null
                        $('.bootbox-form').addClass('has-error').children().first().focus()
                        $('.bootbox-input-text').on 'keydown', ->
                            $(this).parent().removeClass 'has-error'

                        return false
                ,
                buttons: {
                    confirm: {
                        label: "Подтвердить"
                    },
                    cancel: {
                        className: "display-none"
                    }
                }
            }

        # Окно редактирования платежа
        $scope.editPayment = (payment) ->
            if not payment.confirmed
                $scope.new_payment = angular.copy(payment)
                $scope.$apply()
                lightBoxShow 'addpayment'
                return

            bootbox.prompt
                title: "Введите пароль"
                className: "modal-password"
                callback: (result) ->
                    if result == "363"
                        $scope.new_payment = angular.copy payment
                        $scope.$apply()
                        lightBoxShow 'addpayment'
                    else if result != null
                        $('.bootbox-form').addClass('has-error').children().first().focus()
                        $('.bootbox-input-text').on 'keydown', ->
                            $(this).parent().removeClass 'has-error'
                        return false
                buttons:
                    confirm:
                        label: "Подтвердить"
                    cancel:
                        className: "display-none"

        # Показать окно добавления платежа
        $scope.addPaymentDialog = ->
            $scope.new_payment = {id_status : 0}
            lightBoxShow 'addpayment'

        # Добавить платеж
        $scope.addPayment = ->
            # Получаем элементы (я знаю, что по-хорошему нужно получить их один раз вне функции
            # а не каждый раз, когда функция вызывается, искать их заново. Но забей. Хочу их внутри когда
            payment_date	= $("#payment-date")
            payment_sum 	= $("#payment-sum")
            payment_select	= $("#payment-select")
            payment_type	= $("#paymenttypes-select")

            # Установлен ли способ оплаты
            if !$scope.new_payment.id_status
                payment_select.focus().parent().addClass "has-error"
            else
                payment_select.parent().removeClass "has-error"

            # Установлен ли тип платежа?
            if  $scope.new_payment is 'teacher' and !$scope.new_payment.id_type
                payment_type.focus().parent().addClass "has-error"
                return
            else
                payment_type.parent().removeClass "has-error"

            # Установлена ли сумма платежа?
            if not $scope.new_payment.sum
                payment_sum.focus().parent().addClass "has-error"
                return
            else
                payment_sum.parent().removeClass "has-error"

            # Установлена ли дата платежа?
            if !$scope.new_payment.date
                payment_date.focus().parent().addClass "has-error"
                return
            else
                payment_date.parent().removeClass "has-error"

            # редактирование платежа, если есть ID
            if $scope.new_payment.id
                #delete $scope.new_payment.Entity
                ajaxStart()
                _.extend $scope.new_payment, { type: $scope.new_payment.Entity.type }
                $.post "ajax/paymentEdit", $scope.new_payment, (response) ->
                    angular.forEach $scope.payments, (payment, i) ->
                        if payment.id == $scope.new_payment.id
                            $scope.payments[i] = $scope.new_payment
                            $scope.$apply()
                    ajaxEnd()
                    lightBoxHide()
            else
                # иначе сохранение платежа
                # Добавляем дополнительные данные в new_payment
                $scope.new_payment.user_login		= $scope.user.login
                $scope.new_payment.first_save_date	= moment().format('YYYY-MM-DD HH:mm:ss')
                $scope.new_payment.id_student		= $scope.student.id
                $scope.new_payment.id_user			= $scope.user.id

                ajaxStart()
                $.post "ajax/paymentAdd",
                    $scope.new_payment
                    type: $scope.new_payment.Entity.type
                , (response) ->
                    $scope.new_payment.id = response

                    # Инициализация если не установлено
                    $scope.payments = initIfNotSet $scope.payments

                    $scope.payments.push $scope.new_payment

                    $scope.new_payment = {id_status : 0}

                    $scope.$apply()

                    ajaxEnd()
                    lightBoxHide()

        # Удалить платеж
        $scope.deletePayment = (index, payment) ->
            if not payment.confirmed
                bootbox.confirm "Вы уверены, что хотите удалить платеж?", (result) ->
                    if result is true
                        $.post "ajax/deletePayment",
                            id_payment: payment.id
                            type: payment.Entity.type
                        $scope.payments.splice index, 1
                        $scope.$apply()
            else
                bootbox.prompt {
                    title: "Введите пароль",
                    className: "modal-password",
                    callback: (result) ->
                        if result == "363"
                            bootbox.confirm "Вы уверены, что хотите удалить платеж?", (result) ->
                                if result is true
                                    $.post "ajax/deletePayment",
                                        id_payment: payment.id
                                        type: payment.Entity.type
                                    $scope.payments.splice index, 1
                                    $scope.$apply()
                        else if result != null
                            $('.bootbox-form').addClass('has-error').children().first().focus()
                            $('.bootbox-input-text').on 'keydown', ->
                                $(this).parent().removeClass 'has-error'
                            return false
                    buttons: {
                        confirm: {
                            label: "Подтвердить"
                        },
                        cancel: {
                            className: "display-none"
                        }
                    }
                }

        # форматировать дату
        $scope.formatDate = (date) ->
            dateOut = new Date date
            dateOut