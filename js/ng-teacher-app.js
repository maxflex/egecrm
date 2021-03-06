var app;

app = angular.module("Teacher", ["ngMap", 'angucomplete-alt']).config([
  '$compileProvider', function($compileProvider) {
    $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|chrome-extension|sip|tel):/);
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
}).filter('toArray', function() {
  return function(obj) {
    var arr;
    arr = [];
    $.each(obj, function(index, value) {
      return arr.push(value);
    });
    return arr;
  };
}).controller('JournalCtrl', function($scope, $timeout) {
  $timeout(function() {
    return $scope.loadData();
  });
  $scope.grades = [];
  $scope.loadData = function() {
    $scope.loading = true;
    $.post('teachers/ajax/Journal', {
      year: $scope.year,
      id_teacher: $scope.id_teacher,
      grades: $scope.grades
    }, (function(response) {
      $scope.dates = response.dates;
      $scope.students = response.students;
      $scope.result = response.result;
      $scope.name_colors = response.name_colors;
      $scope.loading = false;
      $scope.$apply();
    }), 'json');
  };
  $scope.formatDate = function(d) {
    return moment(d).format('DD.MM.YY');
  };
  $scope.grayMonth = function(date) {
    var d;
    d = void 0;
    d = moment(date).format('M');
    d = parseInt(d);
    return d % 2 === 1;
  };
  $scope.yearLabel = function(year) {
    return year + '-' + parseInt(year) + 1 + ' уч. г.';
  };
  $scope.noMoreDates = function(student_id, date) {
    return date > Object.keys($scope.result[student_id]).sort().reverse()[0];
  };
  $scope.setYear = function(year) {
    $scope.year = year;
    $scope.loadData();
  };
  $scope.emptyResult = function() {
    return !$scope.result || Object.keys($scope.result).length === 0;
  };
  return angular.element(document).ready(function() {
    $('.watch-select').selectpicker();
    return set_scope('Teacher');
  });
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
  $scope.toBePaid = function() {
    var to_be_paid;
    to_be_paid = 0;
    $scope.Data.forEach(function(d) {
      return to_be_paid += d.real_sum - d.payment_sum;
    });
    return to_be_paid.toFixed(2);
  };
  return angular.element(document).ready(function() {
    return set_scope("Teacher");
  });
}).controller("EditCtrl", function($scope, $timeout, PhoneService, GroupService, Workplaces, UserService) {
  var _initReportsModule, _initReviewsModule, _loadData, _postData, bindFileUpload, deletePayment, loadMutualAccounts, menus;
  bindArguments($scope, arguments);
  $scope["enum"] = review_statuses;
  $scope.$watch("Teacher.id_head_teacher", function(newVal, oldVal) {
    if (newVal !== oldVal) {
      return $.post("teachers/ajax/saveHeadTeacher", {
        id_teacher: $scope.Teacher.id,
        id_head_teacher: newVal
      });
    }
  });
  $scope.getStudentsHint = function(Lesson) {
    var student_names;
    student_names = Lesson.students.map(function(student_id) {
      return $scope.getStudentName(student_id);
    });
    return student_names.join("\n");
  };
  $timeout(function() {
    $scope.stats_ec_loading = false;
    return $scope.search_stats = {
      id_teacher: $scope.Teacher.id,
      years: [($scope.academic_year - 2).toString(), ($scope.academic_year - 1).toString()],
      grades: ['9', '10', '11']
    };
  });
  $scope.filterStats = function() {
    $scope.stats_ec_loading = true;
    return $.post("teachers/ajax/stats", $scope.search_stats, function(response) {
      $scope.stats_ec = response;
      $scope.stats_ec_loading = false;
      return $scope.$apply();
    }, 'json');
  };
  _initReportsModule = function() {
    $scope.search = $.cookie("reports") ? JSON.parse($.cookie("reports")) : {};
    $scope.search.id_teacher = $scope.Teacher.id;
    $scope.filter();
    return $(".single-select").selectpicker();
  };
  $scope.loadReports = function() {
    frontendLoadingStart();
    return $.post("reports/AjaxGetReports", {
      page: -1,
      teachers: []
    }, function(response) {
      frontendLoadingEnd();
      $scope.Reports = response.data;
      $scope.counts = response.counts;
      $scope.$apply();
      return $scope.refreshCounts();
    }, "json");
  };
  $scope.filter = function() {
    delete $scope.Reports;
    $.cookie("reports", JSON.stringify($scope.search), {
      expires: 365,
      path: '/'
    });
    return $scope.loadReports();
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
  $scope["enum"] = review_statuses;
  $scope.enum_approved = review_statuses_approved;
  _initReviewsModule = function() {
    $scope.search_reviews = $.cookie("reviews") ? JSON.parse($.cookie("reviews")) : {};
    $scope.search_reviews.id_teacher = $scope.Teacher.id;
    $scope.filterReviews();
    return $(".single-select").selectpicker();
  };
  $scope.loadReviews = function() {
    frontendLoadingStart();
    return $.post("ajax/GetReviews", {
      page: -1,
      teachers: []
    }, function(response) {
      frontendLoadingEnd();
      $scope.Reviews = response.data;
      $scope.counts_review = response.counts;
      $scope.$apply();
      return $scope.refreshCounts();
    }, "json");
  };
  $scope.filterReviews = function() {
    delete $scope.Reviews;
    $.cookie("reviews", JSON.stringify($scope.search_reviews), {
      expires: 365,
      path: '/'
    });
    return $scope.loadReviews();
  };
  $scope.studentSelected = function(Student) {
    var student_id;
    student_id = Student.originalObject.id;
    if ($scope.modal_additional_lesson.students.indexOf(student_id) !== -1) {
      return;
    }
    $scope.modal_additional_lesson.students.push(student_id);
    return console.log('selected student', Student);
  };
  $scope.getStudentName = function(id) {
    var fio, student;
    student = _.findWhere($scope.Students, {
      id: id
    });
    fio = student.name.split(' ');
    return fio[0] + ' ' + fio[1];
  };
  $scope.deleteStudent = function(index) {
    return $scope.modal_additional_lesson.students.splice(index, 1);
  };
  $scope.reverseObjKeys = function(obj) {
    return Object.keys(obj).reverse();
  };
  $scope.yearLabel = function(year) {
    return year + '-' + (parseInt(year) + 1) + ' уч. г.';
  };
  $scope.setYear = function(year) {
    return $scope.selected_year = year;
  };
  $scope.addAdditionalLessonDialog = function(additional_lesson) {
    if (additional_lesson == null) {
      additional_lesson = null;
    }
    if (additional_lesson === null) {
      $scope.modal_additional_lesson = {
        students: [],
        id_teacher: $scope.Teacher.id,
        year: getYear(),
        lesson_date: moment().format('DD.MM.YY')
      };
    } else {
      $scope.modal_additional_lesson = _.clone(additional_lesson);
      $scope.modal_additional_lesson.lesson_date = moment($scope.modal_additional_lesson.lesson_date).format('DD.MM.YY');
    }
    return lightBoxShow('additional-lesson');
  };
  $scope.saveAdditionalLesson = function() {
    lightBoxHide();
    $scope.modal_additional_lesson.lesson_date = convertDate($scope.modal_additional_lesson.lesson_date);
    if ($scope.modal_additional_lesson.id) {
      ajaxStart();
      return $.post('ajax/SaveAdditionalLesson', $scope.modal_additional_lesson, function(response) {
        var index;
        index = _.findIndex($scope.AdditionalLessons, {
          id: $scope.modal_additional_lesson.id
        });
        $scope.AdditionalLessons[index] = response;
        $scope.$apply();
        return ajaxEnd();
      }, 'json');
    } else {
      ajaxStart();
      return $.post('ajax/SaveAdditionalLesson', $scope.modal_additional_lesson, function(response) {
        $scope.AdditionalLessons.push(response);
        $scope.$apply();
        return ajaxEnd();
      }, 'json');
    }
  };
  $scope.deleteAdditionalLesson = function() {
    return bootbox.confirm('Вы уверены, что хотите удалить доп. занятие?', function(result) {
      if (result === true) {
        return $.post('ajax/deleteAdditionalLesson', {
          'id': $scope.modal_additional_lesson.id
        }, function() {
          $scope.AdditionalLessons = _.without($scope.AdditionalLessons, _.findWhere($scope.AdditionalLessons, {
            id: $scope.modal_additional_lesson.id
          }));
          $timeout(function() {
            return $scope.$apply();
          });
          return lightBoxHide();
        });
      }
    });
  };
  $scope.getCabinet = function(id) {
    return _.findWhere($scope.all_cabinets, {
      id: parseInt(id)
    });
  };
  $scope.addAdditionalPaymentDialog = function() {
    $scope.new_additional_payment = {
      id_teacher: $scope.Teacher.id,
      year: getYear(),
      date: moment().format('DD.MM.YY')
    };
    return lightBoxShow('additional-payment');
  };
  menus = ['Groups', 'Reviews', 'Lessons', 'payments', 'Reports', 'Stats', 'Bars', 'TeacherAdditionalPayments'];
  $scope.setMenu = function(menu, complex_data) {
    if (menu === 1) {
      _initReviewsModule();
    }
    if (menu === 4) {
      _initReportsModule();
    } else {
      if (menu === 5) {
        $scope.filterStats();
      }
      $.each(menus, function(index, value) {
        return _loadData(index, menu, value, complex_data);
      });
    }
    return $scope.current_menu = menu;
  };
  _postData = function(menu) {
    return {
      id_teacher: $scope.Teacher.id,
      menu: menu
    };
  };
  _loadData = function(menu, selected_menu, ngModel, complex_data) {
    if ($scope[ngModel] === void 0 && menu === selected_menu) {
      return $.post("teachers/ajax/menu", _postData(menu), function(response) {
        if (complex_data) {
          _.each(response, function(value, field) {
            return $scope[field] = value;
          });
        } else {
          $scope[ngModel] = response;
          $timeout(function() {
            return $('.watch-select').selectpicker();
          });
        }
        return $scope.$apply();
      }, "json");
    }
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
  $scope.yearDifference = function(year) {
    return moment().format("YYYY") - year;
  };
  $scope.show_all_lessons = false;
  $scope.getLessons = function() {
    if ($scope.show_all_lessons) {
      return $scope.Lessons;
    }
    return _.filter($scope.Lessons, function(Lesson) {
      return Lesson.date > $scope.academic_year + "-07-15";
    });
  };
  $scope.toggleFreetime = function(day, id_time) {
    var mode;
    mode = $scope.Bars.Freetime[day][id_time] === 'green' ? 'Delete' : 'Add';
    ajaxStart();
    $.post('ajax/' + mode + 'Freetime', {
      'id_entity': $scope.Teacher.id,
      'type_entity': 'teacher',
      'id_time': id_time
    }, function() {
      ajaxEnd();
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
  $scope.lessonsTotalSum = function() {
    var lessons_sum;
    lessons_sum = 0;
    if ($scope.Lessons) {
      $.each($scope.Lessons, function(index, value) {
        return lessons_sum += parseInt(value.price);
      });
    }
    return lessons_sum;
  };
  $scope.lessonsTotalPaid = function(from_lessons) {
    var payments_sum;
    payments_sum = 0;
    if (from_lessons && $scope.Lessons) {
      $.each($scope.Lessons, function(index, lesson) {
        var k, len, payment, ref, results;
        ref = lesson.payments;
        results = [];
        for (k = 0, len = ref.length; k < len; k++) {
          payment = ref[k];
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
    var lessons_sum, payments_sum;
    if (!($scope.Lessons && $scope.Lessons.length)) {
      return;
    }
    lessons_sum = $scope.lessonsTotalSum();
    payments_sum = $scope.lessonsTotalPaid(from_lessons);
    return lessons_sum - payments_sum;
  };
  $scope.formatDate2 = function(date) {
    var dateOut;
    dateOut = new Date(date);
    return dateOut;
  };
  $scope.dateFromCustomFormat = function(date) {
    var D;
    date = date.split(".");
    date = date.reverse();
    date = date.join("-");
    D = new Date(date);
    return moment(D).format("D MMMM YYYY");
  };
  $scope.confirmPayment = function(payment) {
    if ($scope.user_rights.indexOf(11) === -1) {
      return;
    }
    payment.confirmed = payment.confirmed ? 0 : 1;
    return $.post('ajax/confirmPayment', {
      id: payment.id,
      confirmed: payment.confirmed
    });
  };
  $scope.editPayment = function(payment) {
    if (payment.confirmed && $scope.user_rights.indexOf(11) === -1) {
      return;
    }
    $scope.new_payment = angular.copy(payment);
    loadMutualAccounts($scope.new_payment.id_status);
    return lightBoxShow('addpayment');
  };
  $scope.editPaymentAdditional = function(payment) {
    $scope.new_additional_payment = angular.copy(payment);
    return lightBoxShow('additional-payment');
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
  $scope.addPaymentDialog = function() {
    $scope.new_payment = {
      id_status: 0,
      year: getYear(),
      entity_id: $scope.Teacher.id
    };
    lightBoxShow('addpayment');
    $scope.handleKeyPress();
    return setTimeout(function() {
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
  $scope.addAdditionalPayment = function() {
    if ($scope.new_additional_payment.id) {
      ajaxStart();
      return $.post('ajax/PaymentAdditionalEdit', $scope.new_additional_payment, function(response) {
        angular.forEach($scope.TeacherAdditionalPayments, function(payment, i) {
          if (payment.id === $scope.new_additional_payment.id) {
            $scope.TeacherAdditionalPayments[i] = $scope.new_additional_payment;
            return $scope.$apply();
          }
        });
        ajaxEnd();
        return lightBoxHide();
      });
    } else {
      ajaxStart();
      return $.post('ajax/PaymentAdditionalAdd', $scope.new_additional_payment, function(response) {
        if (!$.isArray($scope.TeacherAdditionalPayments)) {
          $scope.TeacherAdditionalPayments = [];
        }
        $scope.TeacherAdditionalPayments.push(response);
        $scope.$apply();
        ajaxEnd();
        return lightBoxHide();
      }, 'json');
    }
  };
  $scope.addPayment = function() {
    var payment_card, payment_card_first_number, payment_category, payment_date, payment_select, payment_sum, payment_type, payment_year;
    payment_date = $('#payment-date');
    payment_year = $("#payment-year");
    payment_category = $("#payment-category");
    payment_sum = $('#payment-sum');
    payment_select = $('#payment-select');
    payment_type = $('#paymenttypes-select');
    payment_card = $('#payment-card-number');
    payment_card_first_number = $("#payment-card-first-number");
    if (!parseInt($scope.new_payment.id_status)) {
      payment_select.focus().parent().addClass('has-error');
      return;
    } else {
      payment_select.parent().removeClass('has-error');
      if (parseInt($scope.new_payment.id_status) === 1) {
        if (!$scope.new_payment.card_number) {
          payment_card.focus().addClass('has-error');
          return;
        } else {
          payment_card.removeClass('has-error');
        }
      }
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
        if ($scope.tobe_paid) {
          $scope.tobe_paid -= $scope.new_payment.sum;
        }
        $scope.new_payment = {
          id_status: 0
        };
        $scope.$apply();
        ajaxEnd();
        lightBoxHide();
      }, 'json');
    }
  };
  deletePayment = function() {
    return bootbox.confirm('Вы уверены, что хотите удалить платеж?', function(result) {
      if (result === true) {
        return $.post('ajax/deletePayment', {
          'id_payment': $scope.new_payment.id
        }, function() {
          $scope.payments = _.without($scope.payments, _.findWhere($scope.payments, {
            id: $scope.new_payment.id
          }));
          if ($scope.tobe_paid) {
            $scope.tobe_paid += parseInt($scope.new_payment.sum);
          }
          $timeout(function() {
            return $scope.$apply();
          });
          return lightBoxHide();
        });
      }
    });
  };
  $scope.deletePayment = function() {
    if ($scope.new_payment.confirmed && $scope.user_rights.indexOf(11) === -1) {
      return;
    }
    return deletePayment();
  };
  $scope.deletePaymentAdditional = function() {
    return bootbox.confirm('Вы уверены, что хотите удалить доп. услугу?', function(result) {
      if (result === true) {
        return $.post('ajax/deletePaymentAdditional', {
          'id_payment': $scope.new_additional_payment.id,
          'id_teacher': $scope.Teacher.id
        }, function() {
          $scope.TeacherAdditionalPayments = _.without($scope.TeacherAdditionalPayments, _.findWhere($scope.TeacherAdditionalPayments, {
            id: $scope.new_additional_payment.id
          }));
          $timeout(function() {
            return $scope.$apply();
          });
          return lightBoxHide();
        });
      }
    });
  };
  $scope.formatDateMonthName = function(date, full_year) {
    return moment(date).format("D MMMM YY" + (full_year ? 'YY' : ''));
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
    switch (window.location.hash) {
      case '#additional':
        $scope.setMenu(7, true);
    }
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
    $scope.Teacher.subjects_ec = [];
    $("#subjects-select option:selected").each(function() {
      if ($(this).val()) {
        return $scope.Teacher.subjects_ec.push($(this).val());
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
  $scope.getGroupsYears = function() {
    if ($scope.Groups) {
      return _.uniq(_.pluck(ang_scope.Groups, 'year'));
    }
  };
  return $scope.getReviewsYears = function() {
    if ($scope.Reviews) {
      return _.uniq(_.pluck(ang_scope.Reviews, 'year'));
    }
  };
}).controller("ListCtrl", function($scope, $timeout, $http, PhoneService, Workplaces) {
  bindArguments($scope, arguments);
  $scope.in_egecentr = localStorage.getItem('teachers_in_egecentr') || '';
  $scope.id_subject = localStorage.getItem('teachers_id_subject') || 0;
  $timeout(function() {
    $("#filter-branches").selectpicker({
      noneSelectedText: "филиалы"
    }).selectpicker('refresh');
    return $http.post("teachers/ajax/LoadAll").then(function(response) {
      console.log(response.data);
      $scope.Teachers = response.data;
      return $timeout(function() {
        return $scope.refreshCounts();
      });
    });
  });
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
    var branches, subjects;
    subjects = [$scope.id_subject];
    branches = [$scope.filter_branch];
    return ($scope.in_egecentr === '' ? true : Teacher.in_egecentr === parseInt($scope.in_egecentr)) && (!$scope.id_subject ? true : _.intersection(Teacher.subjects_ec, subjects.map(Number)).length) && (!$scope.filter_branch ? true : _.intersection(Teacher.branches, branches.map(Number)).length);
  };
  $scope.getCount = function(state, id_subject) {
    var branches, subjects;
    subjects = [id_subject];
    branches = [$scope.filter_branch];
    return _.filter($scope.Teachers, function(Teacher) {
      return (state === '' ? true : Teacher.in_egecentr === parseInt(state)) && (!id_subject ? true : _.intersection(Teacher.subjects_ec, subjects.map(Number)).length) && (!$scope.filter_branch ? true : _.intersection(Teacher.branches, branches.map(Number)).length);
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
    return $("#state-select").selectpicker();
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
