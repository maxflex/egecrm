var app;

app = angular.module("Schedule", ['mwl.calendar']).controller("MainCtrl", function($scope, $timeout) {
  var getColor;
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
  $scope.$watchCollection('Group.Schedule', function(newVal, oldVal) {
    $scope.events = {};
    return newVal.forEach(function(Schedule) {
      var month;
      month = moment(Schedule.date).format('M');
      if ($scope.events[month] === void 0) {
        $scope.events[month] = [];
      }
      return $scope.events[month].push({
        startsAt: new Date(Schedule.date),
        color: {
          primary: getColor(Schedule)
        }
      });
    });
  });
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
  $scope.setParamsFromGroup = function(Group) {
    return $.each($scope.Group.Schedule, function(i, schedule) {
      var d, v;
      v = angular.copy(schedule);
      d = moment(v.date).format("d");
      d = parseInt(d);
      if (d === 0) {
        d = 7;
      }
      if (Group.day_and_time[d] !== void 0 && Group.day_and_time[d].length === 1) {
        v.time = $scope.Time[Group.day_and_time[d][0].id_time];
        v.cabinet = Group.day_and_time[d][0].id_cabinet;
      } else {
        v.time = null;
        v.cabinet = '';
      }
      ajaxStart();
      return $.post("groups/ajax/TimeFromGroup", {
        id: v.id,
        time: v.time,
        cabinet: v.cabinet
      }, function() {
        ajaxEnd();
        schedule.time = v.time;
        schedule.cabinet = v.cabinet;
        return $scope.$apply();
      });
    });
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
    return month_name = moment().month(month - 1).format("MMMM");
  };
  return angular.element(document).ready(function() {
    return set_scope('Schedule');
  });
});
