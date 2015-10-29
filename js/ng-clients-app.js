// Generated by CoffeeScript 1.9.3
angular.module("Clients", []).filter('to_trusted', [
  '$sce', function($sce) {
    return function(text) {
      return $sce.trustAsHtml(text);
    };
  }
]).controller("ErrorsCtrl", function($scope) {
  return angular.element(document).ready(function() {
    set_scope("Clients");
    return $.post("clients/ajax/getErrorStudents", {
      mode: window.location.search
    }, function(response) {
      $scope.Response = response;
      return $scope.$apply();
    }, "json");
  });
}).controller("ListCtrl", function($scope) {
  $scope.filter_cancelled = 0;
  $scope.clientsFilter = function(Student) {
    if ($scope.filter_cancelled === 2) {
      return _.findWhere(Student.Contract.subjects, {
        status: 1
      }) !== void 0 && Student.Contract.cancelled === 0;
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
  $scope.to_students = true;
  $scope.to_representatives = false;
  $scope.smsDialog3 = function() {
    $scope.sms_students = $scope.$eval("Students | filter:clientsFilter");
    $scope.sms_students_ids = _.pluck($scope.sms_students, 'id');
    return smsDialog3();
  };
  return angular.element(document).ready(function() {
    $.post("clients/ajax/GetStudents", {}, function(response) {
      $scope.Students = response;
      return $scope.$apply();
    }, "json");
    set_scope("Clients");
    return smsMode(3);
  });
});
