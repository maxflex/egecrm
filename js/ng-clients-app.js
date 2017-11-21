var app;

app = angular.module("Clients", ["ui.bootstrap"]).filter('to_trusted', [
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
}).filter('toArray', function() {
  return function(obj) {
    var arr;
    arr = [];
    $.each(obj, function(index, value) {
      return arr.push(value);
    });
    return arr;
  };
}).controller("ListCtrl", function($scope, $timeout, PhoneService) {
  bindArguments($scope, arguments);
  $scope.yearLabel = function(year) {
    return 'договоры на ' + year + '-' + (parseInt(year) + 1) + ' год';
  };
  $scope.getNumber = function(index) {
    return (($scope.current_page - 1) * 30) + (index + 1);
  };
  $scope.refreshCounts = function() {
    return $timeout(function() {
      $('.watch-select option').each(function(index, el) {
        $(el).data('subtext', $(el).attr('data-subtext'));
        return $(el).data('content', $(el).attr('data-content'));
      });
      return $('.watch-select').selectpicker('refresh');
    }, 100);
  };
  $scope.filter = function() {
    $.cookie("clients", JSON.stringify($scope.search), {
      expires: 365,
      path: '/'
    });
    $scope.current_page = 1;
    return $scope.getByPage($scope.current_page);
  };
  $scope.sort = function() {
    if ($scope.search.order === void 0) {
      $scope.search.order = 'asc';
    } else if ($scope.search.order === 'asc') {
      $scope.search.order = 'desc';
    } else if ($scope.search.order === 'desc') {
      delete $scope.search.order;
    }
    return $scope.filter();
  };
  $scope.pageChanged = function() {
    if ($scope.current_page > 1) {
      window.history.pushState({}, '', 'clients/?page=' + $scope.current_page);
    }
    return $scope.getByPage($scope.current_page);
  };
  $scope.getByPage = function(page) {
    frontendLoadingStart();
    return $.post("clients/ajax/GetStudents", {
      page: page
    }, function(response) {
      frontendLoadingEnd();
      $scope.Students = response.data;
      $scope.counts = response.counts;
      $scope.totals = response.totals;
      $scope.$apply();
      return $scope.refreshCounts();
    }, "json");
  };
  angular.element(document).ready(function() {
    set_scope("Clients");
    $scope.search = $.cookie("clients") ? JSON.parse($.cookie("clients")) : {};
    $scope.current_page = $scope.currentPage;
    $scope.pageChanged();
    return $(".single-select").selectpicker();
  });
  $scope.to_students = true;
  return $scope.to_representatives = false;
});
