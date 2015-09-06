// Generated by CoffeeScript 1.9.3
angular.module("Clients", []).controller("ListCtrl", function($scope) {
  $scope.getScore = function(subjects) {
    var ar;
    ar = [];
    $.each(subjects, function(i, v) {
      if (v.score !== null && v.score !== "") {
        return ar.push(v.score);
      }
    });
    return ar.join(" + ");
  };
  $scope.filter_cancelled = 0;
  $scope.order = 2;
  $scope.setOrder = function(order) {
    console.log(order, $scope.asc);
    if ($scope.order !== order) {
      $scope.order = order;
      return $scope.asc = true;
    } else {
      return $scope.asc = !$scope.asc;
    }
  };
  $scope.asc = true;
  $scope.orderStudents = function() {
    switch ($scope.order) {
      case 1:
        return 'last_name';
      case 2:
        return 'Contract.id';
      default:
        return 'date_formatted';
    }
  };
  return $scope.getSubjectsCount = function(Contract) {
    if (Contract.subjects) {
      return Object.keys(Contract.subjects).length;
    } else {
      return 0;
    }
  };
});
