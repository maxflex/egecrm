// Generated by CoffeeScript 1.9.3
angular.module("Clients", []).filter('to_trusted', [
  '$sce', function($sce) {
    return function(text) {
      return $sce.trustAsHtml(text);
    };
  }
]).controller("ListCtrl", function($scope) {
  $scope.filter_cancelled = 0;
  $scope.remainderSum = function() {
    var sum;
    sum = 0;
    $.each($scope.Students, function(index, Student) {
      if (Student.Remainder.id) {
        return sum += Student.Remainder.remainder;
      }
    });
    return sum;
  };
  $scope.clientsFilter = function(Student) {
    var count;
    switch ($scope.filter_cancelled) {
      case 0:
        return _.findWhere(Student.Contract.subjects, {
          status: 3
        }) !== void 0;
      case 1:
        count = 0;
        $.each(Student.Contract.subjects, function(index, subject) {
          if (subject.status === 1) {
            return count++;
          }
        });
        return count !== Object.keys(Student.Contract.subjects).length && count > 0;
      case 2:
        count = 0;
        $.each(Student.Contract.subjects, function(index, subject) {
          if (subject.status === 1) {
            return count++;
          }
        });
        return count === Object.keys(Student.Contract.subjects).length;
      case 3:
        return _.findWhere(Student.Contract.subjects, {
          status: 2
        }) !== void 0;
      case 4:
        return true;
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
