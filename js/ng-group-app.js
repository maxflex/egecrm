// Generated by CoffeeScript 1.9.3
var indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

angular.module("Group", []).filter('to_trusted', [
  '$sce', function($sce) {
    return function(text) {
      return $sce.trustAsHtml(text);
    };
  }
]).filter('range', function() {
  return function(input, total) {
    var i, j, ref;
    total = parseInt(total);
    for (i = j = 0, ref = total; j < ref; i = j += 1) {
      input.push(i);
    }
    return input;
  };
}).controller("ScheduleCtrl", function($scope) {
  $scope.schedulde_loaded = false;
  $scope.getLine1 = function(Schedule) {
    return moment(Schedule.date).format("D MMMM YYYY г.");
  };
  $scope.setTimeFromGroup = function(Group) {
    $.each($scope.Group.Schedule, function(i, v) {
      if (!v.time) {
        return v.time = Group.start;
      }
    });
    $.post("groups/ajax/TimeFromGroup", {
      id_group: Group.id,
      time: Group.start
    });
    return $scope.$apply();
  };
  $scope.setTime = function(Schedule, event) {
    $(event.target).hide();
    $(event.target).parent().children("input").show().on("changeTime, blur", function(e) {
      var time;
      time = $(this).val();
      if (time) {
        Schedule.time = time;
        $.post("groups/ajax/AddScheduleTime", {
          time: time,
          date: Schedule.date,
          id_group: $scope.Group.id
        });
        $scope.$apply();
      }
      return $(this).hide().parent().children("span").html(time ? time : "не установлено").show();
    }).focus();
    return false;
  };
  $scope.getInitParams = function(el) {
    var current_date, month, year;
    month = parseInt($(el).attr("month"));
    year = month >= 8 ? parseInt(moment().format("YYYY")) : moment().add(1, "years").format("YYYY");
    current_date = new Date(year + "-" + month + "-01");
    return {
      language: 'ru',
      startDate: current_date,
      endDate: moment(current_date).endOf("month").toDate(),
      multidate: true,
      beforeShowDay: function(d) {
        var ref;
        if (ref = moment(d).format("YYYY-MM-DD"), indexOf.call($scope.vocation_dates, ref) >= 0) {
          return 'vocation';
        }
      }
    };
  };
  $scope.monthName = function(month) {
    return moment().month(month - 1).format("MMMM");
  };
  $scope.dateChange = function(e) {
    var d, t;
    if (!$scope.schedule_loaded) {
      return;
    }
    d = moment(clicked_date).format("YYYY-MM-DD");
    $scope.Group.Schedule = initIfNotSet($scope.Group.Schedule);
    t = $scope.Group.Schedule.filter(function(schedule) {
      return schedule.date === d;
    });
    if (t.length === 0) {
      $scope.Group.Schedule.push({
        date: d
      });
      $.post("groups/ajax/AddScheduleDate", {
        date: d,
        id_group: $scope.Group.id
      });
    } else {
      $.each($scope.Group.Schedule, function(i, v) {
        if (v !== void 0) {
          if (v.date === d) {
            return $scope.Group.Schedule.splice(i, 1);
          }
        }
      });
      $.post("groups/ajax/DeleteScheduleDate", {
        date: d,
        id_group: $scope.Group.id
      });
    }
    return $scope.$apply();
  };
  return angular.element(document).ready(function() {
    var init_dates, j, len, ref, schedule_date;
    set_scope('Group');
    init_dates = [];
    ref = $scope.Group.Schedule;
    for (j = 0, len = ref.length; j < len; j++) {
      schedule_date = ref[j];
      init_dates.push(new Date(schedule_date.date));
    }
    console.log(init_dates);
    $(".calendar-month").each(function() {
      var d, k, len1, m, month_number;
      $(this).datepicker($scope.getInitParams(this)).on("changeDate", $scope.dateChange);
      m = $(this).attr("month");
      for (k = 0, len1 = init_dates.length; k < len1; k++) {
        d = init_dates[k];
        month_number = moment(d).format("M");
        if (month_number === m) {
          $(this).datepicker("_setDate", d);
        }
      }
      return setTimeout(function() {
        $scope.schedule_loaded = true;
        return $scope.$apply();
      }, 500);
    });
    $(".table-condensed").first().children("thead").css("display", "table-caption");
    return $(".table-condensed").eq(15).children("tbody").children("tr").first().remove();
  });
}).controller("EditCtrl", function($scope) {
  var bindDraggable, initDayAndTime, initFreetime, justSave;
  $scope.weekdays = [
    {
      "short": "ПН",
      "full": "Понедельник",
      "schedule": ["", "", "16:15", "18:40"]
    }, {
      "short": "ВТ",
      "full": "Вторник",
      "schedule": ["", "", "16:15", "18:40"]
    }, {
      "short": "СР",
      "full": "Среда",
      "schedule": ["", "", "16:15", "18:40"]
    }, {
      "short": "ЧТ",
      "full": "Четверг",
      "schedule": ["", "", "16:15", "18:40"]
    }, {
      "short": "ПТ",
      "full": "Пятница",
      "schedule": ["", "", "16:15", "18:40"]
    }, {
      "short": "СБ",
      "full": "Суббота",
      "schedule": ["11:00", "13:30", "16:00", "18:30"]
    }, {
      "short": "ВС",
      "full": "Воскресенье",
      "schedule": ["11:00", "13:30", "16:00", "18:30"]
    }
  ];
  bindDraggable = function() {
    $(".student-line").draggable({
      helper: 'clone',
      revertDuration: 0,
      revert: function(valid) {
        var id_student;
        if (!valid) {
          id_student = $(this).data("id");
          $scope.removeStudent(id_student);
          return this.remove();
        }
      },
      start: function(event, ui) {
        $(this).css("visibility", "hidden");
        return $(ui.helper).addClass("tr-helper");
      },
      stop: function(event, ui) {
        return $(this).css("visibility", "visible");
      }
    });
    return $(".table-students").droppable({
      tolerance: 'pointer'
    });
  };
  $scope.dayAndTime = function() {
    return lightBoxShow("freetime");
  };
  $scope.dayAndTimeClick = function(index, n) {
    index++;
    $scope.form_changed = true;
    $scope.Group.day_and_time[index] = initIfNotSet($scope.Group.day_and_time[index]);
    if ($scope.Group.day_and_time[index][n] !== true) {
      return $scope.Group.day_and_time[index][n] = "";
    } else {
      $scope.Group.day_and_time[index][n] = $scope.weekdays[index - 1].schedule[n];
      return console.log($scope.weekdays[index - 1].schedule[n]);
    }
  };
  $scope.saveDayAndTime = function() {
    lightBoxHide();
    return $(".save-button").mousedown();
  };
  initDayAndTime = function(day) {
    $scope.Group.day_and_time = initIfNotSet($scope.Group.day_and_time);
    return $scope.Group.day_and_time[day] = initIfNotSet($scope.Group.day_and_time[day]);
  };
  $scope.inDayAndTime = function(day, value) {
    initDayAndTime(day);
    return $.inArray(value, objectToArray($scope.Group.day_and_time[day])) >= 0;
  };
  $scope.inDayAndTime2 = function(time, freetime) {
    if (freetime === void 0) {
      return false;
    }
    freetime = objectToArray(freetime);
    return $.inArray(time, freetime) >= 0;
  };
  $scope.inCabinetFreetime = function(time, freetime) {
    if (freetime === void 0) {
      return false;
    }
    freetime = objectToArray(freetime);
    return $.inArray(time, freetime) >= 0;
  };
  $scope.changeCabinet = function() {
    $("#group-cabinet").attr("disabled", "disabled");
    ajaxStart();
    return $.post("groups/ajax/GetCabinetFreetime", {
      id_group: $scope.Group.id,
      cabinet: $scope.Group.cabinet
    }, function(freetime) {
      ajaxEnd();
      $("#group-cabinet").removeAttr("disabled");
      $scope.cabinet_freetime = freetime;
      return $scope.$apply();
    }, "json");
  };
  $scope.changeTeacher = function() {
    if ($scope.Group.id_teacher === "0") {
      return;
    }
    ajaxStart();
    return $.post("groups/ajax/GetTeacherFreetime", {
      id_branch: $scope.Group.id_branch,
      id_teacher: $scope.Group.id_teacher
    }, function(freetime) {
      ajaxEnd();
      $scope.teacher_freetime = freetime;
      return $scope.$apply();
    }, "json");
  };
  $scope.selectAllWorking = function(id_branch) {
    $.each($scope.weekdays, function(index, weekday) {
      if (index > 4) {
        return;
      }
      if ($scope.freetime_selected_all_working) {
        $scope.Group.day_and_time[index + 1][2] = "";
        return $scope.Group.day_and_time[index + 1][3] = "";
      } else {
        $scope.Group.day_and_time[index + 1][2] = weekday.schedule[2];
        return $scope.Group.day_and_time[index + 1][3] = weekday.schedule[3];
      }
    });
    return $scope.freetime_selected_all_working = !$scope.freetime_selected_all_working;
  };
  $scope.selectAllWeek = function() {
    $.each($scope.weekdays, function(index, weekday) {
      if ($scope.freetime_selected_all_week) {
        $scope.Group.day_and_time[index + 1][0] = "";
        $scope.Group.day_and_time[index + 1][1] = "";
        $scope.Group.day_and_time[index + 1][2] = "";
        return $scope.Group.day_and_time[index + 1][3] = "";
      } else {
        $scope.Group.day_and_time[index + 1][0] = weekday.schedule[0];
        $scope.Group.day_and_time[index + 1][1] = weekday.schedule[1];
        $scope.Group.day_and_time[index + 1][2] = weekday.schedule[2];
        return $scope.Group.day_and_time[index + 1][3] = weekday.schedule[3];
      }
    });
    return $scope.freetime_selected_all_week = !$scope.freetime_selected_all_week;
  };
  $scope.selectAllIndex = function(index) {
    $scope.freetime_selected_all_index = initIfNotSet($scope.freetime_selected_all_index);
    $.each($scope.weekdays, function(i, weekday) {
      if ($scope.freetime_selected_all_index[index]) {
        return $scope.Group.day_and_time[i + 1][index] = "";
      } else {
        return $scope.Group.day_and_time[i + 1][index] = weekday.schedule[index];
      }
    });
    return $scope.freetime_selected_all_index[index] = !$scope.freetime_selected_all_index[index];
  };
  $scope.to_students = true;
  $scope.to_representatives = false;
  $scope.$watch("[to_students, to_representatives]", function(newValue, oldValue) {
    if (!newValue[0] && !newValue[1]) {
      return $(".ajax-email-button").attr("disabled", "disabled");
    } else {
      return $(".ajax-email-button").removeAttr("disabled");
    }
  });
  $scope.emailDialog = function() {
    var html;
    $("#email-history").html("<center class='text-gray'>загрузка истории сообщений...</center>");
    html = "";
    $.post("ajax/emailHistory", {
      place: "GROUP",
      id_place: $scope.Group.id
    }, function(response) {
      console.log(response);
      if (response !== false) {
        $.each(response, function(i, v) {
          var files_html;
          files_html = "";
          $.each(v.files, function(i, file) {
            return files_html += '<div class="sms-coordinates"><a target="_blank" href="files/email/' + file.name + '" class="link-reverse small">' + file.uploaded_name + '</a><span> (' + file.size + ')</span></div>';
          });
          return html += '<div class="clear-sms">		<div class="from-them">		' + v.message + ' 		<div class="sms-coordinates">' + v.coordinates + '</div>' + files_html + '</div>						</div>';
        });
        return $("#email-history").html(html);
      } else {
        return $("#email-history").html("");
      }
    }, "json");
    $("#email-address").text(("Группа " + $scope.Group.id + " ") + ($scope.Group.is_special ? "(спецгруппа)" : ""));
    return lightBoxShow('email');
  };
  initFreetime = function(freetime, day) {
    freetime = initIfNotSet(freetime);
    freetime[$scope.Group.id_branch] = initIfNotSet(freetime[$scope.Group.id_branch]);
    return freetime[$scope.Group.id_branch][day] = initIfNotSet(freetime[$scope.Group.id_branch][day]);
  };
  $scope.inFreetime = function(time, Student, day) {
    var freetime;
    if (Student.freetime[$scope.Group.id_branch] === void 0) {
      if (Student.freetime[0] === void 0) {
        return false;
      }
      freetime = Student.freetime[0];
    } else {
      freetime = Student.freetime[$scope.Group.id_branch];
    }
    return $.inArray(time, freetime[day]) >= 0;
  };
  $scope.inRedFreetime = function(time, Student, day) {
    if (Student.freetime_red === null) {
      return false;
    }
    return $.inArray(time, Student.freetime_red[day]) >= 0;
  };
  $scope.setStudentStatus = function(Student, event) {
    $(event.target).hide();
    $(".student-status-select-" + Student.id).show(0, function() {
      $(this).simulate('mousedown');
      return $("option[value^='?']").remove();
    });
    return false;
  };
  $scope.teachersFilter = function(Teacher) {
    var ref, ref1;
    return ((ref = parseInt($scope.Group.id_branch), indexOf.call(Teacher.branches, ref) >= 0) || !$scope.Group.id_branch) && ((ref1 = parseInt($scope.Group.id_subject), indexOf.call(Teacher.subjects, ref1) >= 0) || !$scope.Group.id_subject);
  };
  $scope.countSubjects = function(Contract) {
    return Object.keys(Contract.subjects).length;
  };
  $(document).on("mouseup", function() {
    $("select[class^='student-status-select']").hide();
    return $(".s-s-s").show();
  });
  $scope.bindGroupStudentStatusChange = function() {
    return $("select[class^='student-status-select']").on("input", function() {
      var id_student;
      $(this).hide();
      id_student = $(this).data("id");
      $(".student-status-span-" + id_student).show();
      return $scope.Group.student_statuses[id_student] = $(this).val();
    });
  };
  $scope.addStudent = function(id_student, event) {
    var el;
    if (indexOf.call($scope.Group.students, id_student) < 0) {
      el = $(event.target);
      el.hide();
      $("#student-adding-" + id_student).show();
      return $.post("groups/ajax/inGroup", {
        id_student: id_student,
        id_group: $scope.Group.id,
        id_subject: $scope.Group.id_subject
      }, function(in_other_group) {
        if (!in_other_group) {
          console.log(el);
          el.show();
          $("#student-adding-" + id_student).hide();
          $scope.Group.students.push(id_student);
          $scope.TmpStudents = initIfNotSet($scope.TmpStudents);
          $scope.TmpStudents.push($scope.getStudent(id_student));
          $scope.form_changed = true;
          $scope.$apply();
          $scope.bindGroupStudentStatusChange();
          bindDraggable();
          return justSave();
        } else {
          return $("#student-adding-" + id_student).html("в другой группе");
        }
      }, "json");
    }
  };
  $scope.removeStudent = function(id_student) {
    $.each($scope.Group.students, function(index, data) {
      if (data === id_student) {
        $scope.Group.students.splice(index, 1);
        justSave();
        $scope.form_changed = true;
        return $scope.$apply();
      }
    });
    return $.each($scope.TmpStudents, function(index, data) {
      if (data.id === id_student) {
        return $scope.TmpStudents.splice(index, 1);
      }
    });
  };
  $scope.studentAdded = function(id_student) {
    return indexOf.call($scope.Group.students, id_student) >= 0;
  };
  $scope.getStudent = function(id_student) {
    var Student, i;
    return Student = ((function() {
      var j, len, ref, results;
      ref = $scope.Students;
      results = [];
      for (j = 0, len = ref.length; j < len; j++) {
        i = ref[j];
        if (i.id === id_student) {
          results.push(i);
        }
      }
      return results;
    })())[0];
  };
  $scope.getTeacher = function(id_teacher) {
    var Teacher, i;
    id_teacher = parseInt(id_teacher);
    return Teacher = ((function() {
      var j, len, ref, results;
      ref = $scope.Teachers;
      results = [];
      for (j = 0, len = ref.length; j < len; j++) {
        i = ref[j];
        if (i.id === id_teacher) {
          results.push(i);
        }
      }
      return results;
    })())[0];
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
  $scope.changeBranch = function() {
    $("#group-cabinet").attr("disabled", "disabled");
    ajaxStart();
    return $.post("groups/ajax/getCabinet", {
      id_branch: $scope.Group.id_branch
    }, function(cabinets) {
      ajaxEnd();
      $scope.Cabinets = cabinets;
      if (cabinets !== void 0 && cabinets.length) {
        $scope.Group.cabinet = cabinets[0].id;
      }
      if (cabinets.length !== 1) {
        $("#group-cabinet").removeAttr("disabled");
      }
      $scope.$apply();
      return clearSelect();
    }, "json");
  };
  $scope.addClientsPanel = function() {
    if (!$scope.Students) {
      $scope.loadStudents();
    }
    $scope.add_clients_panel = !$scope.add_clients_panel;
    if (!$scope.search.grade && $scope.Group.grade) {
      $scope.search.grade = $scope.Group.grade;
    }
    if (!$scope.search.id_subject && $scope.Group.id_subject) {
      $scope.search.id_subject = $scope.Group.id_subject;
    }
    if (!$scope.search.id_branch && $scope.Group.id_branch) {
      $scope.search.id_branch = $scope.Group.id_branch;
      $scope.$apply();
      return $("#group-branch-filter").selectpicker('render');
    }
  };
  $scope.subjectChange = function() {
    $scope.loadStudents();
    $scope.Group.id_teacher = 0;
    return clearSelect();
  };
  $scope.loading_students = false;
  $scope.loadStudents = function() {
    if (!$scope.Group.id) {
      return;
    }
    $scope.Students = false;
    $scope.loading_students = true;
    return $.post("groups/ajax/getStudents", {
      id_group: $scope.Group.id,
      id_subject: $scope.Group.id_subject
    }, function(response) {
      $scope.loading_students = false;
      $scope.Students = response;
      $.each($scope.Group.student_statuses, function(id_student, id_status) {
        var Student;
        id_student = parseInt(id_student);
        Student = $scope.getStudent(id_student);
        if (Student !== void 0) {
          Student.id_status = id_status;
          return $scope.$apply();
        }
      });
      return $scope.$apply();
    }, "json");
  };
  angular.element(document).ready(function() {
    set_scope("Group");
    $scope.bindGroupStudentStatusChange();
    if ($scope.Group.Comments === false) {
      $scope.Group.Comments = [];
    }
    return frontendLoadingEnd();
  });
  $(document).ready(function() {
    emailMode(2);
    bindDraggable();
    return $("#group-edit").on('keyup change', 'input, select, textarea', function() {
      $scope.form_changed = true;
      return $scope.$apply();
    });
  });
  justSave = function() {
    return $.post("groups/ajax/save", $scope.Group);
  };
  return $(".save-button").on("mousedown", function() {
    ajaxStart();
    $scope.saving = true;
    $scope.$apply();
    return $.post("groups/ajax/save", $scope.Group, function(response) {
      if ($scope.Group.id) {
        ajaxEnd();
        $scope.saving = false;
        $scope.form_changed = false;
        return $scope.$apply();
      } else {
        return redirect("groups/edit/" + response);
      }
    });
  });
}).controller("ListCtrl", function($scope) {
  var bindDraggable, bindDraggable2;
  $scope.weekdays = [
    {
      "short": "ПН",
      "full": "Понедельник",
      "schedule": ["", "", "16:15", "18:40"]
    }, {
      "short": "ВТ",
      "full": "Вторник",
      "schedule": ["", "", "16:15", "18:40"]
    }, {
      "short": "СР",
      "full": "Среда",
      "schedule": ["", "", "16:15", "18:40"]
    }, {
      "short": "ЧТ",
      "full": "Четверг",
      "schedule": ["", "", "16:15", "18:40"]
    }, {
      "short": "ПТ",
      "full": "Пятница",
      "schedule": ["", "", "16:15", "18:40"]
    }, {
      "short": "СБ",
      "full": "Суббота",
      "schedule": ["11:00", "13:30", "16:00", "18:30"]
    }, {
      "short": "ВС",
      "full": "Воскресенье",
      "schedule": ["11:00", "13:30", "16:00", "18:30"]
    }
  ];
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
    id_subject: ""
  };
  $scope.search2 = {
    grades: [],
    branches: [],
    id_subject: ""
  };
  $scope.groupsFilter = function(Group) {
    return (Group.grade === parseInt($scope.search.grade) || !$scope.search.grade) && (parseInt($scope.search.id_branch) === Group.id_branch || !$scope.search.id_branch) && (parseInt($scope.search.id_subject) === Group.id_subject || !$scope.search.id_subject);
  };
  $scope.groupsFilter2 = function(Group) {
    var ref, ref1;
    if (!Group.hasOwnProperty("grade")) {
      return true;
    }
    return ((ref = String(Group.grade), indexOf.call($scope.search2.grades, ref) >= 0) || $scope.search2.grades.length === 0) && ((ref1 = String(Group.branch), indexOf.call($scope.search2.branches, ref1) >= 0) || $scope.search2.branches.length === 0) && (Group.subject === parseInt($scope.search2.id_subject) || !$scope.search2.id_subject);
  };
  $scope.dateToStart = function(date) {
    var D;
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
  bindDraggable = function() {
    $(".request-main-list").draggable({
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
    return $(".group-list").droppable({
      tolerance: 'pointer',
      hoverClass: "request-status-drop-hover",
      drop: function(event, ui) {
        var Group, id_group, id_student;
        id_group = $(this).data("id");
        id_student = $(ui.draggable).data("id");
        Group = $scope.getGroup(id_group);
        if (indexOf.call(Group.students, id_student) >= 0) {
          return notifySuccess("Ученик уже в группе");
        } else {
          $.post("groups/ajax/AddStudentDnd", {
            id_group: id_group,
            id_student: id_student
          });
          Group.students.push(id_student);
          return $scope.$apply();
        }
      }
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
        var Group, id_group, id_student;
        id_group = $(this).data("id");
        id_student = $(ui.draggable).data("id");
        Group = $scope.getGroup(id_group);
        if (indexOf.call(Group.students, id_student) >= 0) {
          return notifySuccess("Ученик уже в группе");
        } else {
          $.post("groups/ajax/AddStudentDnd", {
            id_group: id_group,
            id_student: id_student
          });
          Group.students.push(id_student);
          return $scope.$apply();
        }
      }
    });
    return $(".group-list-2").droppable({
      tolerance: 'pointer',
      hoverClass: "border-dashed-droppable-hover",
      activeClass: "border-dashed-droppable",
      drop: function(event, ui) {
        var Group, Groups, Student, group_index, in_group, student_group_index, table, testy;
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
  $scope.changeMode = function() {
    $scope.change_mode = parseInt($scope.change_mode);
    switch ($scope.change_mode) {
      case 2:
        redirect("groups");
        return ajaxStart();
      default:
        redirect("groups/?mode=students");
        return ajaxStart();
    }
  };
  $(document).ready(function() {
    if ($scope.mode === 2) {
      $("#group-branch-filter2").selectpicker({
        noneSelectedText: "филиалы"
      });
      return $("#grades-select2").selectpicker({
        noneSelectedText: "класс",
        multipleSeparator: ", "
      });
    }
  });
  return angular.element(document).ready(function() {
    set_scope("Group");
    switch ($scope.mode) {
      case 1:
        $.post("settings/ajax/getStudents", {}, function(response) {
          $scope.Students = response;
          $scope.$apply();
          return bindDraggable();
        }, "json");
        return $scope.$watchCollection("search_student", function(newValue, oldValue) {
          console.log(newValue);
          return setTimeout(function() {
            return bindDraggable();
          }, 100);
        });
      case 2:
        return $.post("settings/ajax/StudentsWithNoGroup", {}, function(response) {
          $scope.Groups2 = response.GroupsShort;
          $scope.Groups2.push({
            Students: []
          });
          $scope.GroupsShort = response.GroupsShort;
          $scope.GroupsFull = response.GroupsFull;
          $scope.$apply();
          return bindDraggable2();
        }, "json");
    }
  });
});
