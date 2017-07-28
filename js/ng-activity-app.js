var app;

app = angular.module("Activity", []).controller("IndexCtrl", function($scope, $http, $timeout, UserService) {
  $scope.UserService = UserService;
  $timeout(function() {
    set_scope('Activity');
    $scope.search = {};
    return $scope.refreshCounts();
  });
  $scope.formatMinutes = function(minutes) {
    var format;
    format = minutes >= 60 ? 'H час m мин' : 'm мин';
    return moment.duration(minutes, 'minutes').format(format);
  };
  $scope.refreshCounts = function() {
    return $timeout(function() {
      $('.selectpicker option').each(function(index, el) {
        $(el).data('subtext', $(el).attr('data-subtext'));
        return $(el).data('content', $(el).attr('data-content'));
      });
      return $('.selectpicker').selectpicker('refresh');
    }, 600);
  };
  return $scope.show = function() {
    return $http.get("activity/get/" + $scope.search.user_id + "/" + $scope.search.date).then(function(response) {
      return $scope.data = response.data;
    });
  };
});
