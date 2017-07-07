var app;

app = angular.module("Contracts", ["ui.bootstrap"]).filter('to_trusted', [
  '$sce', function($sce) {
    return function(text) {
      return $sce.trustAsHtml(text);
    };
  }
]).filter('hideZero', function() {
  return function(item) {
    if (item > 0) {
      return item;
    } else {
      return null;
    }
  };
}).controller("ListCtrl", function($scope, $timeout) {
  $scope.getNumber = function(index) {
    return (($scope.current_page - 1) * 30) + (index + 1);
  };
  $timeout(function() {
    $('.watch-select option').each(function(index, el) {
      $(el).data('subtext', $(el).attr('data-subtext'));
      return $(el).data('content', $(el).attr('data-content'));
    });
    return $('.watch-select').selectpicker('refresh');
  }, 500);
  $scope.yearLabel = function(year) {
    return year + '-' + (parseInt(year) + 1) + ' уч. г.';
  };
  $scope.filter = function() {
    $.cookie("contracts", JSON.stringify($scope.search), {
      expires: 365,
      path: '/'
    });
    $scope.current_page = 1;
    return $scope.getByPage($scope.current_page);
  };
  $scope.pageChanged = function() {
    if ($scope.current_page > 1) {
      window.history.pushState({}, '', 'contracts/?page=' + $scope.current_page);
    }
    return $scope.getByPage($scope.current_page);
  };
  $scope.getByPage = function(page) {
    frontendLoadingStart();
    return $.post("contracts/ajax/GetContracts", {
      page: page
    }, function(response) {
      frontendLoadingEnd();
      $scope.Contracts = response.data;
      $scope.counts = response.counts;
      return $scope.$apply();
    }, "json");
  };
  $scope.keyFilter = function(event) {
    if (event.keyCode === 13) {
      return $scope.filter();
    }
  };
  return angular.element(document).ready(function() {
    set_scope("Contracts");
    $scope.search = $.cookie("contracts") ? JSON.parse($.cookie("contracts")) : {};
    $scope.current_page = $scope.currentPage;
    return $scope.pageChanged();
  });
});
