// Generated by CoffeeScript 1.9.3
angular.module("Stats", []).controller("ListCtrl", function($scope) {
  $scope.payment_status = $.cookie("stats_payment_status");
  return $scope.setPayment = function(payment_status) {
    $.cookie("stats_payment_status", payment_status, {
      expires: 365,
      path: '/'
    });
    return location.reload();
  };
});
