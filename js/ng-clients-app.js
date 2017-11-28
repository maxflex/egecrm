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
}).controller("SubjectsCtrl", function($scope, $timeout, PhoneService) {
  bindArguments($scope, arguments);
  angular.element(document).ready(function() {
    set_scope("Clients");
    $scope.search = $.cookie("clients_subjects") ? JSON.parse($.cookie("clients_subjects")) : {};
    $scope.current_page = $scope.currentPage;
    $scope.pageChanged();
    return $timeout(function() {
      return $(".single-select").selectpicker();
    }, 300);
  });
  $scope.yearLabel = function(year) {
    return 'договоры на ' + year + '-' + (parseInt(year) + 1) + ' год';
  };
  $scope.filter = function() {
    $.cookie("clients_subjects", JSON.stringify($scope.search), {
      expires: 365,
      path: '/'
    });
    $scope.current_page = 1;
    return $scope.getByPage($scope.current_page);
  };
  $scope.pageChanged = function() {
    if ($scope.current_page > 1) {
      window.history.pushState({}, '', 'clients/subjects?page=' + $scope.current_page);
    }
    return $scope.getByPage($scope.current_page);
  };
  $scope.getByPage = function(page) {
    frontendLoadingStart();
    return $.post("clients/ajax/GetSubjects", {
      page: page
    }, function(response) {
      frontendLoadingEnd();
      $scope.contract_subjects = response.data;
      $scope.count = response.count;
      return $scope.$apply();
    }, "json");
  };
  return $scope.getNumber = function(index) {
    return (($scope.current_page - 1) * 100) + (index + 1);
  };
}).controller("ListCtrl", function($scope, $timeout, PhoneService) {
  var _paymentDate;
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
  $scope.payment_options = {};
  $scope.splitPaymentsOptions = function(year) {
    var options;
    if (!year) {
      return;
    }
    if ($scope.payment_options[year] !== void 0) {
      return $scope.payment_options[year];
    }
    year = parseInt(year);
    options = {
      '1-0': [],
      '2-0': [_paymentDate(year + 1, '01-27')],
      '3-0': [_paymentDate(year, '11-20'), _paymentDate(year + 1, '02-20')],
      '3-1': [_paymentDate(year, '11-27'), _paymentDate(year + 1, '02-27')],
      '8-0': [_paymentDate(year, '10-15'), _paymentDate(year, '11-15'), _paymentDate(year, '12-15'), _paymentDate(year + 1, '01-15'), _paymentDate(year + 1, '02-15'), _paymentDate(year + 1, '03-15'), _paymentDate(year + 1, '04-15')]
    };
    $scope.payment_options[year] = options;
    return options;
  };
  _paymentDate = function(year, date) {
    return moment(parseInt(year) + '-' + date).format('DD.MM.YY');
  };
  $scope.getPaymentLabel = function(dates) {
    var len, payment, str;
    len = dates.length + 1;
    payment = 'платеж';
    if (len > 1 && len <= 4) {
      payment += 'а';
    }
    if (len > 4) {
      payment += 'ей';
    }
    str = len + ' ' + payment;
    if (dates.length > 0) {
      str += ': ';
      if (len === 8) {
        str += 'ежемесячно 15 числа';
      } else {
        dates.forEach(function(date, index) {
          str += date;
          if ((index + 1) !== dates.length) {
            return str += ', ';
          }
        });
      }
    }
    return str;
  };
  return angular.element(document).ready(function() {
    set_scope("Clients");
    $scope.search = $.cookie("clients") ? JSON.parse($.cookie("clients")) : {};
    $scope.current_page = $scope.currentPage;
    $scope.pageChanged();
    return $(".single-select").selectpicker();
  });
});
