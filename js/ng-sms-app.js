var app;

app = angular.module("Sms", ["ui.bootstrap"]).controller("Main", function($scope) {
  $scope.pageChanged = function() {
    var redirect_string;
    ajaxStart();
    redirect_string = "sms/" + $scope.currentPage;
    if ($scope.search) {
      redirect_string += "?search=" + $scope.search;
    }
    redirect_string += $scope.search && $scope.phone ? '&' : $scope.phone ? '?' : '';
    if ($scope.phone) {
      redirect_string += "&phone=" + $scope.phone;
    }
    return redirect(redirect_string);
  };
  return angular.element(document).ready(function() {
    return set_scope("Sms");
  });
});
