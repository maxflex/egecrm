// Generated by CoffeeScript 1.9.3
angular.module("Settings", []).controller("VocationsCtrl", function($scope) {
  $scope.schedulde_loaded = false;
  $scope.menu = 1;
  $scope.getLine1 = function(Schedule) {
    return moment(Schedule.date).format("D MMMM YYYY г.");
  };
  $scope.getLine2 = function(Schedule) {
    return moment(Schedule.date).format("dddd");
  };
  $scope.setTime = function(Schedule, event) {
    $(event.target).hide();
    $(event.target).parent().children("input").show().timepicker({
      timeFormat: 'H:i',
      scrollDefault: '09:30',
      selectOnBlur: true
    }).on("changeTime, blur", function(e) {
      var time;
      time = $(this).val();
      if (time) {
        Schedule.time = time;
        $.post("groups/ajax/AddScheduleTime", {
          time: time,
          date: Schedule.date,
          id_group: $scope.Group.id
        });
        $scope.$apply();
      }
      return $(this).hide().parent().children("span").html(time ? time : "не установлено").show();
    }).focus();
    return false;
  };
  $scope.getInitParams = function(el) {
    var current_date, month, year;
    month = parseInt($(el).attr("month"));
    year = month >= 8 ? parseInt(moment().format("YYYY")) : moment().add(1, "years").format("YYYY");
    current_date = new Date(year + "-" + month + "-01");
    return {
      language: 'ru',
      startDate: current_date,
      endDate: moment(current_date).endOf("month").toDate(),
      multidate: true
    };
  };
  $scope.monthName = function(month) {
    return moment().month(month - 1).format("MMMM");
  };
  $scope.dateChange = function(e) {
    var d, t;
    if (!$scope.schedule_loaded) {
      return;
    }
    d = moment(clicked_date).format("YYYY-MM-DD");
    $scope.Group.Schedule = initIfNotSet($scope.Group.Schedule);
    t = $scope.Group.Schedule.filter(function(schedule) {
      return schedule.date === d;
    });
    if (t.length === 0) {
      $scope.Group.Schedule.push({
        date: d
      });
      $.post("groups/ajax/AddScheduleDate", {
        date: d,
        id_group: $scope.Group.id
      });
    } else {
      $.each($scope.Group.Schedule, function(i, v) {
        if (v !== void 0) {
          if (v.date === d) {
            return $scope.Group.Schedule.splice(i, 1);
          }
        }
      });
      $.post("groups/ajax/DeleteScheduleDate", {
        date: d,
        id_group: $scope.Group.id
      });
    }
    return $scope.$apply();
  };
  return angular.element(document).ready(function() {
    var init_dates, j, len, ref, schedule_date;
    set_scope('Group');
    init_dates = [];
    ref = $scope.Group.Schedule;
    for (j = 0, len = ref.length; j < len; j++) {
      schedule_date = ref[j];
      init_dates.push(new Date(schedule_date.date));
    }
    console.log(init_dates);
    return $(".calendar-month").each(function() {
      var d, k, len1, m, month_number;
      $(this).datepicker($scope.getInitParams(this)).on("changeDate", $scope.dateChange);
      m = $(this).attr("month");
      for (k = 0, len1 = init_dates.length; k < len1; k++) {
        d = init_dates[k];
        month_number = moment(d).format("M");
        if (month_number === m) {
          $(this).datepicker("_setDate", d);
        }
      }
      $(".table-condensed").first().children("thead").css("display", "table-caption");
      return setTimeout(function() {
        $scope.schedule_loaded = true;
        return $scope.$apply();
      }, 500);
    });
  });
});
