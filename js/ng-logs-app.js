var app;

app = angular.module("Logs", ["ui.bootstrap"]).controller("ListCtrl", function($scope, $timeout, UserService) {
  var load;
  $scope.UserService = UserService;
  $scope.LogTypes = {
    create: 'создание',
    update: 'обновление',
    "delete": 'удаление',
    wrong_login: 'неверный логин',
    wrong_password: 'неверный пароль',
    wrong_captcha: 'неверная капча',
    wrong_sms_code: 'неверный код смс',
    sms_code_sent: 'код смс отправлен',
    outside_office: 'вне офиса',
    banned: 'заблокирован',
    login: 'вход',
    url: 'просмотр URL'
  };
  frontendLoadingStart();
  $scope.toJson = function(data) {
    return JSON.parse(data);
  };
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
