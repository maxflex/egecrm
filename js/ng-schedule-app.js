var app;

app = angular.module("Schedule", ['mwl.calendar']).controller("MainCtrl", function($scope, $timeout) {
  var getColor;
  $scope.months = [9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
  $timeout(function() {
    var first_lesson_date, first_lesson_month, year;
    $scope.viewDate = {};
    $scope.displayMonth = {};
    first_lesson_month = moment($scope.Group.first_lesson_date).format("M");
    year = $scope.Group.year;
    if (first_lesson_month <= 8) {
      year++;
    }
    first_lesson_date = new Date(year + "-" + first_lesson_month + "-01");
    $scope.months.forEach(function(month) {
      year = $scope.Group.year;
      if (month <= 8) {
        year++;
      }
      $scope.viewDate[month] = new Date(year + "-" + month + "-01");
      return $scope.displayMonth[month] = $scope.viewDate[month] >= first_lesson_date;
    });
    return $timeout(function() {
      return $scope.calendarLoaded = true;
    });
  });
  $scope.calendarTitle = 'test';
  $scope.events = {};
  $scope.$watchCollection('Lessons', function(newVal, oldVal) {
    $scope.events = {};
    return newVal.forEach(function(Lesson) {
      var month;
      month = moment(Lesson.lesson_date).format('M');
      if ($scope.events[month] === void 0) {
        $scope.events[month] = [];
      }
      return $scope.events[month].push({
        startsAt: new Date(Lesson.lesson_date),
        color: {
          primary: getColor(Lesson)
        }
      });
    });
  });
  getColor = function(Lesson) {
    if (Lesson.is_conducted) {
      return '#337ab7';
    }
    if (Lesson.cancelled) {
      return '#c0c0c0';
    }
    return '#5cb85c';
  };
  $scope.formatDate = function(date) {
    return moment(date).format("DD.MM.YY");
  };
  $scope.countNotCancelled = function() {
    return _.where($scope.Lessons, {
      cancelled: 0
    }).length;
  };
  $scope.lessonCount = function() {
    return Object.keys($scope.Group.day_and_time).length;
  };
  $scope.duplicateLessons = function() {
    var bug_index, current_date, date, index, results, to_be_duplicated;
    date = (parseInt($scope.Group.year) + 1) + '-06-01';
    current_date = moment($scope.Lessons[$scope.Lessons.length - 1].lesson_date).add(7, 'days').format("YYYY-MM-DD");
    index = 0;
    bug_index = 0;
    to_be_duplicated = {};
    results = [];
    while (current_date < date) {
      if ($scope.special_dates.vacations.indexOf(current_date) === -1 && _.find($scope.Lessons, {
        lesson_date: current_date
      }) === void 0) {
        index++;
        to_be_duplicated[index] = _.clone($scope.Lessons[$scope.Lessons.length - 1]);
        delete to_be_duplicated[index].id;
        to_be_duplicated[index].lesson_date = current_date;
        $.post("groups/ajax/SaveLesson", to_be_duplicated[index], function(response) {
          bug_index++;
          to_be_duplicated[bug_index].id = response.id;
          console.log(response.id, to_be_duplicated[bug_index]);
          $scope.Lessons.push(to_be_duplicated[bug_index]);
          return $scope.$apply();
        }, 'json');
      }
      results.push(current_date = moment(current_date).add(7, 'days').format("YYYY-MM-DD"));
    }
    return results;
  };
  $scope.lessonModal = function(lesson) {
    if (lesson == null) {
      lesson = null;
    }
    $('#schedule-modal').modal('show');
    if (lesson === null) {
      return $scope.modal_lesson = {
        id_group: $scope.Group.id
      };
    } else {
      $scope.modal_lesson = _.clone(lesson);
      return $scope.modal_lesson.lesson_date = moment($scope.modal_lesson.lesson_date).format('DD.MM.YY');
    }
  };
  $scope.saveLesson = function() {
    ajaxStart();
    $('#schedule-modal').modal('hide');
    $scope.modal_lesson.lesson_date = convertDate($scope.modal_lesson.lesson_date);
    return $.post("groups/ajax/SaveLesson", $scope.modal_lesson, function(response) {
      var index;
      ajaxEnd();
      if (!$scope.modal_lesson.id) {
        $scope.modal_lesson.id = response.id;
        $scope.Lessons.push(response);
      } else {
        index = _.findIndex($scope.Lessons, {
          id: $scope.modal_lesson.id
        });
        $scope.Lessons[index] = _.clone($scope.modal_lesson);
      }
      return $scope.$apply();
    }, 'json');
  };
  $scope.getCabinet = function(id) {
    return _.findWhere($scope.all_cabinets, {
      id: parseInt(id)
    });
  };
  $scope.deleteLesson = function() {
    ajaxStart();
    $('#schedule-modal').modal('hide');
    return $.post("groups/ajax/DeleteLesson", {
      id: $scope.modal_lesson.id
    }, function(response) {
      var index;
      index = _.findIndex($scope.Lessons, {
        id: $scope.modal_lesson.id
      });
      $scope.Lessons.splice(index, 1);
      $scope.$apply();
      return ajaxEnd();
    });
  };
  $scope.monthName = function(month) {
    var month_name;
    return month_name = moment().month(month - 1).format("MMMM");
  };
  return angular.element(document).ready(function() {
    return set_scope('Schedule');
  });
});
