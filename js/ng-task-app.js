var app;

app = angular.module("Task", ['ngSanitize']).filter('reverse', function() {
  return function(items) {
    if (items) {
      return items.slice().reverse();
    }
  };
}).filter('unsafe', function($sce) {
  return $sce.trustAsHtml;
}).controller("ListCtrl", function($scope, $timeout, UserService) {
  bindArguments($scope, arguments);
  $scope.editing_tasks = [];
  $scope.filterResponsible = function() {
    var params;
    params = '';
    if ($scope.id_user_responsible !== '') {
      params = "?user=" + $scope.id_user_responsible;
    }
    return redirect(window.location.pathname + params);
  };
  $scope.editTask = function(Task) {
    $scope.editing_task = Task.id;
    $scope.old_html = Task.html;
    if (typeof this.e === "object") {
      $scope.e.destroy();
    }
    $scope.e = CKEDITOR.replace("task-" + Task.id, {
      language: 'ru',
      height: 500,
      title: "testy",
      extraPlugins: 'pastebase64,panel,button,panelbutton,colorbutton'
    });
    $scope.e.setData(Task.html);
    $scope.e.on('contentDom', function() {
      return $scope.e.document.on('keydown', function(event) {
        event = event.data.$;
        if (event.which === 13 && (event.ctrlKey || event.metaKey) || (event.which === 19)) {
          Task.html = $scope.e.getData();
          $scope.e.destroy();
          delete $scope.e;
          $scope.editing_task = void 0;
          $scope.$apply();
          $scope.saveTask(Task);
        }
        if (event.which === 27) {
          Task.html += " ";
          $scope.e.destroy();
          delete $scope.e;
          $scope.editing_task = void 0;
          return $scope.$apply();
        }
      });
    });
    return $scope.e.on('instanceReady', function(event) {
      $scope.e.focus().select;
      return $scope.e.execCommand('selectAll');
    });
  };
  $scope.editingTask = function(Task) {
    return Task.id === $scope.editing_task;
  };
  $scope.toggleTaskStatus = function(Task) {
    var Task_copy, task_statuses;
    Task_copy = {
      id: Task.id,
      id_status: Task.id_status
    };
    task_statuses = Object.keys($scope.task_statuses).map(Number);
    Task_copy.id_status = task_statuses[task_statuses.indexOf(Task_copy.id_status) + 1];
    if (!Task_copy.id_status) {
      Task_copy.id_status = 1;
    }
    return $scope.saveTask(Task_copy).then(function(response) {
      console.log(response);
      if (response) {
        Task.id_status = Task_copy.id_status;
        return $scope.$apply();
      }
    });
  };
  $scope.deleteTask = function(Task) {
    Task["delete"] = 1;
    return $scope.saveTask({
      id: Task.id,
      "delete": 1
    });
  };
  $scope.addTask = function() {
    return $.post("tasks/ajax/add", {}, function(id_task) {
      var Task;
      Task = {
        id: id_task,
        id_status: 1,
        id_user_responsible: 69,
        html: "Текст задачи..."
      };
      $scope.Tasks.unshift(Task);
      $scope.$apply();
      $scope.editTask(Task);
      return setTimeout(function() {
        $scope.bindFileUpload(Task);
        return $('.selectpicker').selectpicker('refresh');
      }, 100);
    });
  };
  $scope.bindFileUpload = function(Task) {
    return $('#fileupload' + Task.id).fileupload({
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
          Task.files = initIfNotSet(Task.files);
          Task.files.push(response.result);
          $scope.saveTask(Task);
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
  $scope.deleteTaskFile = function(Task, id) {
    Task.files.splice(id, 1);
    return $scope.saveTask(Task);
  };
  $scope.saveTask = function(Task) {
    return $.post("tasks/ajax/save", {
      Task: Task
    });
  };
  angular.element(document).ready(function() {
    var channel, pusher;
    $.each($scope.Tasks, function(i, Task) {
      return $scope.bindFileUpload(Task);
    });
    pusher = new Pusher('a9e10be653547b7106c0', {
      encrypted: true
    });
    channel = pusher.subscribe('tasks');
    return channel.bind('reload', function(data) {
      if (parseInt(data.user_id) !== parseInt($scope.user.id)) {
        return $('.reload-badge').show().addClass('animated fadeIn');
      }
    });
  });
  return $(document).ready(function() {
    return set_scope('Task');
  });
});
