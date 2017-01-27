var app;

app = angular.module("Payments", ["ui.bootstrap"]).filter('reverse', function() {
  return function(items) {
    if (items) {
      return items.slice().reverse();
    }
  };
}).controller("LkTeacherCtrl", function($scope, $http) {
  $scope.lessonsTotalSum = function() {
    var lessons_sum;
    lessons_sum = 0;
    if ($scope.Lessons) {
      $.each($scope.Lessons, function(index, value) {
        return lessons_sum += parseInt(value.teacher_price);
      });
    }
    return lessons_sum;
  };
  $scope.totalNdfl = function() {
    var sum;
    sum = 0;
    if ($scope.Lessons) {
      $.each($scope.Lessons, function(index, value) {
        return sum += parseInt(value.ndfl);
      });
    }
    return sum;
  };
  $scope.lessonsTotalPaid = function(from_lessons) {
    var payments_sum;
    payments_sum = 0;
    if (from_lessons && $scope.Lessons) {
      $.each($scope.Lessons, function(index, lesson) {
        var j, len, payment, ref, results;
        ref = lesson.payments;
        results = [];
        for (j = 0, len = ref.length; j < len; j++) {
          payment = ref[j];
          results.push(payments_sum += parseInt(payment.sum));
        }
        return results;
      });
    } else {
      if ($scope.payments) {
        $.each($scope.payments, function(index, value) {
          return payments_sum += parseInt(value.sum);
        });
      }
    }
    return payments_sum;
  };
  $scope.toBePaid = function(from_lessons) {
    var lessons_sum, ndfl_sum, payments_sum;
    if (!($scope.Lessons && $scope.Lessons.length)) {
      return;
    }
    lessons_sum = $scope.lessonsTotalSum();
    payments_sum = $scope.lessonsTotalPaid(from_lessons);
    ndfl_sum = $scope.totalNdfl(from_lessons);
    return lessons_sum - payments_sum - ndfl_sum;
  };
  $scope.dateFromCustomFormat = function(date) {
    var D;
    date = date.split(".");
    date = date.reverse();
    date = date.join("-");
    D = new Date(date);
    return moment(D).format("D MMMM YYYY");
  };
  $scope.formatDate = function(date) {
    return moment(date).format("D MMMM YYYY");
  };
  $scope.formatTime = function(time) {
    return time.substr(0, 5);
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
                $scope.Lessons = response.Lessons;
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
    if (payment.confirmed && $scope.user_rights.indexOf(11) === -1) {
      return;
    }
    $scope.new_payment = angular.copy(payment);
    return lightBoxShow('addpayment');
  };
  $scope.addPaymentDialog = function() {
    $scope.new_payment = {
      id_status: 0,
      year: $scope.academic_year
    };
    return lightBoxShow('addpayment');
  };
  $scope.addPayment = function() {
    var payment_card, payment_card_first_number, payment_date, payment_select, payment_sum, payment_type;
    payment_date = $("#payment-date");
    payment_sum = $("#payment-sum");
    payment_select = $("#payment-select");
    payment_type = $("#paymenttypes-select");
    payment_card = $('#payment-card-number');
    payment_card_first_number = $("#payment-card-first-number");
    if (!$scope.new_payment.id_status) {
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
        $scope.new_payment.id = response.id;
        $scope.new_payment.document_number = response.document_number;
        $scope.payments = initIfNotSet($scope.payments);
        $scope.payments.push($scope.new_payment);
        $scope.new_payment = {
          id_status: 0
        };
        $scope.$apply();
        ajaxEnd();
        return lightBoxHide();
      }, 'json');
    }
  };
  $scope.deletePayment = function(index, payment) {
    if (payment.confirmed && $scope.user_rights.indexOf(11) === -1) {
      return;
    }
    return bootbox.confirm("Вы уверены, что хотите удалить платеж?", function(result) {
      if (result === true) {
        ajaxStart();
        return $.post("ajax/deletePayment", {
          id_payment: payment.id
        }, function() {
          ajaxEnd();
          $scope.payments.splice(index, 1);
          return $timeout(function() {
            return $scope.$apply();
          });
        });
      }
    });
  };
  $scope.printPKO = function(payment) {
    $scope.print_mode = 'pko';
    $scope.PrintPayment = payment;
    $scope.Representative = $scope.representative;
    $scope.$apply();
    return printDiv($scope.print_mode + "-print");
  };
  return $scope.formatDate = function(date) {
    var dateOut;
    dateOut = new Date(date);
    return dateOut;
  };
});
