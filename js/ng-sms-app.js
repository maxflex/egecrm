var app;

app = angular.module("Sms", ["ui.bootstrap"]).controller("Main", function($scope, $element, $http, PhoneService) {
  bindArguments($scope, arguments);
  $scope.getByPage = function(page) {
    frontendLoadingStart();
    return $.post('sms/ajax/history', {
      page: page,
      search: $scope.search
    }, function(response) {
      frontendLoadingEnd();
      $scope.data = response.data;
      $scope.total = response.total;
      return $scope.$apply();
    }, 'json');
  };
  $scope.filter = _.debounce(function() {
    console.log('debounced');
    $scope.current_page = 1;
    return $scope.getByPage($scope.current_page);
  }, 150);
  $scope.pageChanged = function() {
    if ($scope.current_page > 1) {
      window.history.pushState({}, '', 'clients/?page=' + $scope.current_page);
    }
    return $scope.getByPage($scope.current_page);
  };
  return angular.element(document).ready(function() {
    setTimeout(function() {
      return $('#entity-phone-phone').attr('placeholder', 'отправить СМС');
    }, 300);
    $scope.current_page = $scope.currentPage;
    $scope.pageChanged();
    return set_scope("Sms");
  });
});
