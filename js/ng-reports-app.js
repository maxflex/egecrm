var app;

app = angular.module("Reports", ["ui.bootstrap"]).filter('to_trusted', [
  '$sce', function($sce) {
    return function(text) {
      return $sce.trustAsHtml(text);
    };
  }
]).filter('toArray', function() {
  return function(obj) {
    var arr;
    arr = [];
    $.each(obj, function(index, value) {
      return arr.push(value);
    });
    return arr;
  };
}).filter('range', function() {
  return function(input, total) {
    var i, j, ref;
    total = parseInt(total);
    for (i = j = 1, ref = total + 1; j < ref; i = j += 1) {
      input.push(i);
    }
    return input;
  };
}).controller("UserListCtrl", function($scope, $timeout) {
  $scope.helper_updating = false;
  $scope.formatDateTime = function(date) {
    return moment(date).format("DD.MM.YY в HH:mm");
  };
  $scope.forceNoreport = function(d) {
    return $.post("reports/AjaxForceNoreport", {
      id_student: d.id_entity,
      id_teacher: d.id_teacher,
      id_subject: d.id_subject,
      year: d.year
    }, function(response) {
      d.force_noreport = !d.force_noreport;
      return $scope.$apply();
    });
  };
  $scope.yearLabel = function(year) {
    return year + '-' + (parseInt(year) + 1) + ' уч. г.';
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
  $scope.updateHelperTable = function() {
    frontendLoadingStart();
    $scope.helper_updating = true;
    return $.post("reports/AjaxRecalcHelper", {}, function(response) {
      frontendLoadingEnd();
      $scope.helper_updating = false;
      $scope.reports_updated = response.date;
      $('#red-report-count').html(response.red_count);
      return $scope.$apply();
    }, "json");
  };
  $scope.filter = function() {
    delete $scope.Reports;
    $.cookie("reports", JSON.stringify($scope.search), {
      expires: 365,
      path: '/'
    });
    $scope.current_page = 1;
    return $scope.getByPage($scope.current_page);
  };
  $scope.pageChanged = function() {
    if ($scope.current_page > 1) {
      window.history.pushState({}, '', 'reports/?page=' + $scope.current_page);
    }
    return $scope.getByPage($scope.current_page);
  };
  $scope.getByPage = function(page) {
    frontendLoadingStart();
    return $.post("reports/AjaxGetReports", {
      page: page,
      teachers: $scope.Teachers
    }, function(response) {
      frontendLoadingEnd();
      $scope.Reports = response.data;
      $scope.counts = response.counts;
      $scope.$apply();
      return $scope.refreshCounts();
    }, "json");
  };
  return angular.element(document).ready(function() {
    set_scope("Reports");
    $scope.search = $.cookie("reports") ? JSON.parse($.cookie("reports")) : {};
    $scope.current_page = $scope.currentPage;
    $scope.pageChanged();
    return $(".single-select").selectpicker();
  });
}).controller("ListCtrl", function($scope) {
  $scope.getReports = function(id_student) {
    return _.where($scope.Reports, {
      id_student: id_student
    });
  };
  $scope.getSubjects = function(Visits) {
    return Object.keys(Visits);
  };
  $scope.noReports = function(Visits) {
    var has_reports;
    if (Visits === false || !Visits.length) {
      return true;
    }
    has_reports = false;
    $.each(Visits, function(index, Visit) {
      if (Visit.hasOwnProperty('id_student')) {
        has_reports = true;
      }
    });
    return !has_reports;
  };
  $scope.getYears = function() {
    var years;
    years = _.uniq(_.where($scope.Visits, {
      type_entity: 'STUDENT'
    }), function(Visit) {
      return Visit.year;
    });
    return _.pluck(years, 'year');
  };
  $scope.getByYears = function(year) {
    return _.where($scope.Visits, {
      year: year
    });
  };
  $scope.formatDate = function(date) {
    return moment(date).format("DD.MM.YY");
  };
  $scope.getDay = function(date) {
    return moment(date).format("dddd");
  };
  $scope.formatTime = function(time) {
    return time.slice(0, 5);
  };
  $scope.isReport = function(Report) {
    return Report.hasOwnProperty('homework_grade');
  };
  $scope.getByGrade = function(grade, id_group) {
    return _.where($scope.Students, {
      grade: grade,
      id_group: id_group
    });
  };
  return angular.element(document).ready(function() {
    setTimeout(function() {
      return $scope.$apply();
    }, 50);
    return set_scope("Reports");
  });
}).controller("AddCtrl", function($scope) {
  var textareasHaveErrors;
  $scope.setGrade = function(prop, n) {
    $scope.form_changed = true;
    if (!$scope.Report[prop] || $scope.Report[prop] !== n) {
      return $scope.Report[prop] = n;
    } else {
      return $scope.Report[prop] = 0;
    }
  };
  $scope.countSymbols = function(text) {
    if (!text || text.length <= 0) {
      return;
    }
    return text.length;
  };
  $scope.deleteReport = function() {
    return bootbox.confirm("Вы уверены, что хотите удалить отчет №" + $scope.Report.id + "?", function(result) {
      if (result === true) {
        ajaxStart();
        return $.post("reports/ajaxDelete", {
          id_report: $scope.Report.id
        }, function() {
          return history.back();
        });
      }
    });
  };
  $scope.addReport = function() {
    if (textareasHaveErrors()) {
      return;
    }
    ajaxStart();
    $scope.adding = true;
    return $.post("reports/ajaxAdd", {
      Report: $scope.Report
    }, function(response) {
      console.log(response);
      return history.back();
    }, "json");
  };
  $scope.formatDate = function(date) {
    return moment(date).format("DD.MM.YY");
  };
  $scope.formatDate2 = function(date) {
    var D;
    date = date.split(".");
    date = date.reverse();
    date = date.join("-");
    D = new Date(date);
    return moment(D).format("D MMMM YYYY года");
  };
  $scope.editReport = function() {
    if (textareasHaveErrors()) {
      return;
    }
    ajaxStart();
    $scope.saving = true;
    return $.post("reports/ajaxEdit", {
      Report: $scope.Report
    }, function(response) {
      ajaxEnd();
      $scope.form_changed = false;
      $scope.saving = false;
      $scope.$apply();
      return console.log(response);
    });
  };
  textareasHaveErrors = function() {
    var has_errors;
    if ($(".teacher-rating.active").length < 5) {
      notifyError('Не все оценки не установлены!');
      return true;
    }
    has_errors = false;
    $(".teacher-review-textarea").each(function(i, element) {
      if ($(element).val().length < 10) {
        $(element).focus().addClass("has-error");
        notifyError('Комментарий должен быть длиннее 10 символов');
        has_errors = true;
        return false;
      } else {
        return $(element).removeClass("has-error");
      }
    });
    return has_errors;
  };
  return angular.element(document).ready(function() {
    $(".form-change-control").on('keyup change', 'input, select, textarea', function() {
      $scope.form_changed = true;
      return $scope.$apply();
    });
    return set_scope("Reports");
  });
}).controller("TeacherListCtrl", function($scope, $timeout) {
  $scope.yearLabel = function(year) {
    return year + '-' + (parseInt(year) + 1) + ' уч. г.';
  };
  $scope.changeYear = function() {
    delete $scope.data;
    return $.post("reports/AjaxLoadByYear", {
      year: $scope.year
    }, function(response) {
      $scope.data = response;
      return $scope.$apply();
    }, 'json');
  };
  return angular.element(document).ready(function() {
    $scope.changeYear();
    return set_scope("Reports");
  });
});
