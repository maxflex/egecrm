var app;

app = angular.module("Calls", []).config([
  '$compileProvider', function($compileProvider) {
    $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|chrome-extension|sip):/);
  }
]).controller("MissedCtrl", function($scope, $timeout, $http, PhoneService) {
  bindArguments($scope, arguments);
  $timeout(function() {
    return set_scope('Calls');
  });
  $scope.formatTime = function(time) {
    return moment(time * 1000).format("DD.MM.YY в HH:mm");
  };
  $scope.callDuration = function(seconds) {
    var format;
    if (!seconds) {
      return '';
    }
    format = 's сек';
    if (seconds > 60) {
      format = 'm мин ' + format;
    }
    if (seconds > 3600) {
      format = 'H час ' + format;
    }
    return moment.duration(seconds, 'seconds').format(format);
  };
  return $scope.deleteCall = function(call) {
    return $.post('calls/ajax/delete', {
      entry_id: call.entry_id
    }, function() {
      $scope.missed = _.without($scope.missed, call);
      return $scope.$apply();
    });
  };
});
