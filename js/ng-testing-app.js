var app,
  indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

app = angular.module("Testing", ['angucomplete-alt']).filter('range', function() {
  return function(input, total) {
    var i, j, ref;
    total = parseInt(total);
    for (i = j = 1, ref = total + 1; j < ref; i = j += 1) {
      input.push(i);
    }
    return input;
  };
}).controller("ListCtrl", function($scope) {
  $scope.formatDate = function(date) {
    return moment(date).format('DD MMMM');
  };
  return angular.element(document).ready(function() {
    return set_scope("Testing");
  });
}).controller("StudentsCtrl", function($scope) {
  $scope.testingStarted = function(Testing) {
    return moment(Testing.date + " " + Testing.start_time).unix() <= Math.floor(Date.now() / 1000);
  };
  $scope.addTesting = function(Testing) {
    Testing.adding = true;
    ajaxStart();
    return $.post('testing/ajaxAddStudent', {
      id_testing: Testing.id,
      id_subject: Testing.selected_subject,
      grade: $scope.grade
    }, function(response) {
      ajaxEnd();
      Testing.Students = initIfNotSet(Testing.Students);
      Testing.Students.push(response);
      $scope.$apply();
      return $('.subject-select').selectpicker('refresh');
    }, "json");
  };
  $scope.getTesting = function(Testing) {
    return _.findWhere(Testing.Students, {
      id_student: $scope.id_student
    });
  };
  $scope.getAllSubjects = function(Testing) {
    var subject_ids;
    subject_ids = [];
    if ($scope.grade === 11 && Testing.subjects_11 !== null) {
      $.each(Testing.subjects_11, function(id_subject, value) {
        if (id_subject > 0) {
          return subject_ids.push(id_subject);
        }
      });
    } else if ($scope.grade === 9 && Testing.subjects_9 !== null) {
      $.each(Testing.subjects_9, function(id_subject, value) {
        if (id_subject > 0) {
          return subject_ids.push(id_subject);
        }
      });
    }
    return subject_ids;
  };
  $scope.getAllSubjects = function(Testing) {
    var subject_ids;
    subject_ids = [];
    if ($scope.grade === 11 && Testing.subjects_11 !== null) {
      $.each(Testing.subjects_11, function(id_subject, value) {
        if (id_subject > 0) {
          return subject_ids.push(id_subject);
        }
      });
    } else if ($scope.grade === 9 && Testing.subjects_9 !== null) {
      $.each(Testing.subjects_9, function(id_subject, value) {
        if (id_subject > 0) {
          return subject_ids.push(id_subject);
        }
      });
    }
    return subject_ids;
  };
  $scope.totalTestsCount = function(Testing) {
    return Object.keys(Testing.subjects_9).length + Object.keys(Testing.subjects_11).length;
  };
  $scope.isAvailable = function(Testing, id_subject) {
    if ($scope.grade === 11) {
      return indexOf.call(Object.keys(Testing.subjects_11), id_subject) >= 0;
    } else {
      return indexOf.call(Object.keys(Testing.subjects_9), id_subject) >= 0;
    }
  };
  $scope.formatDate = function(date) {
    return moment(date).format('DD MMMM');
  };
  return angular.element(document).ready(function() {
    $(".subject-select").selectpicker();
    return set_scope("Testing");
  });
}).controller("AddCtrl", function($scope) {
  $scope.formatDay = function(date) {
    var day, month;
    day = moment(date).format("ddd");
    month = moment(date).format(", DD MMMM");
    return day.toUpperCase() + month;
  };
  $scope.subjectChecked = function(grade, id_subject) {
    var arr;
    if (grade === 11) {
      arr = $scope.Testing.subjects_11;
    } else {
      arr = $scope.Testing.subjects_9;
    }
    return $.inArray(parseInt(id_subject), arr) >= 0;
  };
  $scope.deleteTesting = function(id_testing) {
    return bootbox.confirm("Вы уверены, что хотите удалить тестирование №" + id_testing + "?", function(result) {
      if (result === true) {
        ajaxStart();
        return $.post("testing/ajaxDelete", {
          id_testing: id_testing
        }, function() {
          return redirect("testing");
        });
      }
    });
  };
  $scope.changeDate = function() {
    $scope.cabinet_load = void 0;
    ajaxStart();
    return $.post("testing/ajaxChangeDate", {
      id: $scope.Testing.id,
      date: $scope.Testing.date
    }, function(response) {
      ajaxEnd();
      $scope.cabinet_load = response;
      return $scope.$apply();
    }, "json");
  };
  $scope.refreshSelect = function() {
    return setTimeout(function() {
      return $('#subject-add-student').selectpicker('refresh');
    }, 100);
  };
  $scope.notEnoughTime = function(minutes) {
    var date_end, date_start, minutes_end, minutes_start;
    if (!$scope.Testing || !$scope.Testing.start_time || !$scope.Testing.end_time) {
      return true;
    }
    date_start = new Date('2015-09-01 ' + $scope.Testing.start_time);
    date_end = new Date('2015-09-01 ' + $scope.Testing.end_time);
    minutes_start = (date_start.getHours() * 60) + date_start.getMinutes();
    minutes_end = (date_end.getHours() * 60) + date_end.getMinutes();
    return (minutes_end - minutes_start) < minutes;
  };
  $scope.addStudent = function() {
    var data;
    if (!$scope.selectedSubjectGrade || !$scope.selectedStudent) {
      return;
    }
    $scope.form_changed = true;
    data = $scope.selectedSubjectGrade.split('|');
    $scope.Testing.Students = initIfNotSet($scope.Testing.Students);
    $scope.Testing.Students.push({
      id_student: $scope.selectedStudent.originalObject.id,
      id_subject: data[0],
      grade: data[1]
    });
    $.post('testing/ajaxGetStudentGroupsBySubject', {
      id_student: $scope.selectedStudent.originalObject.id,
      id_subject: data[0],
      grade: data[1]
    }, function(response) {
      var l;
      l = $scope.Testing.Students.length;
      $scope.Testing.Students[l - 1].group_ids = response;
      return $scope.$apply();
    }, "json");
    $scope.selectedSubjectGrade = void 0;
    $scope.selectedStudent = void 0;
    setTimeout(function() {
      $scope.$broadcast('angucomplete-alt:clearInput');
      $scope.$apply();
      return $('#subject-add-student').selectpicker('refresh');
    }, 50);
    return false;
  };
  $scope.getStudent = function(id_student) {
    return _.findWhere($scope.Students, {
      id: id_student
    });
  };
  $scope.deleteStudent = function(id_student) {
    $scope.form_changed = true;
    return $scope.Testing.Students = _.without($scope.Testing.Students, _.findWhere($scope.Testing.Students, {
      id_student: id_student
    }));
  };
  $scope.form_changed = false;
  $scope.saveTesting = function() {
    $scope.saving = true;
    ajaxStart();
    return $.post("testing/ajaxSave", {
      Testing: $scope.Testing
    }, function(response) {
      ajaxEnd();
      $scope.saving = false;
      $scope.form_changed = false;
      return $scope.$apply();
    });
  };
  $scope.addTesting = function() {
    ajaxStart();
    $scope.adding = true;
    return $.post("testing/ajaxAdd", {
      Testing: $scope.Testing
    }, function(response) {
      console.log(response);
      return redirect("testing/");
    }, "json");
  };
  return angular.element(document).ready(function() {
    $(".form-change-control").on('keyup change', 'input, select', function() {
      $scope.form_changed = true;
      return $scope.$apply();
    });
    if ($scope.Testing !== void 0) {
      $scope.changeDate();
    }
    $('#subject-add-student').selectpicker();
    return set_scope("Testing");
  });
});
