var app;

app = angular.module("Logs", ["ui.bootstrap"]).filter('cut', function() {
  return function(value, wordwise, max, nothing, tail) {
    var lastspace;
    if (nothing == null) {
      nothing = '';
    }
    if (tail == null) {
      tail = '…';
    }
    if (!value || value === '') {
      return nothing;
    }
    max = parseInt(max, 10);
    if (!max) {
      return value;
    }
    if (value.length <= max) {
      return value;
    }
    value = value.substr(0, max);
    if (wordwise) {
      lastspace = value.lastIndexOf(' ');
      if (lastspace !== -1) {
        if (value.charAt(lastspace - 1) === '.' || value.charAt(lastspace - 1) === ',') {
          lastspace = lastspace - 1;
        }
        value = value.substr(0, lastspace);
      }
    }
    return value + tail;
  };
}).controller("ListCtrl", function($scope, $timeout, UserService) {
  var load;
  $scope.UserService = UserService;
  $scope.LogTypes = {
    create: 'создание',
    update: 'обновление',
    "delete": 'удаление',
    authorization: 'авторизация',
    url: 'просмотр URL'
  };
  frontendLoadingStart();
  $scope.toJson = function(data) {
    return JSON.parse(data);
  };
  $scope.$watch('search.table', function(newVal, oldVal) {
    if ((newVal && oldVal) || (oldVal && !newVal)) {
      return $scope.search.column = null;
    }
  });
  $scope.refreshCounts = function() {
    return $timeout(function() {
      $('.selectpicker option').each(function(index, el) {
        $(el).data('subtext', $(el).attr('data-subtext'));
        return $(el).data('content', $(el).attr('data-content'));
      });
      return $('.selectpicker').selectpicker('refresh');
    }, 100);
  };
  $scope.filter = function() {
    $.cookie("logs", JSON.stringify($scope.search), {
      expires: 365,
      path: '/'
    });
    $scope.current_page = 1;
    return $scope.pageChanged();
  };
  $scope.keyFilter = function(event) {
    if (event.keyCode === 13) {
      return $scope.filter();
    }
  };
  $timeout(function() {
    $scope.search = $.cookie("logs") ? JSON.parse($.cookie("logs")) : {};
    load($scope.page);
    return $scope.current_page = $scope.page;
  });
  $scope.pageChanged = function() {
    frontendLoadingEnd();
    load($scope.current_page);
    if ($scope.current_page > 1) {
      return window.history.pushState({}, '', 'logs/?page=' + $scope.current_page);
    }
  };
  load = function(page) {
    return $.post("logs/ajaxGetLogs", {
      page: page
    }, function(response) {
      frontendLoadingEnd();
      $scope.logs = response.data;
      $scope.counts = response.counts;
      $scope.$apply();
      return $scope.refreshCounts();
    }, 'json');
  };
  angular.element(document).ready(function() {
    return set_scope("Logs");
  });
  $scope.formatDateTime = function(date) {
    return moment(date).format('DD.MM.YY в HH:mm');
  };
  return $scope.getUser = function(user_id) {
    return _.find($scope.users, {
      id: user_id
    });
  };
});
