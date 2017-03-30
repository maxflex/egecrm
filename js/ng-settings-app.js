var app, testy;

testy = 1;

app = angular.module("Settings", ["ui.bootstrap", 'ngSanitize', 'mwl.calendar']).filter('to_trusted', [
  '$sce', function($sce) {
    return function(text) {
      return $sce.trustAsHtml(text);
    };
  }
]).filter('toArray', function() {
  return function(obj) {
    var arr;
    arr = [];
    $.each(obj, function(index, value) {
      return arr.push(value);
    });
    return arr;
  };
}).controller("VocationsCtrl", function($scope, $timeout) {
  var getColor;
  $scope.schedulde_loaded = false;
  $scope.menu = 1;
  $scope.exam_days = {
    9: [],
    11: []
  };
  $scope.saveExamDays = function() {
    ajaxStart();
    $scope.adding = true;
    return $.post("ajax/saveExamDays", {
      exam_days: $scope.exam_days,
      year: $scope.current_year
    }, function(response) {
      $scope.adding = false;
      $scope.$apply();
      return ajaxEnd();
    });
  };
  $scope.yearLabel = function(year) {
    return year + '-' + (parseInt(year) + 1) + ' уч. г.';
  };
  $scope.setYear = function(year) {
    $.cookie("current_year", year, {
      expires: 365,
      path: '/'
    });
    return redirect("settings/vocations?year=" + year);
  };
  $scope.months = [9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
  $timeout(function() {
    $scope.viewDate = {};
    $scope.months.forEach(function(month) {
      var year;
      year = $scope.Group.year;
      if (month <= 8) {
        year++;
      }
      return $scope.viewDate[month] = new Date(year + "-" + month + "-01");
    });
    return $timeout(function() {
      return $scope.calendarLoaded = true;
    });
  });
  $scope.calendarTitle = 'test';
  $scope.events = {};
  getColor = function(Schedule) {
    if (Schedule.was_lesson) {
      return '#337ab7';
    }
    if (Schedule.cancelled) {
      return '#c0c0c0';
    }
    return '#5cb85c';
  };
  $scope.formatDate = function(date) {
    return moment(date).format("D MMMM YYYY г.");
  };
  $scope.countNotCancelled = function(Schedule) {
    return _.where(Schedule, {
      cancelled: 0
    }).length;
  };
  $scope.lessonCount = function() {
    return Object.keys($scope.Group.day_and_time).length;
  };
  $scope.scheduleModal = function(schedule) {
    if (schedule == null) {
      schedule = null;
    }
    $('#schedule-modal').modal('show');
    if (schedule === null) {
      return $scope.modal_schedule = {
        id_group: $scope.Group.id
      };
    } else {
      $scope.modal_schedule = _.clone(schedule);
      return $scope.modal_schedule.date = moment($scope.modal_schedule.date).format('DD.MM.YYYY');
    }
  };
  $scope.saveSchedule = function() {
    ajaxStart();
    $('#schedule-modal').modal('hide');
    $scope.modal_schedule.date = convertDate($scope.modal_schedule.date);
    return $.post("groups/ajax/SaveSchedule", $scope.modal_schedule, function(response) {
      var index;
      ajaxEnd();
      if (!$scope.modal_schedule.id) {
        $scope.modal_schedule.id = response.id;
        $scope.Group.Schedule.push($scope.modal_schedule);
      } else {
        index = _.findIndex($scope.Group.Schedule, {
          id: $scope.modal_schedule.id
        });
        $scope.Group.Schedule[index] = _.clone($scope.modal_schedule);
      }
      return $scope.$apply();
    });
  };
  $scope.getCabinet = function(id) {
    return _.findWhere($scope.all_cabinets, {
      id: parseInt(id)
    });
  };
  $scope.deleteSchedule = function(Schedule) {
    ajaxStart();
    return $.post("groups/ajax/DeleteSchedule", {
      id: Schedule.id
    }, function(response) {
      var index;
      index = _.findIndex($scope.Group.Schedule, {
        id: Schedule.id
      });
      $scope.Group.Schedule.splice(index, 1);
      $scope.$apply();
      return ajaxEnd();
    });
  };
  $scope.getPastLesson = function(Schedule) {
    return _.findWhere($scope.past_lessons, {
      lesson_date: Schedule.date,
      lesson_time: Schedule.time
    });
  };
  $scope.lessonStarted = function(Schedule) {
    var lesson_time;
    lesson_time = new Date(Schedule.date + " " + Schedule.time).getTime();
    return lesson_time < new Date().getTime();
  };
  $scope.monthName = function(month) {
    var month_name;
    month_name = moment().month(month - 1).format("MMMM");
    if (month === 1) {
      month_name += ' ' + (parseInt($scope.Group.year) + 1);
    }
    return month_name;
  };
  return angular.element(document).ready(function() {
    return set_scope('Settings');
  });
}).controller("CabinetsCtrl", function($scope) {
  return angular.element(document).ready(function() {
    return set_scope('Settings');
  });
});
