testy = 1

app = angular.module "Settings", ["ui.bootstrap", 'ngSanitize', 'mwl.calendar']
    .filter 'to_trusted', ['$sce', ($sce) ->
        return (text) ->
            return $sce.trustAsHtml(text)
    ]
    .filter 'toArray', ->
        (obj) ->
            arr = []
            $.each obj, (index, value) ->
                arr.push(value)
            return arr
    .controller "RecommendedCtrl", ($scope) ->
        $scope.yearLabel = (year) ->
            year + '-' + (parseInt(year) + 1) + ' уч. г.'
        angular.element(document).ready ->
            set_scope 'Settings'
    .controller "VacationsCtrl", ($scope, $timeout) ->
        $scope.schedulde_loaded = false
        $scope.menu = 1

        $scope.exam_days =
            9: []
            11: []

        $scope.saveExamDays = ->
            ajaxStart()
            $scope.adding = true
            $.post "ajax/saveExamDays", {exam_days: $scope.exam_days, year: $scope.current_year}, (response) ->
                $scope.adding = false
                $scope.$apply()
                ajaxEnd()

        $scope.yearLabel = (year) ->
            year + '-' + (parseInt(year) + 1) + ' уч. г.'

        $scope.setYear = (year) ->
            $.cookie("current_year", year, { expires: 365, path: '/' })
            redirect "settings/vacations?year=#{year}"


        # SCHEDULE COPY
        $scope.months = [9, 10, 11, 12, 1, 2, 3, 4, 5, 6]

        $timeout -> $scope.calendarLoaded = true

        $scope.formatDate = (date) ->
            moment(date).format "D MMMM YYYY г."

        $timeout ->
            $scope.viewDate = {}
            $scope.displayMonth = {}

            $scope.months.forEach (month) ->
                year = $scope.current_year
                year++ if month <=8
                $scope.viewDate[month] = new Date("#{year}-#{month}-01")
                $scope.displayMonth[month] = true

            $timeout -> $scope.calendarLoaded = true

        $scope.editVacation = (vacation = null) ->
            $('#schedule-modal').modal('show')
            if vacation is null
                $scope.modal_vacation = {year: $scope.current_year}
            else
                $scope.modal_vacation = _.clone(vacation)
                $scope.modal_vacation.date = moment($scope.modal_vacation.date).format('DD.MM.YY')

        $scope.saveVacation = ->
            ajaxStart()
            $('#schedule-modal').modal('hide')
            $scope.modal_vacation.date = convertDate($scope.modal_vacation.date)
            $.post "ajax/SaveVacation", $scope.modal_vacation, (response)->
                console.log('save complete', response)
                ajaxEnd()
                if not $scope.modal_vacation.id
                    $scope.modal_vacation.id = response.id
                    $scope.Vacations.push(response)
                else
                    index = _.findIndex($scope.Vacations, {id: $scope.modal_vacation.id})
                    $scope.Vacations[index] = _.clone($scope.modal_vacation)
                $scope.$apply()
            , "json"

        $scope.deleteVacation = (Vacation) ->
            ajaxStart()
            $.post "ajax/DeleteVacation", {id: Vacation.id}, (response) ->
                index = _.findIndex($scope.Vacations, {id: Vacation.id})
                $scope.Vacations.splice(index, 1)
                $scope.$apply()
                ajaxEnd()

        $scope.monthName = (month) ->
            month_name = moment().month(month - 1).format "MMMM"

        angular.element(document).ready ->
            set_scope 'Settings'

    .controller "CabinetsCtrl", ($scope) ->
        angular.element(document).ready ->
            set_scope 'Settings'
