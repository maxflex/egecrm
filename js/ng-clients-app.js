// Generated by CoffeeScript 1.9.3
angular.module("Clients", []).filter('to_trusted', [
  '$sce', function($sce) {
    return function(text) {
      return $sce.trustAsHtml(text);
    };
  }
]).controller("ListCtrl", function($scope) {
  $scope.filter_cancelled = 0;
  $scope.clientsFilter = function(Student) {
    if ($scope.filter_cancelled === 2) {
      return Student.Contract.pre_cancelled === 1;
    } else {
      return Student.Contract.cancelled === $scope.filter_cancelled;
    }
  };
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
  $scope.getSubjectsCount = function(Contract) {
    if (Contract.subjects) {
      return Object.keys(Contract.subjects).length;
    } else {
      return 0;
    }
  };
  return angular.element(document).ready(function() {
    return set_scope("Clients");
  });
});
