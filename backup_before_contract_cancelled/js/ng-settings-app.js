// Generated by CoffeeScript 1.9.3
var testy;

testy = 1;

angular.module("Settings", ["ui.bootstrap"]).filter('to_trusted', [
  '$sce', function($sce) {
    return function(text) {
      return $sce.trustAsHtml(text);
    };
  }
]).controller("CabinetsCtrl", function($scope) {
  $scope.weekdays = [
    {
      "short": "ПН",
      "full": "Понедельник",
      "schedule": ["", "", "16:15", "18:40"]
    }, {
      "short": "ВТ",
      "full": "Вторник",
      "schedule": ["", "", "16:15", "18:40"]
    }, {
      "short": "СР",
      "full": "Среда",
      "schedule": ["", "", "16:15", "18:40"]
    }, {
      "short": "ЧТ",
      "full": "Четверг",
      "schedule": ["", "", "16:15", "18:40"]
    }, {
      "short": "ПТ",
      "full": "Пятница",
      "schedule": ["", "", "16:15", "18:40"]
    }, {
      "short": "СБ",
      "full": "Суббота",
      "schedule": ["11:00", "13:30", "16:00", "18:30"]
    }, {
      "short": "ВС",
      "full": "Воскресенье",
      "schedule": ["11:00", "13:30", "16:00", "18:30"]
    }
  ];
  $scope.inCabinetFreetime = function(time, freetime) {
    if (freetime === void 0) {
      return false;
    }
    freetime = objectToArray(freetime);
    return $.inArray(time, freetime) >= 0;
  };
  $scope.getBranchCabinets = function(id_branch) {
    return _.where($scope.Cabinets, {
      id_branch: id_branch
    });
  };
  return angular.element(document).ready(function() {
    return set_scope("Settings");
  });
}).controller("VocationsCtrl", function($scope) {
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
      exam_days: $scope.exam_days
    }, function(response) {
      $scope.adding = false;
      $scope.$apply();
      return ajaxEnd();
    });
  };
  $scope.getLine1 = function(Schedule) {
    return moment(Schedule.date).format("D MMMM YYYY г.");
  };
  $scope.getLine2 = function(Schedule) {
    return moment(Schedule.date).format("dddd");
  };
  $scope.setTime = function(Schedule, event) {
    $(event.target).hide();
    $(event.target).parent().children("input").show().on("changeTime, blur", function(e) {
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
    set_scope('Settings');
    init_dates = [];
    ref = $scope.Group.Schedule;
    for (j = 0, len = ref.length; j < len; j++) {
      schedule_date = ref[j];
      init_dates.push(new Date(schedule_date.date));
    }
    console.log(init_dates);
    $(".calendar-month").each(function() {
      var d, day, k, len1, m, month, month_number, year;
      $(this).datepicker($scope.getInitParams(this)).on("changeDate", $scope.dateChange);
      m = $(this).attr("month");
      for (k = 0, len1 = init_dates.length; k < len1; k++) {
        d = init_dates[k];
        month_number = moment(d).format("M");
        if (month_number === m) {
          year = parseInt(moment(d).format("YYYY"));
          month = parseInt(moment(d).format("M") - 1);
          day = parseInt(moment(d).format("D"));
          $(this).datepicker("_setDate", new Date(Date.UTC.apply(Date, [year, month, day])));
        }
      }
      return setTimeout(function() {
        $scope.schedule_loaded = true;
        return $scope.$apply();
      }, 500);
    });
    $(".table-condensed").first().children("thead").css("display", "table-caption");
    return $(".table-condensed").eq(15).children("tbody").children("tr").first().remove();
  });
});