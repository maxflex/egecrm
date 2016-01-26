// Generated by CoffeeScript 1.9.3
angular.module("Teacher", ["ngMap"]).config([
  '$compileProvider', function($compileProvider) {
    $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|chrome-extension|sip):/);
  }
]).filter('to_trusted', [
  '$sce', function($sce) {
    return function(text) {
      return $sce.trustAsHtml(text);
    };
  }
]).filter('reverse', function() {
  return function(items) {
    if (items) {
      return items.slice().reverse();
    }
  };
}).filter('range', function() {
  return function(input, total) {
    var i, j, ref;
    total = parseInt(total);
    for (i = j = 0, ref = total; j < ref; i = j += 1) {
      input.push(i);
    }
    return input;
  };
}).controller("SalaryCtrl", function($scope) {
  return angular.element(document).ready(function() {
    return set_scope("Teacher");
  });
}).controller("EditCtrl", function($scope) {
  var bindFileUpload;
  $scope.picture_version = 1;
  bindFileUpload = function() {
    return $('#fileupload').fileupload({
      formData: {
        id_teacher: $scope.Teacher.id
      },
      dataType: 'json',
      maxFileSize: 10000000,
      send: function() {
        return NProgress.configure({
          showSpinner: true
        });
      },
      progress: function(e, data) {
        return NProgress.set(data.loaded / data.total);
      },
      always: function() {
        NProgress.configure({
          showSpinner: false
        });
        return ajaxEnd();
      },
      done: function(i, response) {
        if (response.result.status !== "ERROR") {
          $scope.Teacher.has_photo = true;
          $scope.picture_version++;
          return $scope.$apply();
        } else {
          return notifyError(response.result.error);
        }
      },
      fail: function(e, data) {
        return $.each(data.messages, function(index, error) {
          return notifyError(error);
        });
      }
    });
  };
  $scope.toBePaid = function() {
    var lessons_sum, payments_sum;
    if (!$scope.Data.length) {
      return;
    }
    lessons_sum = 0;
    $.each($scope.Data, function(index, value) {
      return lessons_sum += parseInt(value.teacher_price);
    });
    payments_sum = 0;
    $.each($scope.payments, function(index, value) {
      return payments_sum += parseInt(value.sum);
    });
    return lessons_sum - payments_sum;
  };
  $scope.sipNumber = function(number) {
    number = number.toString();
    return "sip:" + number.replace(/[^0-9]/g, '');
  };
  $scope.callSip = function(element) {
    var number;
    number = $("#" + element).val();
    number = $scope.sipNumber(number);
    return location.href = number;
  };
  $scope.formatDate2 = function(date) {
    var dateOut;
    dateOut = new Date(date);
    return dateOut;
  };
  $scope.confirmPayment = function(payment) {
    bootbox.prompt({
      title: 'Введите пароль',
      className: 'modal-password',
      callback: function(result) {
        if (result === '363') {
          payment.confirmed = payment.confirmed ? 0 : 1;
          $.post('ajax/confirmTeacherPayment', {
            id: payment.id,
            confirmed: payment.confirmed
          });
          $scope.$apply();
        } else if (result !== null) {
          $('.bootbox-form').addClass('has-error').children().first().focus();
          $('.bootbox-input-text').on('keydown', function() {
            $(this).parent().removeClass('has-error');
          });
          return false;
        }
      },
      buttons: {
        confirm: {
          label: 'Подтвердить'
        },
        cancel: {
          className: 'display-none'
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
    bootbox.prompt({
      title: 'Введите пароль',
      className: 'modal-password',
      callback: function(result) {
        if (result === '363') {
          $scope.new_payment = angular.copy(payment);
          $scope.$apply();
          lightBoxShow('addpayment');
        } else if (result !== null) {
          $('.bootbox-form').addClass('has-error').children().first().focus();
          $('.bootbox-input-text').on('keydown', function() {
            $(this).parent().removeClass('has-error');
          });
          return false;
        }
      },
      buttons: {
        confirm: {
          label: 'Подтвердить'
        },
        cancel: {
          className: 'display-none'
        }
      }
    });
  };
  $scope.addPaymentDialog = function() {
    $scope.new_payment = {
      id_status: 0
    };
    lightBoxShow('addpayment');
  };
  $scope.addPayment = function() {
    var payment_date, payment_select, payment_sum, payment_type;
    payment_date = $('#payment-date');
    payment_sum = $('#payment-sum');
    payment_select = $('#payment-select');
    payment_type = $('#paymenttypes-select');
    if (!$scope.new_payment.id_status) {
      payment_select.focus().parent().addClass('has-error');
      return;
    } else {
      payment_select.parent().removeClass('has-error');
    }
    if (!$scope.new_payment.sum) {
      payment_sum.focus().parent().addClass('has-error');
      return;
    } else {
      payment_sum.parent().removeClass('has-error');
    }
    if (!$scope.new_payment.date) {
      payment_date.focus().parent().addClass('has-error');
      return;
    } else {
      payment_date.parent().removeClass('has-error');
    }
    if ($scope.new_payment.id) {
      ajaxStart();
      $.post('ajax/TeacherPaymentEdit', $scope.new_payment, function(response) {
        angular.forEach($scope.payments, function(payment, i) {
          if (payment.id === $scope.new_payment.id) {
            $scope.payments[i] = $scope.new_payment;
            $scope.$apply();
          }
        });
        ajaxEnd();
        lightBoxHide();
      });
    } else {
      $scope.new_payment.user_login = $scope.user.login;
      $scope.new_payment.first_save_date = moment().format('YYYY-MM-DD HH:mm:ss');
      $scope.new_payment.id_teacher = $scope.Teacher.id;
      $scope.new_payment.id_user = $scope.user.id;
      ajaxStart();
      $.post('ajax/TeacherPaymentAdd', $scope.new_payment, function(response) {
        $scope.new_payment.id = response;
        $scope.payments = initIfNotSet($scope.payments);
        $scope.payments.push($scope.new_payment);
        $scope.new_payment = {
          id_status: 0
        };
        $scope.$apply();
        ajaxEnd();
        lightBoxHide();
      });
    }
  };
  $scope.deletePayment = function(index, payment) {
    if (!payment.confirmed) {
      bootbox.confirm('Вы уверены, что хотите удалить платеж?', function(result) {
        if (result === true) {
          console.log(index);
          $.post('ajax/deleteTeacherPayment', {
            'id_payment': payment.id
          });
          $scope.payments = _.without($scope.payments, _.findWhere($scope.payments, {
            id: payment.id
          }));
          $scope.$apply();
        }
      });
    } else {
      bootbox.prompt({
        title: 'Введите пароль',
        className: 'modal-password',
        callback: function(result) {
          if (result === '363') {
            bootbox.confirm('Вы уверены, что хотите удалить платеж?', function(result) {
              if (result === true) {
                $.post('ajax/deletePayment', {
                  'id_payment': payment.id
                });
                $scope.payments = _.without($scope.payments, _.findWhere($scope.payments, {
                  id: payment.id
                }));
                $scope.$apply();
              }
            });
          } else if (result !== null) {
            $('.bootbox-form').addClass('has-error').children().first().focus();
            $('.bootbox-input-text').on('keydown', function() {
              $(this).parent().removeClass('has-error');
            });
            return false;
          }
        },
        buttons: {
          confirm: {
            label: 'Подтвердить'
          },
          cancel: {
            className: 'display-none'
          }
        }
      });
    }
  };
  $scope.formatDate = function(date) {
    return moment(date).format("D MMMM YY");
  };
  $scope.formatTime = function(time) {
    return time.substr(0, 5);
  };
  $scope.coordinate_time = function(date) {
    return moment(date).format("YYYY.MM.DD в HH:mm");
  };
  $scope.dateToStart = function(date) {
    var D;
    date = date.split(".");
    date = date.reverse();
    date = date.join("-");
    D = new Date(date);
    return moment().to(D);
  };
  $scope.phoneCorrect = phoneCorrect;
  $scope.isMobilePhone = isMobilePhone;
  angular.element(document).ready(function() {
    set_scope("Teacher");
    $scope.weekdays = [
      {
        "short": "ПН",
        "full": "Понедельник",
        "schedule": ["", "", $scope.time[1], $scope.time[2]]
      }, {
        "short": "ВТ",
        "full": "Вторник",
        "schedule": ["", "", $scope.time[1], $scope.time[2]]
      }, {
        "short": "СР",
        "full": "Среда",
        "schedule": ["", "", $scope.time[1], $scope.time[2]]
      }, {
        "short": "ЧТ",
        "full": "Четверг",
        "schedule": ["", "", $scope.time[1], $scope.time[2]]
      }, {
        "short": "ПТ",
        "full": "Пятница",
        "schedule": ["", "", $scope.time[1], $scope.time[2]]
      }, {
        "short": "СБ",
        "full": "Суббота",
        "schedule": [$scope.time[3], $scope.time[4], $scope.time[5], $scope.time[6]]
      }, {
        "short": "ВС",
        "full": "Воскресенье",
        "schedule": [$scope.time[3], $scope.time[4], $scope.time[5], $scope.time[6]]
      }
    ];
    $.each($scope.Teacher.branches, function(index, branch) {
      return $scope.Teacher.branches[index] = branch.toString();
    });
    return setTimeout(function() {
      return $scope.$apply();
    }, 100);
  });
  $scope.toggleBanned = function() {
    $scope.Teacher.banned = !$scope.Teacher.banned;
    return $scope.form_changed = true;
  };
  $scope.goToTutor = function() {
    return window.open("https://crm.a-perspektiva.ru/repetitors/edit/?id=" + $scope.Teacher.id_a_pers, "_blank");
  };
  $(document).ready(function() {
    bindFileUpload();
    $("#subjects-select").selectpicker({
      noneSelectedText: "предметы",
      multipleSeparator: ", "
    });
    $("#teacher-branches").selectpicker({
      noneSelectedText: "удобные филиалы для преподавателя"
    });
    return $("#teacher-edit").on('keyup change', 'input, select, textarea', function() {
      $scope.form_changed = true;
      return $scope.$apply();
    });
  });
  return $(".save-button").on("click", function() {
    var has_errors;
    has_errors = false;
    $(".phone-masked").filter(function() {
      var not_filled;
      not_filled = $(this).val().match(/_/);
      if (not_filled !== null) {
        $(this).addClass("has-error").focus();
        notifyError("Номер телефона указан неполностью");
        has_errors = true;
        return false;
      } else {
        return $(this).removeClass("has-error");
      }
    });
    if (has_errors) {
      return false;
    }
    $scope.Teacher.subjects = [];
    $("#subjects-select option:selected").each(function() {
      if ($(this).val()) {
        return $scope.Teacher.subjects.push($(this).val());
      }
    });
    $scope.Teacher.branches = [];
    $("#teacher-branches option:selected").each(function() {
      if ($(this).val()) {
        return $scope.Teacher.branches.push($(this).val());
      }
    });
    ajaxStart();
    $scope.saving = true;
    $scope.$apply();
    $scope.Teacher.freetime = $scope.freetime;
    return $.post("teachers/ajax/save", $scope.Teacher, function(response) {
      console.log(response);
      if ($scope.Teacher.id) {
        ajaxEnd();
        $scope.saving = false;
        $scope.form_changed = false;
        return $scope.$apply();
      } else {
        return redirect("teachers/edit/" + response);
      }
    });
  });
}).controller("ListCtrl", function($scope) {
  $scope.othersCount = function() {
    return _.where($scope.Teachers, {
      had_lesson: 0
    }).length;
  };
  $scope.smsDialog = smsDialogTeachers;
  $scope.showHidden = function() {
    $scope.show_others = !$scope.show_others;
    if ($scope.show_others) {
      return $('html, body').animate({
        scrollTop: $("#hidden-teachers-button").offset().top
      }, 400);
    } else {
      return $('html, body').animate({
        scrollTop: $("#teachers-list").prop("scrollHeight") - 420
      }, 400);
    }
  };
  $scope.deleteTeacher = function(id_teacher, index) {
    return bootbox.confirm("Вы уверены, что хотите удалить преподавателя №" + id_teacher + "?", function(result) {
      if (result === true) {
        $scope.Teachers.splice(index, 1);
        $scope.$apply();
        $.post("teachers/ajax/delete", {
          id_teacher: id_teacher
        });
        return console.log("here", index, id_teacher);
      }
    });
  };
  return angular.element(document).ready(function() {
    return smsMode(4);
  });
});
