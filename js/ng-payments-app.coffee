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
        $scope.initSearch = ->
            $scope.search = mode : 'STUDENT', payment_type : '', confirmed : '', type : '' if not $scope.search
        $scope.filter = (current_page)->
            console.log 'filter' # инициализируем фильтры если еще не были установлены
            $scope.initSearch()
            $scope.search.current_page = if current_page then current_page else 1

            window.history.pushState {}, '', 'payments' + (if $scope.search.current_page > 1 then '/?page=' + $scope.search.current_page else '')
            $.cookie 'payments', JSON.stringify($scope.search), { expires: 365, path: '/' }

            $scope.getByPage()
        
        $scope.pageChanged = ->
            console.log 'page changed ' + $scope.search.current_page
            $scope.initSearch()
            window.history.pushState {}, '', 'payments' + (if $scope.search.current_page > 1 then '/?page=' + $scope.search.current_page else '')
            $scope.getByPage()

        $scope.getByPage = ->
            console.log 'get by page'
            frontendLoadingStart() and $scope.loading = true if not $scope.loading

            $.post "payments/AjaxGetPayments",
                search: $scope.search
            , (response) ->
                frontendLoadingEnd() and $scope.loading = false
                $scope.payments = response.payments
                $scope.counts = response.counts
                $scope.refreshCounts()
                $scope.$apply()
            , "json"

        $scope.refreshCounts = ->
            console.log 'refresh counts'
            $timeout ->
                $('.watch-select option').each (index, el) ->
                    $(el).data 'subtext', $(el).attr 'data-subtext'
                    $(el).data 'content', $(el).attr 'data-content'
                $('.watch-select').selectpicker 'refresh'
                , 100

        angular.element(document).ready ->
            console.log 'ready'
            set_scope "Payments"
            $scope.search = JSON.parse $.cookie 'payments' if $.cookie 'payments'
            $scope.filter $scope.current_page
            $(".single-select").selectpicker()

        # done
        $scope.confirmPayment = (payment) ->
            bootbox.prompt {
                title: "Введите пароль",
                className: "modal-password",
                callback: (result) ->
                    if result is null
                    else if hex_md5(result) is payments_hash
                        payment.confirmed = (payment.confirmed + 1) % 2
                        $.post "ajax/confirmPayment",
                            id:        payment.id
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
                    },
                }
                onEscape: true
            }

        # Окно редактирования платежа
        $scope.editPayment = (payment) ->
            if not payment.confirmed
                $scope.new_payment = angular.copy payment
                $scope.$apply()
                lightBoxShow 'addpayment'
                return

            bootbox.prompt
                title: "Введите пароль"
                className: "modal-password"
                callback: (result) ->
                    if result is null
                    else if hex_md5(result) is payments_hash
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
            payment_card = $('#payment-card-number')
            payment_card_first_number = $("#payment-card-first-number")

            # Установлен ли способ оплаты
            if !$scope.new_payment.id_status
                payment_select.focus().parent().addClass "has-error"
                return
            else
                payment_select.parent().removeClass "has-error"
                if parseInt($scope.new_payment.id_status) is 1
                    if not $scope.new_payment.card_first_number
                        payment_card_first_number.focus().addClass 'has-error'
                        return
                    else
                        payment_card_first_number.removeClass 'has-error'

                    if not $scope.new_payment.card_number
                        payment_card.focus().addClass 'has-error'
                        return
                    else
                        payment_card.removeClass 'has-error'

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
                $scope.new_payment.entity_id		= $scope.student.id
                $scope.new_payment.entity_type		= $scope.new_payment.Entity.type
                $scope.new_payment.id_user			= $scope.user.id

                ajaxStart()
                $.post 'ajax/paymentAdd',
                    $scope.new_payment
                , (response) ->
                    $scope.new_payment.id = response.id
                    $scope.new_payment.document_number = response.document_number

                    # Инициализация если не установлено
                    $scope.payments = initIfNotSet $scope.payments

                    $scope.payments.push $scope.new_payment

                    $scope.new_payment = {id_status : 0}

                    $scope.$apply()

                    ajaxEnd()
                    lightBoxHide()
                , 'json'

        # Удалить платеж
        $scope.deletePayment = (index, payment) ->
            if not payment.confirmed
                bootbox.confirm "Вы уверены, что хотите удалить платеж?", (result) ->
                    if result is true
                        $.post "ajax/deletePayment",
                            id_payment: payment.id
                        $scope.payments.splice index, 1
                        $scope.$apply()
            else
                bootbox.prompt {
                    title: "Введите пароль",
                    className: "modal-password",
                    callback: (result) ->
                        if result is null
                        else if hex_md5(result) is payments_hash
                            bootbox.confirm "Вы уверены, что хотите удалить платеж?", (result) ->
                                if result is true
                                    $.post "ajax/deletePayment",
                                        id_payment: payment.id
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

        $scope.printPKO = (payment) ->
            $scope.print_mode = 'pko'
            $scope.PrintPayment = payment
            $scope.Representative = $scope.representative
            $scope.$apply()
            printDiv $scope.print_mode + "-print"

#         $scope.printBill = (payment) ->
#             $scope.print_mode = 'bill'
#             $scope.PrintPayment = payment 
#             $scope.$apply()
#             printDiv $scope.print_mode + "-print"

        # форматировать дату
        $scope.formatDate = (date) ->
            dateOut = new Date date
            dateOut