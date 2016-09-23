// Generated by CoffeeScript 1.10.0
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
    var i, k, ref;
    total = parseInt(total);
    for (i = k = 0, ref = total; k < ref; i = k += 1) {
      input.push(i);
    }
    return input;
  };
}).filter('hideZero', function() {
  return function(item) {
    if (item > 0) {
      return item;
    } else {
      return null;
    }
  };
}).filter('yearFilter', function() {
  return function(items, year) {
    return _.where(items, {
      'year': year
    });
  };
}).controller("FaqCtrl", function($scope) {
  $scope.save = function() {
    ajaxStart();
    return $.post('ajax/saveTeacherFaq', {
      html: $scope.editor.getValue()
    }, function() {
      return ajaxEnd();
    });
  };
  return angular.element(document).ready(function() {
    $scope.editor = ace.edit("editor");
    $scope.editor.setOptions({
      minLines: 43,
      maxLines: 43
    });
    return $scope.editor.getSession().setMode("ace/mode/html");
  });
}).controller("SalaryCtrl", function($scope) {
  return angular.element(document).ready(function() {
    return set_scope("Teacher");
  });
}).controller("EditCtrl", function($scope, $timeout, $http) {
  var _loadData, _postData, bindFileUpload, menus;
  $scope["enum"] = review_statuses;
  menus = ['Groups', 'Reviews', 'Lessons', 'payments', 'Reports', 'Stats', 'Bars'];
  $scope.setMenu = function(menu) {
    $.each(menus, function(index, value) {
      return _loadData(index, menu, value);
    });
    return $scope.current_menu = menu;
  };
  _postData = function(menu) {
    return {
      id_teacher: $scope.Teacher.id,
      menu: menu
    };
  };
  _loadData = function(menu, selected_menu, ngModel) {
    if ($scope[ngModel] === void 0 && menu === selected_menu) {
      return $.post("teachers/ajax/menu", _postData(menu), function(response) {
        $scope[ngModel] = response;
        return $scope.$apply();
      }, "json");
    }
  };
  $scope.yearDifference = function(year) {
    return moment().format("YYYY") - year;
  };
  $scope.toggleFreetime = function(day, id_time) {
    var mode;
    mode = $scope.Bars.Freetime[day][id_time] === 'green' ? 'Delete' : 'Add';
    $.post('ajax/' + mode + 'Freetime', {
      'id_entity': $scope.Teacher.id,
      'type_entity': 'teacher',
      'id_time': id_time
    }, function() {
      $scope.Bars.Freetime[day][id_time] = mode === 'Add' ? 'green' : 'empty';
      $scope.$apply();
    });
  };
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
    if (!$scope.Lessons.length) {
      return;
    }
    lessons_sum = 0;
    $.each($scope.Lessons, function(index, value) {
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
        if (result === null) {

        } else if (hex_md5(result === payments_hash)) {
          payment.confirmed = payment.confirmed ? 0 : 1;
          $.post('ajax/confirmPayment', {
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
        if (result === null) {

        } else if (hex_md5(result === payments_hash)) {
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
    $scope.handleKeyPress();
    setTimeout(function() {
      return $($("#addpayment select")[0]).focus();
    }, 200);
  };
  $scope.handleKeyPress = function() {
    return $('#addpayment').on('keydown', function(e) {
      var select_val;
      if (e.keyCode === 13) {
        if ($('#payment-select').is(':focus')) {
          select_val = $('#payment-select').val();
          if (select_val !== '0') {
            if (select_val === '1') {
              $('#payment-card').focus();
            } else {
              $('#payment-sum').focus();
            }
          }
        } else {
          if ($('#payment-card').is(':focus')) {
            $('#payment-sum').focus();
          } else {
            if ($('#payment-sum').is(':focus')) {
              $('#payment-date').focus();
            } else {
              if ($('#payment-date').is(':focus')) {
                $scope.addPayment();
              }
            }
          }
        }
        return e.preventDefault();
      }
    });
  };
  $scope.addPayment = function() {
    var payment_card, payment_card_first_number, payment_date, payment_select, payment_sum, payment_type;
    payment_date = $('#payment-date');
    payment_sum = $('#payment-sum');
    payment_select = $('#payment-select');
    payment_type = $('#paymenttypes-select');
    payment_card = $('#payment-card-number');
    payment_card_first_number = $("#payment-card-first-number");
    if (!$scope.new_payment.id_status) {
      payment_select.focus().parent().addClass('has-error');
      return;
    } else {
      payment_select.parent().removeClass('has-error');
      if (1 === parseInt($scope.new_payment.id_status)) {
        if (!$scope.new_payment.card_first_number) {
          payment_card_first_number.focus().addClass('has-error');
          return;
        }
      } else {
        payment_card_first_number.removeClass('has-error');
      }
    }
    if (!$scope.new_payment.card_number) {
      payment_card.focus().addClass('has-error');
      return;
    } else {
      payment_card.removeClass('has-error');
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
      $scope.new_payment.entity_type = 'TEACHER';
      ajaxStart();
      $.post('ajax/PaymentEdit', $scope.new_payment, function(response) {
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
      $scope.new_payment.entity_id = $scope.Teacher.id;
      $scope.new_payment.entity_type = 'TEACHER';
      $scope.new_payment.id_type = 1;
      $scope.new_payment.id_user = $scope.user.id;
      ajaxStart();
      $.post('ajax/PaymentAdd', $scope.new_payment, function(response) {
        $scope.new_payment.id = response.id;
        $scope.new_payment.document_number = response.document_number;
        $scope.payments = initIfNotSet($scope.payments);
        $scope.payments.push($scope.new_payment);
        $scope.new_payment = {
          id_status: 0
        };
        $scope.$apply();
        ajaxEnd();
        lightBoxHide();
      }, 'json');
    }
  };
  $scope.deletePayment = function(index, payment) {
    if (!payment.confirmed) {
      bootbox.confirm('Вы уверены, что хотите удалить платеж?', function(result) {
        if (result === true) {
          console.log(index);
          $.post('ajax/deletePayment', {
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
          if (result === null) {

          } else if (hex_md5(result === payments_hash)) {
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
  $scope.formatDateMonthName = function(date) {
    return moment(date).format("D MMMM YY");
  };
  $scope.formatDate = function(date) {
    var dateOut;
    dateOut = new Date(date);
    return dateOut;
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
  $scope.shortenGrades = function() {
    var a, combo_end, combo_start, i, j, limit, pairs;
    a = $scope.Teacher.grades;
    if (a.length < 1) {
      return;
    }
    limit = a.length - 1;
    combo_end = -1;
    pairs = [];
    i = 0;
    while (i <= limit) {
      combo_start = parseInt(a[i]);
      if (combo_start > 11) {
        i++;
        combo_end = -1;
        pairs.push($scope.Grades[combo_start]);
        continue;
      }
      if (combo_start <= combo_end) {
        i++;
        continue;
      }
      j = i;
      while (j <= limit) {
        combo_end = parseInt(a[j]);
        if (combo_end >= 11) {
          break;
        }
        if (parseInt(a[j + 1]) - combo_end > 1) {
          break;
        }
        j++;
      }
      if (combo_start !== combo_end) {
        pairs.push(combo_start + '–' + combo_end + ' классы');
      } else {
        pairs.push(combo_start + ' класс');
      }
      i++;
    }
    $timeout(function() {
      return $('#public-grades').parent().find('.filter-option').html(pairs.join(', '));
    });
  };
  $(document).ready(function() {
    bindFileUpload();
    $("#subjects-select").selectpicker({
      noneSelectedText: "предметы",
      multipleSeparator: "+"
    });
    $('#public-grades').selectpicker({
      noneSelectedText: "классы",
      multipleSeparator: ", "
    });
    $scope.shortenGrades();
    $("#teacher-branches").selectpicker({
      noneSelectedText: "удобные филиалы для преподавателя"
    });
    return $("#teacher-edit").on('keyup change', 'input, select, textarea', function() {
      $scope.form_changed = true;
      return $scope.$apply();
    });
  });
  $(".save-button").on("click", function() {
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
    $scope.Teacher.public_grades = [];
    $("#public-grades option:selected").each(function() {
      if ($(this).val()) {
        return $scope.Teacher.public_grades.push($(this).val());
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
  $scope.emailDialog = function(email) {
    var html;
    $('#email-history').html('<center class="text-gray">загрузка истории сообщений...</center>');
    $('.email-template-list').hide();
    html = '';
    $.post('ajax/emailHistory', {
      'email': email
    }, function(response) {
      console.log(response);
      if (response) {
        $.each(response, function(i, v) {
          var files_html;
          files_html = '';
          $.each(v.files, function(i, file) {
            return files_html += '<div class="sms-coordinates"><a target="_blank" href="files/email/' + file.name + '" class="link-reverse small">' + file.uploaded_name + '</a><span> (' + file.size + ')</span></div>';
          });
          return html += '<div class="clear-sms"><div class="from-them">' + v.message + '<div class="sms-coordinates">' + v.coordinates + '</div>' + files_html + '</div></div>';
        });
        return $('#email-history').html(html);
      } else {
        return $('#email-history').html('');
      }
    }, 'json');
    $('#email-address').text(email);
    return lightBoxShow('email');
  };
  return $scope.getGroupsYears = function() {
    if ($scope.Groups) {
      return _.uniq(_.pluck(ang_scope.Groups, 'year'));
    }
  };
}).controller("ListCtrl", function($scope, $timeout) {
  $scope.in_egecentr = localStorage.getItem('teachers_in_egecentr') || 0;
  $scope.id_subject = localStorage.getItem('teachers_id_subject') || 0;
  $scope.othersCount = function() {
    return _.where($scope.Teachers, {
      had_lesson: 0
    }).length;
  };
  $scope.smsDialog = smsDialogTeachers;
  $scope.changeState = function() {
    localStorage.setItem('teachers_in_egecentr', $scope.in_egecentr);
    return $scope.refreshCounts();
  };
  $scope.changeSubjects = function() {
    localStorage.setItem('teachers_id_subject', $scope.id_subject);
    return $scope.refreshCounts();
  };
  $scope.teachersFilter = function(Teacher) {
    var subjects;
    subjects = [$scope.id_subject];
    return (!$scope.in_egecentr ? true : Teacher.in_egecentr === (parseInt($scope.in_egecentr) || 1)) && (!$scope.id_subject ? true : _.intersection(Teacher.subjects, subjects.map(Number)).length);
  };
  $scope.getCount = function(state, id_subject) {
    var subjects;
    subjects = [id_subject];
    return _.filter($scope.Teachers, function(Teacher) {
      return (!state ? true : Teacher.in_egecentr === (parseInt(state) || 1)) && (!id_subject ? true : _.intersection(Teacher.subjects, subjects.map(Number)).length);
    }).length;
  };
  $scope.refreshCounts = function() {
    return $timeout(function() {
      $('#state-select option, #subjects-select option').each(function(index, el) {
        $(el).data('subtext', $(el).attr('data-subtext'));
        return $(el).data('content', $(el).attr('data-content'));
      });
      return $('#state-select, #subjects-select').selectpicker('refresh', 100);
    });
  };
  angular.element(document).ready(function() {
    set_scope('Teacher');
    $("#subjects-select").selectpicker({
      noneSelectedText: "предметы",
      multipleSeparator: "+"
    });
    $("#state-select").selectpicker();
    return smsMode(4);
  });
  $scope.totalHold = function(grade) {
    var Teacher, denominator, k, len, numerator, ref;
    numerator = 0;
    denominator = 0;
    ref = $scope.Teachers;
    for (k = 0, len = ref.length; k < len; k++) {
      Teacher = ref[k];
      if (grade) {
        if (Teacher.loss_by_grade[grade]) {
          numerator += Teacher.total_lessons_by_grade[grade] - Teacher.loss_by_grade[grade];
          denominator += Teacher.total_lessons_by_grade[grade];
        }
      } else {
        numerator += Teacher.total_lessons - Teacher.loss;
        denominator += Teacher.total_lessons;
      }
    }
    if (!denominator) {
      return 0;
    }
    return Math.round(100 * numerator / denominator);
  };
  return $scope.totalLessons = function(grade) {
    var Teacher, k, len, ref, total_lessons;
    total_lessons = 0;
    ref = $scope.Teachers;
    for (k = 0, len = ref.length; k < len; k++) {
      Teacher = ref[k];
      if (grade) {
        if (Teacher.fact_lesson_cnt_by_grade[grade]) {
          total_lessons += Teacher.fact_lesson_cnt_by_grade[grade];
        }
      } else {
        total_lessons += Teacher.fact_lesson_total_cnt;
      }
    }
    return total_lessons;
  };
});
