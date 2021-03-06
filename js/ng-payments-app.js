var app,
  indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

app = angular.module("Payments", ["ui.bootstrap"]).filter('reverse', function() {
  return function(items) {
    if (items) {
      return items.slice().reverse();
    }
  };
}).controller("LkTeacherCtrl", function($scope, $http) {
  var _loadPayments;
  $scope.reverseObjKeys = function(obj) {
    return Object.keys(obj).reverse();
  };
  $scope.yearLabel = function(year) {
    return year + '-' + (parseInt(year) + 1) + ' уч. г.';
  };
  $scope.setYear = function(year) {
    return $scope.selected_year = year;
  };
  $scope.totalSum = function(date) {
    var total_sum;
    total_sum = 0;
    $.each($scope.Lessons[$scope.selected_year], function(d, items) {
      var day_sum;
      if (d > date) {
        return;
      }
      day_sum = 0;
      items.forEach(function(item) {
        return day_sum += item.sum;
      });
      day_sum;
      return total_sum += day_sum;
    });
    return total_sum;
  };
  _loadPayments = function() {
    $scope.password_correct = true;
    return $.post("payments/ajaxLkTeacher", {}, function(response) {
      $scope.Lessons = response.Lessons;
      $scope.selected_year = response.selected_year;
      $scope.years = response.years;
      $scope.loaded = true;
      return $scope.$apply();
    }, "json");
  };
  return angular.element(document).ready(function() {
    set_scope("Payments");
    if ($scope.view_mode) {
      return _loadPayments();
    } else {
      return bootbox.prompt({
        title: "Для доступа к странице введите ваш пароль",
        className: "modal-password-bigger",
        callback: function(result) {
          return $.ajax({
            url: "ajax/checkTeacherPass",
            data: {
              password: result
            },
            dataType: "json",
            method: "post",
            success: function(response) {
              if (response === true) {
                _loadPayments();
              } else {
                $scope.password_correct = false;
              }
              return $scope.$apply();
            },
            async: false
          });
        },
        buttons: {
          confirm: {
            label: "Подтвердить"
          },
          cancel: {
            className: "display-none"
          }
        }
      });
    }
  });
}).controller("ListCtrl", function($scope, $timeout) {
  var loadMutualAccounts;
  $scope.getForPagination = function() {
    var count;
    if ($scope.counts) {
      count = 0;
      Object.entries($scope.counts.mode).forEach(function(entry) {
        var ref;
        if (!$scope.search.mode.length || (ref = entry[0], indexOf.call($scope.search.mode, ref) >= 0)) {
          return count += entry[1];
        }
      });
      return count;
    } else {
      return 0;
    }
  };
  $scope.initSearch = function() {
    if (!$scope.search) {
      return $scope.search = {
        mode: 'STUDENT',
        payment_type: '',
        confirmed: '',
        type: ''
      };
    }
  };
  $scope.yearLabel = function(year) {
    return year + '-' + (parseInt(year) + 1) + ' уч. г.';
  };
  $scope.filter = function(current_page) {
    $scope.initSearch();
    $scope.search.current_page = current_page ? current_page : 1;
    window.history.pushState({}, '', 'payments' + ($scope.search.current_page > 1 ? '/?page=' + $scope.search.current_page : ''));
    $.cookie('payments', JSON.stringify($scope.search), {
      expires: 365,
      path: '/'
    });
    return $scope.getByPage();
  };
  $scope.pageChanged = function() {
    $scope.initSearch();
    window.history.pushState({}, '', 'payments' + ($scope.search.current_page > 1 ? '/?page=' + $scope.search.current_page : ''));
    return $scope.getByPage();
  };
  $scope.getByPage = function() {
    if (!$scope.loading) {
      frontendLoadingStart() && ($scope.loading = true);
    }
    return $.post("payments/AjaxGetPayments", {
      search: $scope.search
    }, function(response) {
      frontendLoadingEnd() && ($scope.loading = false);
      $scope.payments = response.payments;
      $scope.counts = response.counts;
      $scope.refreshCounts();
      return $scope.$apply();
    }, "json");
  };
  $scope.refreshCounts = function() {
    return $timeout(function() {
      $('.watch-select option').each(function(index, el) {
        $(el).data('subtext', $(el).attr('data-subtext'));
        return $(el).data('content', $(el).attr('data-content'));
      });
      return $('.watch-select').selectpicker('refresh', 100);
    });
  };
  angular.element(document).ready(function() {
    set_scope("Payments");
    if ($.cookie('payments')) {
      $scope.search = JSON.parse($.cookie('payments'));
    }
    $scope.filter($scope.current_page);
    return $(".single-select").selectpicker();
  });
  $scope.confirmPayment = function(payment) {
    if ($scope.user_rights.indexOf(11) === -1) {
      return;
    }
    payment.confirmed = (payment.confirmed + 1) % 2;
    return $.post("ajax/confirmPayment", {
      id: payment.id,
      confirmed: payment.confirmed
    });
  };
  $scope.editPayment = function(payment) {
    $scope.new_payment = angular.copy(payment);
    loadMutualAccounts($scope.new_payment.id_status);
    return lightBoxShow('addpayment');
  };
  $scope.addPaymentDialog = function() {
    $scope.new_payment = {
      id_status: 0,
      year: $scope.academic_year,
      extra: {}
    };
    return lightBoxShow('addpayment');
  };
  $scope.addPayment = function() {
    var payment_card, payment_card_first_number, payment_category, payment_date, payment_select, payment_sum, payment_type, payment_year;
    payment_date = $("#payment-date");
    payment_year = $("#payment-year");
    payment_category = $("#payment-category");
    payment_sum = $("#payment-sum");
    payment_select = $("#payment-select");
    payment_type = $("#paymenttypes-select");
    payment_card = $('#payment-card-number');
    payment_card_first_number = $("#payment-card-first-number");
    if (!parseInt($scope.new_payment.id_status)) {
      payment_select.focus().parent().addClass("has-error");
      return;
    } else {
      payment_select.parent().removeClass("has-error");
      if (parseInt($scope.new_payment.id_status) === 1) {
        if (!$scope.new_payment.card_number) {
          payment_card.focus().addClass('has-error');
          return;
        } else {
          payment_card.removeClass('has-error');
        }
      }
    }
    if ($scope.new_payment === 'teacher' && !$scope.new_payment.id_type) {
      payment_type.focus().parent().addClass("has-error");
      return;
    } else {
      payment_type.parent().removeClass("has-error");
    }
    if (!$scope.new_payment.sum) {
      payment_sum.focus().parent().addClass("has-error");
      return;
    } else {
      payment_sum.parent().removeClass("has-error");
    }
    if (!$scope.new_payment.date) {
      payment_date.focus().parent().addClass("has-error");
      return;
    } else {
      payment_date.parent().removeClass("has-error");
    }
    if (!$scope.new_payment.year) {
      payment_year.focus().parent().addClass("has-error");
      return;
    } else {
      payment_year.parent().removeClass("has-error");
    }
    if (!parseInt($scope.new_payment.category)) {
      payment_category.focus().parent().addClass("has-error");
      return;
    } else {
      payment_category.parent().removeClass("has-error");
    }
    if ($scope.new_payment.id) {
      ajaxStart();
      return $.post("ajax/paymentEdit", $scope.new_payment, function(response) {
        angular.forEach($scope.payments, function(payment, i) {
          if (payment.id === $scope.new_payment.id) {
            $scope.payments[i] = $scope.new_payment;
            return $scope.$apply();
          }
        });
        ajaxEnd();
        return lightBoxHide();
      });
    } else {
      ajaxStart();
      return $.post('ajax/paymentAdd', $scope.new_payment, function(response) {
        return location.reload();
      }, 'json');
    }
  };
  $scope.deletePayment = function() {
    if ($scope.new_payment.confirmed && $scope.user_rights.indexOf(11) === -1) {
      return;
    }
    return bootbox.confirm("Вы уверены, что хотите удалить платеж?", function(result) {
      if (result === true) {
        ajaxStart();
        return $.post("ajax/deletePayment", {
          id_payment: $scope.new_payment.id
        }, function() {
          var index;
          ajaxEnd();
          index = _.findIndex($scope.payments, {
            id: $scope.new_payment.id
          });
          $scope.payments.splice(index, 1);
          $timeout(function() {
            return $scope.$apply();
          });
          return lightBoxHide();
        });
      }
    });
  };
  $scope.$watch('new_payment.id_status', function(newVal, oldVal) {
    return loadMutualAccounts(newVal);
  });
  loadMutualAccounts = function(id_status) {
    if (parseInt(id_status) === 6) {
      $scope.mutual_accounts = void 0;
      return $.post("ajax/getLastAccounts", {
        id_teacher: $scope.new_payment.entity_id
      }, function(response) {
        $scope.mutual_accounts = response;
        return $scope.$apply();
      }, 'json');
    }
  };
  $scope.printPKO = function(payment) {
    $scope.print_mode = 'pko';
    $scope.PrintPayment = payment;
    $scope.$apply();
    return printDiv($scope.print_mode + "-print");
  };
  $scope.formatPkoDate = function(date) {
    if (date === null) {
      return;
    }
    return moment(convertDate(date)).format("D MMMM YYYY г.");
  };
  $scope.numToText = numToText;
  return $scope.formatDate = function(date) {
    var dateOut;
    dateOut = new Date(date);
    return dateOut;
  };
});
