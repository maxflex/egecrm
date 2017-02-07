var app;

app = angular.module("Tendency", []).filter('toArray', function() {
  return function(obj) {
    var arr;
    arr = [];
    $.each(obj, function(index, value) {
      return arr.push(value);
    });
    return arr;
  };
}).controller("IndexCtrl", function($scope, $timeout) {
  $scope.go = function() {
    console.log('go', $scope.search);
    $scope.count = void 0;
    $scope.loading = true;
    return $.post("tendency/AjaxSearch", {
      search: $scope.search
    }).then(function(response) {
      $scope.loading = false;
      $scope.count = response.count;
      $scope.contracts = response.contracts;
      return $scope.$apply();
    });
  };
  return angular.element(document).ready(function() {
    console.log('test');
    return $timeout(function() {
      $("#subjects").selectpicker({
        noneSelectedText: "предметы",
        multipleSeparator: '+'
      });
      return $("#grades").selectpicker({
        noneSelectedText: "классы",
        multipleSeparator: ', '
      });
    });
  });
});

set_scope("Tendency");
