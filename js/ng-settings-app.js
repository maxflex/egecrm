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
}).controller("RecommendedCtrl", function($scope) {
  $scope.yearLabel = function(year) {
    return year + '-' + (parseInt(year) + 1) + ' уч. г.';
  };
  return angular.element(document).ready(function() {
    return set_scope('Settings');
  });
}).controller("VacationsCtrl", function($scope, $timeout) {
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
    return redirect("settings/vacations?year=" + year);
  };
  $scope.months = [9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
  $timeout(function() {
    return $scope.calendarLoaded = true;
  });
  $scope.formatDate = function(date) {
    return moment(date).format("D MMMM YYYY г.");
  };
  $timeout(function() {
    $scope.viewDate = {};
    $scope.displayMonth = {};
    $scope.months.forEach(function(month) {
      var year;
      year = $scope.current_year;
      if (month <= 8) {
        year++;
      }
      $scope.viewDate[month] = new Date(year + "-" + month + "-01");
      return $scope.displayMonth[month] = true;
    });
    return $timeout(function() {
      return $scope.calendarLoaded = true;
    });
  });
  $scope.editVacation = function(vacation) {
    if (vacation == null) {
      vacation = null;
    }
    $('#schedule-modal').modal('show');
    if (vacation === null) {
      return $scope.modal_vacation = {
        year: $scope.current_year
      };
    } else {
      $scope.modal_vacation = _.clone(vacation);
      return $scope.modal_vacation.date = moment($scope.modal_vacation.date).format('DD.MM.YY');
    }
  };
  $scope.saveVacation = function() {
    ajaxStart();
    $('#schedule-modal').modal('hide');
    $scope.modal_vacation.date = convertDate($scope.modal_vacation.date);
    return $.post("ajax/SaveVacation", $scope.modal_vacation, function(response) {
      var index;
      console.log('save complete', response);
      ajaxEnd();
      if (!$scope.modal_vacation.id) {
        $scope.modal_vacation.id = response.id;
        $scope.Vacations.push(response);
      } else {
        index = _.findIndex($scope.Vacations, {
          id: $scope.modal_vacation.id
        });
        $scope.Vacations[index] = _.clone($scope.modal_vacation);
      }
      return $scope.$apply();
    }, "json");
  };
  $scope.deleteVacation = function(Vacation) {
    ajaxStart();
    return $.post("ajax/DeleteVacation", {
      id: Vacation.id
    }, function(response) {
      var index;
      index = _.findIndex($scope.Vacations, {
        id: Vacation.id
      });
      $scope.Vacations.splice(index, 1);
      $scope.$apply();
      return ajaxEnd();
    });
  };
  $scope.monthName = function(month) {
    var month_name;
    return month_name = moment().month(month - 1).format("MMMM");
  };
  return angular.element(document).ready(function() {
    return set_scope('Settings');
  });
}).controller("CabinetsCtrl", function($scope) {
  return angular.element(document).ready(function() {
    return set_scope('Settings');
  });
});
