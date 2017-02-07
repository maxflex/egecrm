var ang_scope, app;

ang_scope = false;

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
    return $.post('tendency/AjaxSearch', {
      search: $scope.search
    }, function(response) {
      $scope.loading = false;
      $scope.count = response.count;
      $scope.contracts_count = response.contracts_count;
      $scope.payments_sum = response.payments_sum;
      return $scope.$apply();
    }, 'json');
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

ang_scope = angular.element('[ng-app=Tendency]').scope();
