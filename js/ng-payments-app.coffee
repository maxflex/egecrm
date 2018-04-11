app = angular.module "Payments", ["ui.bootstrap"]
    .filter 'reverse', () ->
        (items) ->
            if items
                items.slice().reverse()
    .controller "LkTeacherCtrl", ($scope, $http) ->
        $scope.reverseObjKeys = (obj) -> Object.keys(obj).reverse()

        $scope.yearLabel = (year) ->
            year + '-' + (parseInt(year) + 1) + ' уч. г.'

        $scope.setYear = (year) ->
            $scope.selected_year = year

        $scope.totalSum = (date) ->
            total_sum = 0
            $.each $scope.Lessons[$scope.selected_year], (d, items) ->
                return if d > date
                day_sum = 0
                items.forEach (item) -> day_sum += item.sum
                day_sum
                total_sum += day_sum
            total_sum

        angular.element(document).ready ->
            $scope.password_correct = true;
            $.post "payments/ajaxLkTeacher", {}, (response) ->
                $scope.Lessons 	= response.Lessons
                $scope.selected_year = response.selected_year
                $scope.years = response.years
                $scope.loaded	= true # data loaded
                $scope.$apply()
            , "json"
            # bootbox.prompt {
            #     title: "Для доступа к странице введите ваш пароль",
            #     className: "modal-password-bigger",
            #     callback: (result) ->
            #         $.ajax {
            #             url: "ajax/checkTeacherPass",
            #             data: {password: result},
            #             dataType: "json",
            #             method: "post",
            #             success: (response) ->
            #                 if response == true
            #
            #                 else
            #                     $scope.password_correct = false
            #                 $scope.$apply();
            #             ,
            #             async: false,
            #         }
            #
            #     ,
            #     buttons: {
            #         confirm: {
            #             label: "Подтвердить"
            #         },
            #         cancel: {
            #             className: "display-none"
            #         }
            #     }
            # }
    # @rights-refactored
    .controller "ListCtrl", ($scope, $timeout) ->
        $scope.initSearch = ->
            $scope.search = mode : 'STUDENT', payment_type : '', confirmed : '', type : '' if not $scope.search

        $scope.yearLabel = (year) ->
            year + '-' + (parseInt(year) + 1) + ' уч. г.'

        $scope.filter = (current_page)->
            $scope.initSearch()
            $scope.search.current_page = if current_page then current_page else 1

            window.history.pushState {}, '', 'payments' + (if $scope.search.current_page > 1 then '/?page=' + $scope.search.current_page else '')
            $.cookie 'payments', JSON.stringify($scope.search), { expires: 365, path: '/' }

            $scope.getByPage()

        $scope.pageChanged = ->
            $scope.initSearch()
            window.history.pushState {}, '', 'payments' + (if $scope.search.current_page > 1 then '/?page=' + $scope.search.current_page else '')
            $scope.getByPage()

        $scope.getByPage = ->
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
            $timeout ->
                $('.watch-select option').each (index, el) ->
                    $(el).data 'subtext', $(el).attr 'data-subtext'
                    $(el).data 'content', $(el).attr 'data-content'
                $('.watch-select').selectpicker 'refresh'
                , 100

        angular.element(document).ready ->
            set_scope "Payments"
            $scope.search = JSON.parse $.cookie 'payments' if $.cookie 'payments'
            $scope.filter $scope.current_page
            $(".single-select").selectpicker()

        # done
        $scope.confirmPayment = (payment) ->
	        return if $scope.user_rights.indexOf(11) is -1
	        payment.confirmed = (payment.confirmed + 1) % 2
	        $.post "ajax/confirmPayment",
	            id:        payment.id
	            confirmed: payment.confirmed

        # Окно редактирования платежа
        $scope.editPayment = (payment) ->
            return if payment.confirmed and $scope.user_rights.indexOf(11) is -1
            $scope.new_payment = angular.copy payment
            loadMutualAccounts($scope.new_payment.id_status)
            lightBoxShow 'addpayment'

        # Показать окно добавления платежа
        $scope.addPaymentDialog = ->
            $scope.new_payment = {id_status : 0, year: $scope.academic_year, extra: {}}
            lightBoxShow 'addpayment'

        # Добавить платеж
        $scope.addPayment = ->
            # Получаем элементы (я знаю, что по-хорошему нужно получить их один раз вне функции
            # а не каждый раз, когда функция вызывается, искать их заново. Но забей. Хочу их внутри когда
            payment_date	= $("#payment-date")
            payment_year	= $("#payment-year")
            payment_category = $("#payment-category")
            payment_sum 	= $("#payment-sum")
            payment_select	= $("#payment-select")
            payment_type	= $("#paymenttypes-select")
            payment_card = $('#payment-card-number')
            payment_card_first_number = $("#payment-card-first-number")

            # Установлен ли способ оплаты
            if not parseInt($scope.new_payment.id_status)
                payment_select.focus().parent().addClass "has-error"
                return
            else
                payment_select.parent().removeClass "has-error"
                if parseInt($scope.new_payment.id_status) is 1
                    # if not $scope.new_payment.card_first_number
                    #     payment_card_first_number.focus().addClass 'has-error'
                    #     return
                    # else
                    #     payment_card_first_number.removeClass 'has-error'

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

            # Установлен ли год платежа?
            if not $scope.new_payment.year
                payment_year.focus().parent().addClass("has-error")
                return
            else
                payment_year.parent().removeClass("has-error")

            # Установлена ли категория платежа?
            if not parseInt($scope.new_payment.category)
                payment_category.focus().parent().addClass("has-error")
                return
            else
                payment_category.parent().removeClass("has-error")

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
                ajaxStart()
                $.post 'ajax/paymentAdd',
                    $scope.new_payment
                , (response) ->
                    location.reload()
                , 'json'

        # Удалить платеж
        $scope.deletePayment = ->
            return if $scope.new_payment.confirmed and $scope.user_rights.indexOf(11) is -1
            bootbox.confirm "Вы уверены, что хотите удалить платеж?", (result) ->
                if result is true
                    ajaxStart()
                    $.post "ajax/deletePayment",
                        id_payment: $scope.new_payment.id
                    , ->
                        ajaxEnd()
                        index = _.findIndex($scope.payments, {id: $scope.new_payment.id})
                        $scope.payments.splice(index, 1)
                        $timeout -> $scope.$apply()
                        lightBoxHide()

        $scope.$watch 'new_payment.id_status', (newVal, oldVal) -> loadMutualAccounts(newVal)

        loadMutualAccounts = (id_status) ->
            if parseInt(id_status) == 6
                $scope.mutual_accounts = undefined
                $.post "ajax/getLastAccounts",
                    id_teacher: $scope.new_payment.entity_id
                , (response) ->
                    $scope.mutual_accounts = response
                    $scope.$apply()
                , 'json'

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
