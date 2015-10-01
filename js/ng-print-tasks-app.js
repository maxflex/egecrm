// Generated by CoffeeScript 1.9.3
angular.module("Print", []).controller("UsersCtrl", function($scope) {
  $scope.changeStatus = function(Task) {
    if (Task.id_status === 2) {
      Task.id_status = 0;
    } else {
      Task.id_status++;
    }
    return $.post("print/ajax/ChangeStatus", {
      id_status: Task.id_status,
      id_task: Task.id
    });
  };
  return $scope.formatDate = function(date) {
    return moment(date).format("DD MMMM");
  };
}).controller("TeachersCtrl", function($scope) {
  $scope.PrintTask = {
    files: [],
    comment: "",
    id_group: "",
    id_lesson: ""
  };
  $scope.formatDate = function(date) {
    return moment(date).format("DD MMMM");
  };
  $scope.changeGroup = function() {
    var id_group;
    id_group = parseInt($scope.PrintTask.id_group);
    $scope.PrintTask.id_lesson = "";
    if (!id_group) {
      $scope.GroupLessons = [];
      return false;
    } else {
      return $scope.GroupLessons = _.findWhere($scope.Groups, {
        id: parseInt($scope.PrintTask.id_group)
      }).FutureSchedule;
    }
  };
  $scope.bindFileUpload = function() {
    return $('#fileupload').fileupload({
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
        if (response.result !== "ERROR") {
          $scope.PrintTask.files.push(response.result);
          return $scope.$apply();
        } else {
          return notifyError("Ошибка загрузки");
        }
      },
      fail: function(e, data) {
        return $.each(data.messages, function(index, error) {
          return notifyError(error);
        });
      }
    });
  };
  $scope.addPrintTask = function() {
    if (!$scope.PrintTask.id_group) {
      notifyError("Укажите группу");
      $("#id-group").addClass("has-error").focus();
      return;
    } else {
      $("#id-group").removeClass("has-error");
    }
    if (!$scope.PrintTask.id_lesson) {
      notifyError("Укажите занятие");
      $("#id-lesson").addClass("has-error").focus();
      return;
    } else {
      $("#id-lesson").removeClass("has-error");
    }
    if (!$scope.PrintTask.files.length) {
      notifyError("Добавьте файлы для печати");
      return;
    }
    ajaxStart();
    $scope.adding = true;
    return $.post("print/ajax/addTask", $scope.PrintTask, function(response) {
      return redirect("print");
    });
  };
  return $(document).ready(function() {
    var enjoyhint_instance, enjoyhint_script_steps;
    set_scope('Print');
    $scope.bindFileUpload();
    if (localStorage.getItem('print_hint_shown') === null && $scope.for_teachers && (!$scope.PrintTasks || !$scope.PrintTasks.length)) {
      enjoyhint_instance = new EnjoyHint({});
      enjoyhint_script_steps = [
        {
          selector: '#add-task',
          event: 'click',
          description: 'Чтобы добавить новое задание на печать,<br>' + 'нажмите «добавить задание» в правом верхнем углу',
          right: 15,
          left: -15,
          skipButton: {
            className: 'btn btn-success btn-lg pull-right',
            text: 'ПОНЯТНО'
          }
        }
      ];
      enjoyhint_instance.set(enjoyhint_script_steps);
      enjoyhint_instance.run();
      return localStorage.setItem('print_hint_shown', true);
    }
  });
});
