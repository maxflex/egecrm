// Generated by CoffeeScript 1.9.3
angular.module("Sms", ["ui.bootstrap"]).controller("Main", function($scope) {
  $scope.pageChanged = function() {
    ajaxStart();
    return redirect("sms/" + $scope.currentPage);
  };
  return angular.element(document).ready(function() {
    return set_scope("Sms");
  });
});