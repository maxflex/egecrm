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
    .controller "VocationsCtrl", ($scope, $timeout) ->
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
            redirect "settings/vocations?year=#{year}"


        # SCHEDULE COPY
        $scope.months = [9, 10, 11, 12, 1, 2, 3, 4, 5, 6]

        $timeout ->
            $scope.viewDate = {}
            $scope.displayMonth = {}

            # получить месяц первого занятия и отображать календарь начаная с него
            first_lesson_month = moment($scope.Group.first_schedule).format("M")
            year = $scope.Group.year
            year++ if first_lesson_month <=8
            first_lesson_date = new Date("#{year}-#{first_lesson_month}-01")
            $scope.months.forEach (month) ->
                year = $scope.Group.year
                year++ if month <=8
                $scope.viewDate[month] = new Date("#{year}-#{month}-01")
                $scope.displayMonth[month] = true

            $timeout -> $scope.calendarLoaded = true


        $scope.calendarTitle = 'test'
        $scope.events = {}

        getColor = (Schedule) ->
            return '#337ab7' if Schedule.was_lesson
            return '#c0c0c0' if Schedule.cancelled
            return '#5cb85c'

        $scope.formatDate = (date) ->
            moment(date).format "D MMMM YYYY г."

        $scope.countNotCancelled = (Schedule) ->
            _.where(Schedule, { cancelled: 0 }).length

        $scope.lessonCount = ->
            Object.keys($scope.Group.day_and_time).length

        $scope.scheduleModal = (schedule = null) ->
            $('#schedule-modal').modal('show')
            if schedule is null
                $scope.modal_schedule = {id_group: $scope.Group.id}
            else
                $scope.modal_schedule = _.clone(schedule)
                $scope.modal_schedule.date = moment($scope.modal_schedule.date).format('DD.MM.YYYY')

        $scope.saveSchedule = ->
            ajaxStart()
            $('#schedule-modal').modal('hide')
            $scope.modal_schedule.date = convertDate($scope.modal_schedule.date)
            $.post "groups/ajax/SaveSchedule", $scope.modal_schedule, (response)->
                ajaxEnd()
                if not $scope.modal_schedule.id
                    $scope.modal_schedule.id = response.id
                    $scope.Group.Schedule.push($scope.modal_schedule)
                else
                    index = _.findIndex($scope.Group.Schedule, {id: $scope.modal_schedule.id})
                    $scope.Group.Schedule[index] = _.clone($scope.modal_schedule)
                $scope.$apply()

        $scope.getCabinet = (id) ->
            _.findWhere($scope.all_cabinets, {id: parseInt(id)})

        $scope.deleteSchedule = (Schedule) ->
            ajaxStart()
            $.post "groups/ajax/DeleteSchedule", {id: Schedule.id}, (response) ->
                index = _.findIndex($scope.Group.Schedule, {id: Schedule.id})
                $scope.Group.Schedule.splice(index, 1)
                $scope.$apply()
                ajaxEnd()

        $scope.getPastLesson = (Schedule) ->
            _.findWhere($scope.past_lessons, {lesson_date: Schedule.date, lesson_time: Schedule.time})

        $scope.lessonStarted = (Schedule) ->
            lesson_time = new Date(Schedule.date + " " + Schedule.time).getTime()
            lesson_time < new Date().getTime()

        $scope.monthName = (month) ->
            month_name = moment().month(month - 1).format "MMMM"

        angular.element(document).ready ->
            set_scope 'Settings'

    .controller "CabinetsCtrl", ($scope) ->
        angular.element(document).ready ->
            set_scope 'Settings'
