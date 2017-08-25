var app, testy,
  indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

testy = false;

app = angular.module("Group", ['ngAnimate', 'chart.js']).filter('toArray', function() {
  return function(obj) {
    var arr;
    arr = [];
    $.each(obj, function(index, value) {
      return arr.push(value);
    });
    return arr;
  };
}).filter('to_trusted', [
  '$sce', function($sce) {
    return function(text) {
      return $sce.trustAsHtml(text);
    };
  }
]).filter('orderByDayNumber', function() {
  return function(items, field, reverse) {
    var filtered;
    filtered = [];
    angular.forEach(items, function(item) {
      return filtered.push(item);
    });
    filtered.sort(function(a, b) {
      if (a[field] > b[field]) {
        return 1;
      } else {
        return -1;
      }
    });
    if (reverse) {
      filtered.reverse();
    }
    return filtered;
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
}).controller("JournalCtrl", function($scope) {
  $scope.grayMonth = function(date) {
    var d;
    d = moment(date).format("M");
    d = parseInt(d);
    return d % 2 === 1;
  };
  $scope.getInfo = function(id_student, Schedule) {
    return _.findWhere($scope.LessonData, {
      id_entity: id_student,
      lesson_date: Schedule.date,
      lesson_time: Schedule.time
    });
  };
  $scope.formatDate = function(date) {
    return moment(date).format("DD.MM.YY");
  };
  return angular.element(document).ready(function() {
    return set_scope("Group");
  });
}).controller("LessonCtrl", function($scope) {
  var until_save_interval;
  $scope.formatDate = function(date) {
    var D;
    date = date.split(".");
    date = date.reverse();
    date = date.join("-");
    D = new Date(date);
    return moment(D).format("D MMMM YYYY г.");
  };
  $scope.timeUntilSave = function() {
    var data, date_lesson, date_now, diff;
    date_now = new Date();
    if (!$scope.Schedule.time) {
      $scope.Schedule.time = '00:00';
    }
    date_lesson = new Date($scope.Schedule.date + " " + $scope.Schedule.time + ":00");
    diff = date_now.getTime() - date_lesson.getTime();
    console.log('diff', diff);
    data = {
      seconds: 59 - (Math.floor(diff / 1000) - (Math.floor(diff / 1000 / 60) * 60)),
      minutes: 40 - Math.floor(diff / 1000 / 60)
    };
    if (data.minutes < 0) {
      return true;
    }
    if (data.minutes === 0 && data.seconds <= 0) {
      return true;
    } else {
      return data;
    }
  };
  until_save_interval = setInterval(function() {
    $scope.until_save = $scope.timeUntilSave();
    if ($scope.until_save === true) {
      clearInterval(until_save_interval);
    }
    return $scope.$apply();
  }, 1000);
  $scope.editStudent = function(Student) {
    $scope.EditStudent = Student;
    $scope.EditLessonData = angular.copy($scope.LessonData[$scope.EditStudent.id]);
    clearSelect();
    return lightBoxShow('edit-student');
  };
  $scope.saveStudent = function() {
    $scope.LessonData[$scope.EditStudent.id] = $scope.EditLessonData;
    $scope.students_not_filled = _.filter($scope.LessonData, function(v) {
      return v && +v.presence;
    }).length !== $scope.Schedule.Group.Students.length;
    return lightBoxHide();
  };
  $scope.registerInJournal = function() {
    return bootbox.confirm("Записать запись в журнал?", function(result) {
      if (result === true) {
        if (_.without($scope.LessonData, void 0).length !== $scope.Schedule.Group.Students.length) {
          return bootbox.alert("Заполните данные по всем ученикам перед записью в журнал");
        } else {
          $scope.saving = true;
          $scope.$apply();
          ajaxStart();
          return $.post("groups/ajax/registerInJournal", {
            id_schedule: $scope.Schedule.id,
            data: $scope.LessonData
          }, function(response) {
            ajaxEnd();
            $scope.saving = false;
            $scope.Schedule.was_lesson = true;
            return $scope.$apply();
          });
        }
      }
    });
  };
  $scope.changeRegisterInJournal = function() {
    return bootbox.confirm("Сохранить изменения?", function(result) {
      if (result === true) {
        if (_.without($scope.LessonData, void 0).length !== $scope.Schedule.Group.Students.length) {
          return bootbox.alert("Заполните данные по всем ученикам перед записью в журнал");
        } else {
          $scope.saving = true;
          $scope.$apply();
          ajaxStart();
          return $.post("groups/ajax/registerInJournalWithoutSMS", {
            id_schedule: $scope.Schedule.id,
            data: $scope.LessonData
          }, function(response) {
            ajaxEnd();
            $scope.saving = false;
            $scope.Schedule.was_lesson = true;
            return $scope.$apply();
          });
        }
      }
    });
  };
  return angular.element(document).ready(function() {
    $scope.until_save = $scope.timeUntilSave();
    $scope.students_not_filled = true;
    $scope.$apply();
    return set_scope("Group");
  });
}).controller("EditCtrl", function($scope, $timeout, PhoneService, GroupService) {
  var bindDraggable, bindGroupsDroppable, checkFreeCabinets, justSave, rebindBlinking;
  bindArguments($scope, arguments);
  $timeout(function() {
    return ajaxEnd();
  });
  $scope.timeClick = function(day, time) {
    if ($scope.timeChecked(day, time)) {
      $scope.Group.day_and_time[day] = _.reject($scope.Group.day_and_time[day], function(t) {
        return t.id_time === time.id;
      });
      if (!$scope.Group.day_and_time[day].length) {
        return delete $scope.Group.day_and_time[day];
      }
    } else {
      if ($scope.Group.day_and_time[day] === void 0) {
        $scope.Group.day_and_time[day] = [];
      }
      return $scope.Group.day_and_time[day].push({
        time: time,
        id_time: time.id
      });
    }
  };
  $scope.timeChecked = function(day, time) {
    return $scope.Group.day_and_time[day] && $scope.getGroupTime(day, time) !== void 0;
  };
  $scope.getGroupTime = function(day, time) {
    return _.findWhere($scope.Group.day_and_time[day], {
      id_time: time.id
    });
  };
  $scope.dayAndTime = function() {
    return lightBoxShow("freetime");
  };
  $scope.getTestStatus = function(Test) {
    return test_statuses[Test.intermediate];
  };
  $scope.saveDayAndTime = function() {
    lightBoxHide();
    return justSave(function() {
      $scope.updateCabinetBar(false);
      $scope.reloadSmsNotificationStatuses();
      return checkFreeCabinets();
    });
  };
  $scope.hasDayAndTime = function() {
    return Object.keys($scope.Group.day_and_time).length;
  };
  rebindBlinking = function() {
    var blinking;
    blinking = $(".blink");
    blinking.removeClass("blink");
    return setTimeout(function() {
      return blinking.addClass("blink", 50);
    });
  };
  $scope.getSubject = function(subjects, id_subject) {
    return _.findWhere(subjects, {
      id_subject: id_subject
    });
  };
  bindGroupsDroppable = function() {
    return $(".group-list").droppable({
      tolerance: 'pointer',
      hoverClass: "request-status-drop-hover",
      drop: function(event, ui) {
        var Group, id_group, id_student, old_id_group;
        id_group = $(this).data("id");
        id_student = $(ui.draggable).data("id");
        Group = $scope.getGroup(id_group);
        if (indexOf.call(Group.students, id_student) >= 0) {
          return notifySuccess("Ученик уже в группе");
        } else {
          old_id_group = $scope.Group && ($scope.Group.id !== id_group) ? $scope.Group.id : false;
          ajaxStart();
          return $.post("groups/ajax/AddStudentDnd", {
            id_group: id_group,
            id_student: id_student,
            old_id_group: old_id_group
          }, function() {
            ajaxEnd();
            Group.students.push(id_student);
            $scope.removeStudent(id_student, true);
            return $scope.$apply();
          });
        }
      }
    });
  };
  $scope.search_groups = {
    grade: "",
    id_subject: "",
    year: ""
  };
  $scope.groupsFilter = function(Group) {
    if (Group.id === $scope.Group.id) {
      return false;
    }
    return (Group.grade === parseInt($scope.search_groups.grade) || !$scope.search_groups.grade) && (parseInt($scope.search_groups.year) === Group.year || !$scope.search_groups.year) && (parseInt($scope.search_groups.id_subject) === Group.id_subject || !$scope.search_groups.id_subject);
  };
  bindDraggable = function() {
    if ($(".student-line").length) {
      $(".student-line").draggable({
        helper: 'clone',
        revert: 'invalid',
        start: function(event, ui) {
          $scope.is_student_dragging = true;
          $scope.$apply();
          $(this).css("visibility", "hidden");
          return $(ui.helper).addClass("single-dragging");
        },
        stop: function(event, ui) {
          $scope.is_student_dragging = false;
          $scope.$apply();
          return $(this).css("visibility", "visible");
        }
      });
      return $(".student-dragout").droppable({
        tolerance: 'pointer',
        hoverClass: 'student-dragout-hover',
        drop: function(event, ui) {
          var id_student;
          ui.draggable.remove();
          id_student = $(ui.draggable).data("id");
          $scope.removeStudent(id_student);
          return $scope.$apply();
        }
      });
    }
  };
  checkFreeCabinets = function() {
    return $.post('groups/ajax/checkFreeCabinets', {
      id_group: $scope.Group.id,
      year: $scope.Group.year
    }, function(response) {
      $scope.free_cabinets = response;
      $scope.$apply();
      return $timeout(function() {
        return $('.branch-cabinet').selectpicker('refresh');
      });
    }, 'json');
  };
  $scope.changeYear = function() {
    $scope.changeTeacher();
    $scope.reloadSmsNotificationStatuses();
    return $scope.updateGroup({
      year: $scope.Group.year
    });
  };
  $scope.enoughSmsParams = function() {
    return $scope.Group.year > 0 && $scope.Group.id_subject > 0 && $scope.Group.cabinet_ids.length > 0 && $scope.Group.first_schedule && $scope.Group.id_subject > 0 && $scope.FirstLesson.cabinet;
  };
  $scope.changeTeacher = function() {
    if (!$scope.Group.id) {
      return;
    }
    console.log('changin teacher');
    ajaxStart();
    $.post("groups/ajax/changeTeacher", {
      id_group: $scope.Group.id,
      id_subject: $scope.Group.id_subject,
      day_and_time: $scope.Group.day_and_time,
      id_teacher: $scope.Group.id_teacher,
      year: $scope.Group.year,
      students: $scope.Group.students
    }, function(response) {
      ajaxEnd();
      console.log('teacher changed', response);
      $.each(response.teacher_like_statuses, function(id_student, id_status) {
        return $scope.getStudent(id_student).teacher_like_status = id_status;
      });
      if ($scope.Group.id_teacher) {
        $scope.getTeacher($scope.Group.id_teacher).agreement = response.agreement;
      }
      return $scope.$apply();
    }, "json");
    return $scope.updateTeacherBar();
  };
  $scope.updateTeacherBar = function() {
    if ($scope.Group.id_teacher === "0") {
      return;
    }
    ajaxStart();
    return $.post("groups/ajax/GetTeacherBar", {
      id_teacher: $scope.Group.id_teacher,
      id_group: $scope.Group.id
    }, function(bar) {
      var ref;
      ajaxEnd();
      if ((ref = $scope.getTeacher($scope.Group.id_teacher)) != null) {
        ref.bar = bar;
      }
      $scope.$apply();
      return rebindBlinking();
    }, "json");
  };
  $scope.updateCabinetBar = function(ajax_animation) {
    if (ajax_animation == null) {
      ajax_animation = true;
    }
    if (ajax_animation) {
      ajaxStart();
    }
    return $.post("groups/ajax/GetCabinetBar", {
      id_group: $scope.Group.id
    }, function(bars) {
      if (ajax_animation) {
        ajaxEnd();
      }
      $scope.cabinet_bars = bars;
      $scope.$apply();
      return rebindBlinking();
    }, "json");
  };
  $scope.getCabinet = function(id) {
    return _.findWhere($scope.all_cabinets, {
      id: parseInt(id)
    });
  };
  $scope.updateStudentBars = function() {
    return $.post("groups/ajax/GetStudentBars", {
      student_ids: $scope.Group.students,
      id_group: $scope.Group.id
    }, function(response) {
      console.log(response, 'students');
      $.each(response, function(id_student, bar) {
        return $scope.getStudent(id_student).bar = bar;
      });
      $scope.$apply();
      return rebindBlinking();
    }, "json");
  };
  $scope.updateGroup = function(data) {
    if ($scope.Group.id) {
      ajaxStart();
      return $.post("groups/ajax/updateGroup", {
        id_group: $scope.Group.id,
        data: data
      }, function() {
        return ajaxEnd();
      });
    }
  };
  $scope.to_students = true;
  $scope.to_representatives = false;
  $scope.to_teacher = false;
  $scope.$watch("[to_students, to_representatives]", function(newValue, oldValue) {
    if (!newValue[0] && !newValue[1]) {
      return $(".ajax-email-button").attr("disabled", "disabled");
    } else {
      return $(".ajax-email-button").removeAttr("disabled");
    }
  });
  $scope.teachersFilter = function(Teacher) {
    var ref;
    return (ref = parseInt($scope.Group.id_subject), indexOf.call(Teacher.subjects, ref) >= 0) || !$scope.Group.id_subject;
  };
  $scope.emptyDayFilter = function(day_and_time) {
    return _.filter(day_and_time, function(d) {
      return d.length !== 0;
    });
  };
  $scope.countSubjects = function(Contract) {
    return Object.keys(Contract.subjects).length;
  };
  $scope.reloadSmsNotificationStatuses = function() {
    return $.post("groups/ajax/ReloadSmsNotificationStatuses", {
      id: $scope.Group.id,
      students: $scope.Group.students
    }, function(response) {
      if (response) {
        $.each(response.sms_notification_statuses, function(id_student, id_status) {
          return $scope.getStudent(id_student).sms_notified = id_status;
        });
        return $scope.$apply();
      }
    }, "json");
  };
  $scope.reloadTests = function() {
    return $.post("groups/ajax/ReloadTests", {
      students: $scope.Group.students,
      id_subject: $scope.Group.id_subject,
      grade: $scope.Group.grade
    }, function(response) {
      $.each(response, function(id_student, Test) {
        return $scope.getStudent(id_student).Test = Test;
      });
      return $scope.$apply();
    }, "json");
  };
  $scope.smsNotify = function(Student, event) {
    $(event.target).html("отправка...").removeAttr("ng-click").removeClass("pointer").addClass("default");
    ajaxStart();
    return $.post("groups/ajax/smsNotify", {
      id_student: Student.id,
      id_subject: $scope.Group.id_subject,
      first_schedule: $scope.Group.first_schedule,
      id_group: $scope.Group.id,
      cabinet: $scope.FirstLesson.cabinet
    }, function(response) {
      ajaxEnd();
      Student.sms_notified = true;
      return $scope.$apply();
    });
  };
  $scope.addStudent = function(Student, event) {
    var el, ref;
    if (ref = Student.id, indexOf.call($scope.Group.students, ref) < 0) {
      el = $(event.target);
      el.hide();
      $("#student-adding-" + Student.id).show();
      ajaxStart();
      return $.post("groups/ajax/inGroup", {
        id_student: Student.id,
        id_group: $scope.Group.id,
        id_subject: $scope.Group.id_subject
      }, function(in_other_group) {
        ajaxEnd();
        if (!in_other_group) {
          console.log(el);
          el.show();
          $("#student-adding-" + Student.id).hide();
          $scope.Group.students.push(Student.id);
          $scope.TmpStudents = initIfNotSet($scope.TmpStudents);
          $scope.TmpStudents.push(Student);
          $scope.form_changed = true;
          $scope.$apply();
          $scope.bindGroupStudentStatusChange();
          bindDraggable();
          return justSave();
        } else {
          return $("#student-adding-" + Student.id).html("в другой группе");
        }
      }, "json");
    }
  };
  $scope.removeStudent = function(id_student, remove_without_saving) {
    $.each($scope.Group.students, function(index, data) {
      if (data === id_student) {
        $scope.Group.students.splice(index, 1);
        $timeout(function() {
          if (!remove_without_saving) {
            return justSave();
          }
        });
        $scope.form_changed = true;
        return $scope.$apply();
      }
    });
    return $.each($scope.TmpStudents, function(index, data) {
      if (data !== void 0 && data.id === id_student) {
        return $scope.TmpStudents.splice(index, 1);
      }
    });
  };
  $scope.getStudent = function(id_student) {
    return _.find($scope.TmpStudents, {
      id: parseInt(id_student)
    });
  };
  $scope.getTeacher = function(id_teacher) {
    return _.find($scope.Teachers, {
      id: parseInt(id_teacher)
    });
  };
  $scope.search = {
    grade: "",
    id_branch: "",
    id_subject: ""
  };
  $scope.clientsFilter = function(Student) {
    var ref;
    return (Student.Contract.grade === parseInt($scope.search.grade) || !$scope.search.grade) && ((ref = parseInt($scope.search.id_branch), indexOf.call(Student.branches, ref) >= 0) || !$scope.search.id_branch) && (Student.Contract.subjects && (parseInt($scope.search.id_subject) in Student.Contract.subjects || !$scope.search.id_subject));
  };
  $scope.deleteGroup = function(id_group) {
    return bootbox.confirm("Вы уверены, что хотите удалить группу №" + id_group + "?", function(result) {
      if (result === true) {
        ajaxStart();
        return $.post("groups/ajax/delete", {
          id_group: id_group
        }, function() {
          return redirect("groups");
        });
      }
    });
  };
  $scope.toggleReadyToStart = function() {
    var ready_to_start;
    ready_to_start = $scope.Group.ready_to_start ? 0 : 1;
    if ($scope.Group.id) {
      ajaxStart();
      return $.post("groups/ajax/toggleReadyToStart", {
        id: $scope.Group.id,
        ready_to_start: ready_to_start
      }, function() {
        ajaxEnd();
        $scope.Group.ready_to_start = ready_to_start;
        return $scope.$apply();
      });
    }
  };
  $scope.addGroupsPanel = function() {
    if (!$scope.Groups) {
      $scope.loadGroups();
    }
    $scope.add_groups_panel = !$scope.add_groups_panel;
    if (!$scope.search_groups.grade && $scope.Group.grade) {
      $scope.search_groups.grade = $scope.Group.grade;
    }
    if (!$scope.search_groups.year && $scope.Group.year) {
      $scope.search_groups.year = $scope.Group.year;
    }
    if (!$scope.search_groups.id_subject && $scope.Group.id_subject) {
      return $scope.search_groups.id_subject = $scope.Group.id_subject;
    }
  };
  $scope.subjectChange = function() {
    if (!$scope.Group.id) {
      return;
    }
    $scope.reloadSmsNotificationStatuses();
    $scope.reloadTests();
    $scope.updateGroup({
      id_subject: $scope.Group.id_subject
    });
    $scope.Group.id_teacher = 0;
    $scope.changeTeacher();
    return clearSelect();
  };
  $scope.$watch("Group.grade", function(newVal, oldVal) {
    if (!$scope.Group.id) {
      return;
    }
    if (newVal !== oldVal) {
      return $scope.updateGroup({
        grade: newVal
      });
    }
  });
  $scope.$watch("Group.teacher_price", function(newVal, oldVal) {
    if (!$scope.Group.id) {
      return;
    }
    if (newVal !== oldVal) {
      return $scope.updateGroup({
        teacher_price: newVal
      });
    }
  });
  $scope.$watch("Group.teacher_price_official", function(newVal, oldVal) {
    if (!$scope.Group.id) {
      return;
    }
    if (newVal !== oldVal) {
      return $scope.updateGroup({
        teacher_price_official: newVal
      });
    }
  });
  $scope.$watch("Group.level", function(newVal, oldVal) {
    if (!$scope.Group.id) {
      return;
    }
    if (newVal !== oldVal) {
      return $scope.updateGroup({
        level: newVal
      });
    }
  });
  $scope.$watch("Group.ended", function() {
    if (!$scope.Group.id) {
      return;
    }
    return $scope.updateGroup({
      ended: $scope.Group.ended
    }, $scope.updateTeacherBar(), $scope.updateCabinetBar(false), $scope.updateStudentBars());
  });
  $scope.loading_groups = false;
  $scope.loadGroups = function() {
    if (!$scope.Group.id) {
      return;
    }
    $scope.Groups = false;
    $scope.loading_groups = true;
    return $.post("groups/ajax/getGroups", {}, function(response) {
      $scope.loading_groups = false;
      $scope.Groups = response;
      $scope.$apply();
      return bindGroupsDroppable();
    }, "json");
  };
  angular.element(document).ready(function() {
    set_scope("Group");
    $scope.$apply();
    if ($scope.Group.Comments === false) {
      $scope.Group.Comments = [];
    }
    return frontendLoadingEnd();
  });
  $scope.form_changed = false;
  $scope.saving = false;
  $(document).ready(function() {
    emailMode(2);
    bindDraggable();
    $(".branch-cabinet").selectpicker();
    return set_scope("Group");
  });
  justSave = function(callback) {
    return $.post("groups/ajax/save", $scope.Group, callback);
  };
  $(".save-button").on("mousedown", function() {
    ajaxStart();
    $scope.saving = true;
    $scope.$apply();
    return $.post("groups/ajax/save", $scope.Group, function(response) {
      console.log(response);
      if ($scope.Group.id) {
        ajaxEnd();
        $scope.saving = false;
        $scope.form_changed = false;
        $scope.updateTeacherBar();
        $scope.updateCabinetBar(false);
        $scope.updateStudentBars();
        return $scope.$apply();
      } else {
        return redirect("groups/edit/" + response);
      }
    });
  });
  return $scope.getGroup = function(id_group) {
    var Group, i;
    return Group = ((function() {
      var j, len, ref, results;
      ref = $scope.Groups;
      results = [];
      for (j = 0, len = ref.length; j < len; j++) {
        i = ref[j];
        if (i.id === id_group) {
          results.push(i);
        }
      }
      return results;
    })())[0];
  };
}).controller("ListCtrl", function($scope, $timeout) {
  var bindDraggable2, filterBranches;
  $scope.updateCache = function() {
    ajaxStart();
    return $.post("groups/ajax/UpdateCacheAll", {}, function() {
      return redirect("groups");
    });
  };
  angular.merge = function(s1, s2) {
    return $.extend(true, s1, s2);
  };
  $scope.series = ["договоров"];
  $scope.datasetOverride = [
    {
      type: 'bar',
      backgroundColor: 'rgba(51,122,183,.75)',
      borderColor: 'rgba(51,122,183,.75)',
      borderWidth: 0
    }
  ];
  $scope.options = {
    scaleOverride: true,
    scaleIntegersOnly: true,
    scales: {
      yAxes: [
        {
          ticks: {
            min: 0,
            stepSize: 1
          }
        }
      ]
    }
  };
  $scope.createHelper = function() {
    lightBoxShow('contract-stats');
    $scope.create_helper_data = null;
    return $.post("ajax/GroupCreateHelper", {
      year: $scope.search.year,
      subjects: $scope.search.subjects,
      grade: $scope.search.grade
    }, function(response) {
      $scope.create_helper_data = response;
      $scope.labels = _.keys(response);
      $scope.data = [_.values(response)];
      return $scope.$apply();
    }, "json");
  };
  $scope.getMonthByNumber = function(n) {
    return moment().month(n - 1).format("MMMM");
  };
  $scope.getTeacher = function(id) {
    return _.find($scope.Teachers, {
      id: parseInt(id)
    });
  };
  $scope.order_reverse = false;
  $scope.orderByTime = function() {
    $scope.Groups.sort(function(a, b) {
      var day_index_1, day_index_2;
      day_index_1 = Object.keys(a.day_and_time)[0];
      day_index_2 = Object.keys(b.day_and_time)[0];
      if (day_index_1 === void 0) {
        day_index_1 = -1;
      }
      if (day_index_2 === void 0) {
        day_index_2 = -1;
      }
      if (day_index_1 > day_index_2) {
        return 1;
      } else if (day_index_2 > day_index_1) {
        return -1;
      } else {
        a.day_and_time[day_index_1] = initIfNotSet(a.day_and_time[day_index_1]);
        b.day_and_time[day_index_2] = initIfNotSet(b.day_and_time[day_index_2]);
        a.day_and_time[day_index_1] = objectToArray(a.day_and_time[day_index_1]);
        b.day_and_time[day_index_2] = objectToArray(b.day_and_time[day_index_2]);
        if (a.day_and_time[day_index_1] > b.day_and_time[day_index_2]) {
          return 1;
        } else {
          return -1;
        }
      }
    });
    if ($scope.order_reverse) {
      $scope.Groups.reverse();
    }
    return $scope.order_reverse = !$scope.order_reverse;
  };
  $scope.orderByStudentCount = function() {
    $scope.Groups.sort(function(a, b) {
      return a.students.length - b.students.length;
    });
    if ($scope.order_reverse) {
      $scope.Groups.reverse();
    }
    return $scope.order_reverse = !$scope.order_reverse;
  };
  $scope.orderByFirstLesson = function() {
    $scope.Groups.sort(function(a, b) {
      return a.first_schedule - b.first_schedule;
    });
    if ($scope.order_reverse) {
      $scope.Groups.reverse();
    }
    return $scope.order_reverse = !$scope.order_reverse;
  };
  $scope.orderByDaysBeforeExam = function() {
    $scope.Groups.sort(function(a, b) {
      return a.days_before_exam - b.days_before_exam;
    });
    if ($scope.order_reverse) {
      $scope.Groups.reverse();
    }
    return $scope.order_reverse = !$scope.order_reverse;
  };
  $scope.inDayAndTime2 = function(time, freetime) {
    if (freetime === void 0) {
      return false;
    }
    freetime = objectToArray(freetime);
    return $.inArray(time, freetime) >= 0;
  };
  $scope.search = {
    grade: "",
    id_branch: "",
    subjects: [],
    id_teacher: "",
    cabinet: 0
  };
  $scope.search2 = {
    grades: [],
    branches: [],
    id_subject: ""
  };
  $scope.groupsFilter2 = function(Group) {
    var ref, ref1;
    if (!Group.hasOwnProperty("grade")) {
      return true;
    }
    return ((ref = String(Group.grade), indexOf.call($scope.search2.grades, ref) >= 0) || $scope.search2.grades.length === 0) && ((ref1 = String(Group.branch), indexOf.call($scope.search2.branches, ref1) >= 0) || $scope.search2.branches.length === 0) && (Group.subject === parseInt($scope.search2.id_subject) || !$scope.search2.id_subject);
  };
  filterBranches = function(Student) {
    return _.intersection($scope.search2.branches.map(Number), Student.branches).length > 0;
  };
  $scope.studentsWithNoGroupFilter = function(Student) {
    var ref;
    return ((ref = String(Student.grade), indexOf.call($scope.search2.grades, ref) >= 0) || $scope.search2.grades.length === 0) && ($scope.search2.branches.length === 0 || filterBranches(Student)) && (Student.id_subject === parseInt($scope.search2.id_subject) || !$scope.search2.id_subject) && (Student.year === parseInt($scope.search2.year) || !$scope.search2.year);
  };
  $scope.dateToStart = function(date) {
    var D;
    if (date === null) {
      return "";
    }
    date = date.split(".");
    date = date.reverse();
    date = date.join("-");
    D = new Date(date);
    return moment().to(D);
  };
  $scope.$watchCollection("search2", function(newValue, oldValue) {
    $scope.Groups2 = newValue.branches.length > 0 ? $scope.GroupsFull : $scope.GroupsShort;
    if ($scope.Groups2 !== void 0 && $scope.Groups2.length > 0) {
      if ($scope.Groups2[$scope.Groups2.length - 1].hasOwnProperty("grade")) {
        $scope.Groups2.push({
          Students: []
        });
      }
    }
    return setTimeout(function() {
      return bindDraggable2();
    }, 100);
  });
  $scope.search_student = {
    grade: "",
    id_branch: "",
    id_subject: ""
  };
  $scope.clientsFilter = function(Student) {
    var ref;
    return (Student.Contract.grade === parseInt($scope.search_student.grade) || !$scope.search_student.grade) && ((ref = parseInt($scope.search_student.id_branch), indexOf.call(Student.branches, ref) >= 0) || !$scope.search_student.id_branch) && (Student.Contract.subjects && (parseInt($scope.search_student.id_subject) in Student.Contract.subjects || !$scope.search_student.id_subject));
  };
  $scope.getGroup = function(id_group) {
    var Group, i;
    return Group = ((function() {
      var j, len, ref, results;
      ref = $scope.Groups;
      results = [];
      for (j = 0, len = ref.length; j < len; j++) {
        i = ref[j];
        if (i.id === id_group) {
          results.push(i);
        }
      }
      return results;
    })())[0];
  };
  $scope.getSubject = function(subjects, id_subject) {
    return _.findWhere(subjects, {
      id_subject: id_subject
    });
  };
  bindDraggable2 = function() {
    $(".student-line").draggable({
      helper: 'clone',
      revert: 'invalid',
      start: function(event, ui) {
        $(this).css("visibility", "hidden");
        return $(ui.helper).addClass("tr-helper");
      },
      stop: function(event, ui) {
        return $(this).css("visibility", "visible");
      }
    });
    $(".group-list").droppable({
      tolerance: 'pointer',
      hoverClass: "request-status-drop-hover",
      drop: function(event, ui) {
        var Group, Student, group_branch_ids, id_group, id_student, student_group_index, table;
        id_group = $(this).data("id");
        id_student = $(ui.draggable).data("id");
        Group = $scope.getGroup(id_group);
        Student = _.find($scope.StudentsWithNoGroup, {
          id: parseInt(id_student)
        });
        group_branch_ids = _.pluck(Group.cabinets, 'id_branch');
        if (!_.intersection(group_branch_ids, Student.branches).length) {
          notifyError("Филиалы не соответствуют");
          return false;
        }
        if (Group.grade !== Student.grade) {
          notifyError("Класс не соответствует");
          return false;
        }
        if (Group.id_subject !== Student.id_subject) {
          notifyError("Предмет не соответствует");
          return false;
        }
        if (indexOf.call(Group.students, id_student) >= 0) {
          return notifySuccess("Ученик уже в группе");
        } else {
          ajaxStart();
          $.post("groups/ajax/AddStudentDnd", {
            id_group: id_group,
            id_student: id_student
          }, function() {
            ajaxEnd();
            Group.students.push(id_student);
            return $scope.$apply();
          });
          student_group_index = $(ui.draggable).data("group-index");
          ui.draggable.remove();
          table = $("#group-index-" + student_group_index);
          if (table.find("tr").length <= 1) {
            return table.remove();
          }
        }
      }
    });
    return $(".group-list-2").droppable({
      tolerance: 'pointer',
      hoverClass: "border-dashed-droppable-hover",
      activeClass: "border-dashed-droppable",
      drop: function(event, ui) {
        var Group, Groups, Student, group_index, in_group, student_group_index, table;
        group_index = $(this).data("index");
        student_group_index = $(ui.draggable).data("group-index");
        console.log(group_index, student_group_index);
        if (group_index === student_group_index) {
          return;
        }
        Student = $(ui.draggable).data("student");
        Groups = $scope.$eval("Groups2 | filter:groupsFilter2");
        Group = Groups[group_index];
        in_group = false;
        Group.Students = initIfNotSet(Group.Students);
        $.each(Group.Students, function(index, S) {
          if (S.id === Student.id) {
            return in_group = true;
          }
        });
        if (in_group) {
          notifySuccess("Ученик уже в группе");
        } else {
          Group.Students = objectToArray(Group.Students);
          Group.Students.push(Student);
          ui.draggable.remove();
          table = $("#group-index-" + student_group_index);
          testy = table;
          if (table.find("tr").length <= 1) {
            table.remove();
          }
        }
        $scope.$apply();
        return bindDraggable2();
      }
    });
  };
  $scope.students_picker = false;
  $scope.search2 = {
    grades: "",
    branches: "",
    id_subject: "",
    year: "",
    level: ""
  };
  $scope.loadStudentPicker = function() {
    $scope.students_picker = true;
    if (!$scope.search2.grades && $scope.search.grade) {
      $scope.search2.grades = [$scope.search.grade];
    }
    if (!$scope.search2.year && $scope.search.year) {
      $scope.search2.year = $scope.search.year;
    }
    if (!$scope.search2.branches && $scope.search.id_branch) {
      $scope.search2.branches = [$scope.search.id_branch];
    }
    if (!$scope.search2.id_subject && $scope.search.subjects && $scope.search.subjects.length) {
      $scope.search2.id_subject = $scope.search.subjects[0];
    }
    $("html, body").animate({
      scrollTop: $(document).height()
    }, 1000);
    $timeout(function() {
      $('#group-branch-filter2').selectpicker('refresh');
      $('#grades-select2').selectpicker('refresh');
      return $('#external-filter').selectpicker('refresh');
    });
    return $.post("ajax/StudentsWithNoGroup", {}, function(response) {
      $scope.StudentsWithNoGroup = response;
      $scope.$apply();
      return bindDraggable2();
    }, "json");
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
  $scope.branchCabinetFilter = function() {
    var ids;
    ids = $scope.search.branch_cabinet.split('-');
    $scope.search.id_branch = ids[0];
    $scope.search.cabinet = ids[1];
    $scope.$apply();
    return $scope.filter();
  };
  $scope.filter = function() {
    $.cookie("groups", JSON.stringify($scope.search), {
      expires: 365,
      path: '/'
    });
    $scope.current_page = 1;
    return $scope.getByPage($scope.current_page);
  };
  $scope.pageChanged = function() {
    if ($scope.current_page > 1) {
      window.history.pushState({}, '', 'groups/?page=' + $scope.current_page);
    }
    return $scope.getByPage($scope.current_page);
  };
  $scope.getByPage = function(page) {
    $scope.Groups = void 0;
    frontendLoadingStart();
    return $.post("groups/ajax/get", {
      page: page
    }, function(response) {
      frontendLoadingEnd();
      $scope.Groups = response.data;
      $scope.teacher_ids = response.teacher_ids;
      $scope.counts = response.counts;
      $scope.$apply();
      if ($scope.students_picker) {
        bindDraggable2();
      }
      return $scope.refreshCounts();
    }, "json");
  };
  $scope.teachersFilter2 = function(Teacher) {
    var ref;
    if ($scope.teacher_ids === void 0) {
      return true;
    }
    if ((ref = Teacher.id, indexOf.call($scope.teacher_ids, ref) >= 0) || Teacher.id === parseInt($scope.search.id_teacher)) {
      return true;
    }
    return false;
  };
  $(document).ready(function() {
    var error;
    try {
      if ($("#subjects-select").length) {
        $("#subjects-select").selectpicker({
          noneSelectedText: "предметы",
          multipleSeparator: '+'
        });
      }
      if ($(".search-grades").length) {
        $(".search-grades").selectpicker({
          noneSelectedText: "классы",
          multipleSeparator: ', '
        });
      }
      if ($("#time-select").length) {
        $("#time-select").selectpicker({
          noneSelectedText: "время занятия"
        });
      }
      $("#group-branch-filter2").selectpicker({
        noneSelectedText: "филиалы"
      });
      return $("#grades-select2").selectpicker({
        noneSelectedText: "класс",
        multipleSeparator: ", "
      });
    } catch (error1) {
      error = error1;
    }
  });
  return angular.element(document).ready(function() {
    set_scope("Group");
    $scope.search = $.cookie("groups") ? JSON.parse($.cookie("groups")) : {};
    $scope.current_page = $scope.currentPage;
    $scope.pageChanged();
    $(".single-select").selectpicker();
    setTimeout(function() {
      return $scope.$apply();
    }, 25);
    return frontendLoadingEnd();
  });
}).controller("StudentListCtrl", function($scope, GroupService) {
  return bindArguments($scope, arguments);
}).controller("TeacherListCtrl", function($scope, GroupService) {
  return bindArguments($scope, arguments);
});
