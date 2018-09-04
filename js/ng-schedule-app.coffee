app = angular.module "Schedule", ['mwl.calendar']
    .controller "MainCtrl", ($scope, $timeout) ->
        $scope.months = [9, 10, 11, 12, 1, 2, 3, 4, 5, 6]

        $timeout ->
            $scope.viewDate = {}
            $scope.displayMonth = {}

            # получить месяц первого занятия и отображать календарь начаная с него
            first_lesson_month = moment($scope.Group.first_lesson_date).format("M")
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

        $scope.$watchCollection 'Lessons', (newVal, oldVal) ->
            $scope.events = {}
            newVal.forEach (Lesson) ->
                month = moment(Lesson.lesson_date).format('M')
                $scope.events[month] = [] if $scope.events[month] is undefined
                $scope.events[month].push
                    startsAt: new Date(Lesson.lesson_date)
                    color:
                        primary: getColor(Lesson)

        getColor = (Lesson) ->
            return '#337ab7' if Lesson.is_conducted
            return '#c0c0c0' if Lesson.cancelled
            return '#5cb85c'

        $scope.formatDate = (date) ->
            moment(date).format "DD.MM.YY"

        $scope.countNotCancelled = ->
            _.where($scope.Lessons, { cancelled: 0 }).length

        $scope.lessonCount = ->
            Object.keys($scope.Group.day_and_time).length

        $scope.duplicateLessons = ->
            date = (parseInt($scope.Group.year) + 1) + '-06-01'
            # date = '2016-10-05'
            current_date = moment($scope.Lessons[$scope.Lessons.length - 1].lesson_date).add(7, 'days').format("YYYY-MM-DD")
            index = 0
            bug_index = 0
            to_be_duplicated = {}
            while current_date < date
                if $scope.special_dates.vacations.indexOf(current_date) is -1 && _.find($scope.Lessons, {lesson_date: current_date}) is undefined
                    index++
                    to_be_duplicated[index] = _.clone($scope.Lessons[$scope.Lessons.length - 1])
                    delete to_be_duplicated[index].id
                    to_be_duplicated[index].lesson_date = current_date
                    $.post "groups/ajax/SaveLesson", to_be_duplicated[index], (response)->
                        bug_index++
                        to_be_duplicated[bug_index].id = response.id
                        console.log(response.id, to_be_duplicated[bug_index])
                        $scope.Lessons.push(to_be_duplicated[bug_index])
                        $scope.$apply()
                    , 'json'
                current_date = moment(current_date).add(7, 'days').format("YYYY-MM-DD")

        $scope.lessonModal = (lesson = null) ->
            $('#schedule-modal').modal('show')
            if lesson is null
                $scope.modal_lesson = {id_group: $scope.Group.id, id_teacher: $scope.Group.id_teacher}
            else
                $scope.modal_lesson = _.clone(lesson)
                $scope.modal_lesson.lesson_date = moment($scope.modal_lesson.lesson_date).format('DD.MM.YY')

        $scope.saveLesson = ->
            ajaxStart()
            $('#schedule-modal').modal('hide')
            $scope.modal_lesson.lesson_date = convertDate($scope.modal_lesson.lesson_date)
            $.post "groups/ajax/SaveLesson", $scope.modal_lesson, (response)->
                ajaxEnd()
                if not $scope.modal_lesson.id
                    $scope.modal_lesson.id = response.id
                    $scope.Lessons.push(response)
                else
                    index = _.findIndex($scope.Lessons, {id: $scope.modal_lesson.id})
                    $scope.Lessons[index] = response
                $scope.$apply()
            , 'json'

        $scope.getCabinet = (id) ->
            _.findWhere($scope.all_cabinets, {id: parseInt(id)})

        $scope.getTeacher = (id) ->
            _.findWhere($scope.Teachers, {id: parseInt(id)})

        $scope.deleteLesson = ->
            ajaxStart()
            $('#schedule-modal').modal('hide')
            $.post "groups/ajax/DeleteLesson", {id: $scope.modal_lesson.id}, (response) ->
                index = _.findIndex($scope.Lessons, {id: $scope.modal_lesson.id})
                $scope.Lessons.splice(index, 1)
                $scope.$apply()
                ajaxEnd()

        $scope.monthName = (month) ->
            month_name = moment().month(month - 1).format "MMMM"

        angular.element(document).ready ->
            set_scope 'Schedule'
