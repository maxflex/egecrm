var app;

app = angular.module("Stats", ["ui.bootstrap"]).config([
  '$compileProvider', function($compileProvider) {
    $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|chrome-extension|sip):/);
  }
]).filter('to_trusted', [
  '$sce', function($sce) {
    return function(text) {
      return $sce.trustAsHtml(text);
    };
  }
]).filter('hideZero', function() {
  return function(item) {
    if (item > 0) {
      return item;
    } else {
      return null;
    }
  };
}).controller("ListCtrl", function($scope, PhoneService) {
  bindArguments($scope, arguments);
  $scope.round1 = function(n) {
    return Math.round(n);
  };
  $scope.round2 = function(n) {
    return Math.round(n / 1000) * 1000;
  };
  $scope.goDates = function() {
    var date_end, date_start;
    ajaxStart();
    date_start = $("#date-start").val();
    date_end = $("#date-end").val();
    return redirect("stats/users?date_start=" + date_start + "&date_end=" + date_end);
  };
  $scope.pageChanged = function(group) {
    ajaxStart();
    return redirect("stats/?group=" + group + "&page=" + $scope.currentPage);
  };
  $scope.pageStudentChanged = function() {
    ajaxStart();
    return redirect("stats/visits/total?page=" + $scope.currentPage);
  };
  $scope.pagePaymentChanged = function(group) {
    ajaxStart();
    return redirect("stats/payments" + ($scope.mode === 'teachers' ? '/teachers' : '') + ("?group=" + group + "&page=" + $scope.currentPage));
  };
  $scope.Lessons = {};
  $scope.dateLoad = function(date) {
    if (!$scope.days_mode) {
      return false;
    }
    $("#" + date).toggle();
    if ($scope.Lessons[date] === void 0) {
      return $.post("ajax/loadStatsSchedule", {
        date: date
      }, function(response) {
        $scope.Lessons[date] = response;
        return $scope.$apply();
      }, "json");
    }
  };
  $scope.clickControl = function(Teacher, event) {
    if (event.shiftKey) {
      return PhoneService.call(Teacher.phone);
    } else {
      return redirect("teachers/edit/" + Teacher.id);
    }
  };
  $scope.day = 2;
  $scope.plusDays = function() {
    return $.post("ajax/plusDays", {
      day: $scope.day++
    }, function(response) {
      console.log(response);
      $.each(response, function(date, stat) {
        return $scope.stats[date] = stat;
      });
      return $scope.$apply();
    }, "json");
  };
  $scope.formatDate = function(date) {
    return moment(date).format("D MMM. YYYY");
  };
  $scope.isToday = function(date) {
    return date === moment().format("YYYY-MM-DD");
  };
  $scope.isFuture = function(date) {
    return date >= moment().format("YYYY-MM-DD");
  };
  $scope.isWeekend = function(date) {
    var ref;
    return (ref = moment(date).isoWeekday()) === 6 || ref === 7;
  };
  $scope.sortByDate = function(stats) {
    var tmp;
    tmp = [];
    $.each(stats, function(date, obj) {
      obj.date = date;
      return tmp.push(obj);
    });
    return _.sortBy(tmp, 'date').reverse();
  };
  $scope.formatDay = function(day) {
    return $scope.weekdays[day].short;
  };
  $scope.toggleDiv = function(id) {
    return $(".user-" + id).slideToggle();
  };
  return angular.element(document).ready(function() {
    return set_scope("Stats");
  });
});
