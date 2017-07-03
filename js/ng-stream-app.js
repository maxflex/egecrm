var app;

app = angular.module("Stream", ["ui.bootstrap"]).controller('Main', function($scope, $timeout, $http) {
  var load, refreshCounts;
  bindArguments($scope, arguments);
  $scope.frontend_loading = true;
  refreshCounts = function() {
    return $timeout(function() {
      $('.selectpicker option').each(function(index, el) {
        $(el).data('subtext', $(el).attr('data-subtext'));
        return $(el).data('content', $(el).attr('data-content'));
      });
      return $('.selectpicker').selectpicker('refresh');
    }, 100);
  };
  $scope.keyFilter = function(event) {
    if (event.keyCode === 13) {
      return $scope.filter();
    }
  };
  $scope.filter = function() {
    $.cookie("stream", JSON.stringify($scope.search), {
      expires: 365,
      path: '/'
    });
    $scope.current_page = 1;
    return $scope.pageChanged();
  };
  $timeout(function() {
    $scope.search = $.cookie("stream") ? JSON.parse($.cookie("stream")) : {};
    load($scope.page);
    return $scope.current_page = $scope.page;
  });
  $scope.pageChanged = function() {
    $scope.frontend_loading = true;
    load($scope.current_page);
    return paginate('stream', $scope.current_page);
  };
  $scope.formatDate = function(date) {
    return moment(date).format("DD.MM.YY в HH:mm");
  };
  load = function(page) {
    var params;
    params = '?page=' + page;
    return $http.get("stream/get" + params).then(function(response) {
      console.log(response);
      $scope.data = response.data;
      $scope.frontend_loading = false;
      return refreshCounts();
    });
  };
  return angular.element(document).ready(function() {
    return set_scope("Stream");
  });
});
