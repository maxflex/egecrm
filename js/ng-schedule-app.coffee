app = angular.module "Schedule", ['mwl.calendar']
    .controller "MainCtrl", ($scope, $timeout) ->
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
                $scope.displayMonth[month] = $scope.viewDate[month] >= first_lesson_date

            $timeout -> $scope.calendarLoaded = true

        $scope.calendarTitle = 'test'
        $scope.events = {}

        $scope.$watchCollection 'Group.Schedule', (newVal, oldVal) ->
            $scope.events = {}
            newVal.forEach (Schedule) ->
                month = moment(Schedule.date).format('M')
                $scope.events[month] = [] if $scope.events[month] is undefined
                $scope.events[month].push
                    startsAt: new Date(Schedule.date)
                    color:
                        primary: getColor(Schedule)

        getColor = (Schedule) ->
            return '#337ab7' if Schedule.was_lesson
            return '#c0c0c0' if Schedule.cancelled
            return '#5cb85c'

        $scope.formatDate = (date) ->
            moment(date).format "DD.MM.YY"

        $scope.countNotCancelled = (Schedule) ->
            _.where(Schedule, { cancelled: 0 }).length

        # установка времени филиала и кабинета из настроек группы @time-checked
        $scope.setParamsFromGroup = (Group) ->
            $.each $scope.Group.Schedule, (i, schedule) ->
                return if schedule.was_lesson

                # обновляем только после ответа сервера
                v = angular.copy(schedule)

                # if not v.time
                d = moment(v.date).format("d")
                d = parseInt d
                d = 7 if d is 0

                # если в этот день установлено расписание и время в группе. иначе не устанавливать
                # console.log Group.day_and_time, d, v.date, Group.day_and_time[d]
                if Group.day_and_time[d] isnt undefined and Group.day_and_time[d].length is 1
                    v.time    = $scope.Time[Group.day_and_time[d][0].id_time]
                    v.cabinet = Group.day_and_time[d][0].id_cabinet
                else
                    v.time = null
                    v.cabinet = ''

                # @time-refactored
                ajaxStart()
                $.post "groups/ajax/TimeFromGroup",
                    id: v.id
                    time: v.time
                    cabinet: v.cabinet
                , ->
                    ajaxEnd()
                    schedule.time = v.time
                    schedule.cabinet = v.cabinet
                    $scope.$apply()

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
            set_scope 'Schedule'
