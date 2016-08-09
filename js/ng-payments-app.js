// Generated by CoffeeScript 1.9.3
angular.module("Payments", ["ui.bootstrap"]).filter('reverse', function() {
  return function(items) {
    if (items) {
      return items.slice().reverse();
    }
  };
}).controller("LkTeacherCtrl", function($scope, $http) {
  $scope.formatDate = function(date) {
    return moment(date).format("D MMMM YYYY");
  };
  $scope.formatTime = function(time) {
    return time.substr(0, 5);
  };
  $scope.totalPaid = function() {
    var sum;
    sum = 0;
    $.each($scope.payments, function(i, payment) {
      return sum += payment.sum;
    });
    return sum;
  };
  $scope.totalEarned = function() {
    var sum;
    sum = 0;
    $.each($scope.Data, function(i, data) {
      return sum += data.teacher_price;
    });
    return sum;
  };
  $scope.toBePaid = function() {
    return $scope.totalEarned() - $scope.totalPaid();
  };
  return angular.element(document).ready(function() {
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
              $scope.password_correct = true;
              $.post("payments/ajaxLkTeacher", {}, function(response) {
                console.log(response);
                $scope.payments = response.payments;
                $scope.Data = response.Data;
                $scope.loaded = true;
                return $scope.$apply();
              }, "json");
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
  });
}).controller("ListCtrl", function($scope, $timeout) {
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
  $scope.filter = function(current_page) {
    console.log('filter');
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
    console.log('page changed ' + $scope.search.current_page);
    $scope.initSearch();
    window.history.pushState({}, '', 'payments' + ($scope.search.current_page > 1 ? '/?page=' + $scope.search.current_page : ''));
    return $scope.getByPage();
  };
  $scope.getByPage = function() {
    console.log('get by page');
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
    console.log('refresh counts');
    return $timeout(function() {
      $('.watch-select option').each(function(index, el) {
        $(el).data('subtext', $(el).attr('data-subtext'));
        return $(el).data('content', $(el).attr('data-content'));
      });
      return $('.watch-select').selectpicker('refresh', 100);
    });
  };
  angular.element(document).ready(function() {
    console.log('ready');
    set_scope("Payments");
    if ($.cookie('payments')) {
      $scope.search = JSON.parse($.cookie('payments'));
    }
    $scope.filter($scope.current_page);
    return $(".single-select").selectpicker();
  });
  $scope.confirmPayment = function(payment) {
    return bootbox.prompt({
      title: "Введите пароль",
      className: "modal-password",
      callback: function(result) {
        if (hex_md5(result) === payments_hash) {
          payment.confirmed = (payment.confirmed + 1) % 2;
          $.post("ajax/confirmPayment", {
            id: payment.id,
            confirmed: payment.confirmed
          });
          return $scope.$apply();
        } else if (result !== null) {
          $('.bootbox-form').addClass('has-error').children().first().focus();
          $('.bootbox-input-text').on('keydown', function() {
            return $(this).parent().removeClass('has-error');
          });
          return false;
        }
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
  };
  $scope.editPayment = function(payment) {
    if (!payment.confirmed) {
      $scope.new_payment = angular.copy(payment);
      $scope.$apply();
      lightBoxShow('addpayment');
      return;
    }
    return bootbox.prompt({
      title: "Введите пароль",
      className: "modal-password",
      callback: function(result) {
        if (hex_md5(result) === payments_hash) {
          $scope.new_payment = angular.copy(payment);
          $scope.$apply();
          return lightBoxShow('addpayment');
        } else if (result !== null) {
          $('.bootbox-form').addClass('has-error').children().first().focus();
          $('.bootbox-input-text').on('keydown', function() {
            return $(this).parent().removeClass('has-error');
          });
          return false;
        }
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
  };
  $scope.addPaymentDialog = function() {
    $scope.new_payment = {
      id_status: 0
    };
    return lightBoxShow('addpayment');
  };
  $scope.addPayment = function() {
    var payment_date, payment_select, payment_sum, payment_type;
    payment_date = $("#payment-date");
    payment_sum = $("#payment-sum");
    payment_select = $("#payment-select");
    payment_type = $("#paymenttypes-select");
    if (!$scope.new_payment.id_status) {
      payment_select.focus().parent().addClass("has-error");
    } else {
      payment_select.parent().removeClass("has-error");
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
      $scope.new_payment.user_login = $scope.user.login;
      $scope.new_payment.first_save_date = moment().format('YYYY-MM-DD HH:mm:ss');
      $scope.new_payment.entity_id = $scope.student.id;
      $scope.new_payment.entity_type = $scope.new_payment.Entity.type;
      $scope.new_payment.id_user = $scope.user.id;
      ajaxStart();
      return $.post('ajax/paymentAdd', $scope.new_payment, function(response) {
        $scope.new_payment.id = response;
        $scope.payments = initIfNotSet($scope.payments);
        $scope.payments.push($scope.new_payment);
        $scope.new_payment = {
          id_status: 0
        };
        $scope.$apply();
        ajaxEnd();
        return lightBoxHide();
      });
    }
  };
  $scope.deletePayment = function(index, payment) {
    if (!payment.confirmed) {
      return bootbox.confirm("Вы уверены, что хотите удалить платеж?", function(result) {
        if (result === true) {
          $.post("ajax/deletePayment", {
            id_payment: payment.id
          });
          $scope.payments.splice(index, 1);
          return $scope.$apply();
        }
      });
    } else {
      return bootbox.prompt({
        title: "Введите пароль",
        className: "modal-password",
        callback: function(result) {
          if (hex_md5(result) === payments_hash) {
            return bootbox.confirm("Вы уверены, что хотите удалить платеж?", function(result) {
              if (result === true) {
                $.post("ajax/deletePayment", {
                  id_payment: payment.id
                });
                $scope.payments.splice(index, 1);
                return $scope.$apply();
              }
            });
          } else if (result !== null) {
            $('.bootbox-form').addClass('has-error').children().first().focus();
            $('.bootbox-input-text').on('keydown', function() {
              return $(this).parent().removeClass('has-error');
            });
            return false;
          }
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
  };
  return $scope.formatDate = function(date) {
    var dateOut;
    dateOut = new Date(date);
    return dateOut;
  };
});
