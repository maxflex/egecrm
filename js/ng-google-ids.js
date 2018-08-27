var app;

app = angular.module("GoogleIds", ["ui.bootstrap"]).controller("IndexCtrl", function($scope, $timeout, $http) {
  bindArguments($scope, arguments);
  $timeout(function() {
    return set_scope("GoogleIds");
  });
  $scope.disabled_payments = {};
  $scope.google_ids = '';
  $scope.loading = false;
  $scope.show = function() {
    $scope.loading = true;
    return $.post('google-ids/show', {
      google_ids: $scope.google_ids
    }, function(response) {
      $scope.data = response;
      $scope.loading = false;
      return $scope.$apply();
    }, 'json');
  };
  $scope.getTotalGoogleIds = function() {
    return Object.keys($scope.data).length;
  };
  $scope.getTotal = function(field) {
    var total;
    total = 0;
    Object.keys($scope.data).forEach(function(id_google) {
      if ($scope.data[id_google]) {
        return total += $scope.data[id_google][field].length;
      }
    });
    return total;
  };
  return $scope.getTotalPayments = function(field) {
    var total;
    total = 0;
    Object.keys($scope.data).forEach(function(id_google) {
      if ($scope.data[id_google]) {
        return $scope.data[id_google].payments.forEach(function(payment) {
          if ($scope.disabled_payments.hasOwnProperty(payment.id) && $scope.disabled_payments[payment.id]) {
            return;
          }
          if (payment.id_type === 1) {
            return total += payment.sum;
          } else {
            return total -= payment.sum;
          }
        });
      }
    });
    return total;
  };
});
