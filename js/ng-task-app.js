var app;

app = angular.module("Task", ['ngSanitize']).filter('reverse', function() {
  return function(items) {
    if (items) {
      return items.slice().reverse();
    }
  };
}).filter('unsafe', function($sce) {
  return $sce.trustAsHtml;
}).controller("ListCtrl", function($scope) {
  $scope.editing_tasks = [];
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
    var Task_copy;
    Task_copy = angular.copy(Task);
    Task_copy.id_status++;
    if (Task_copy.id_status > Object.keys($scope.task_statuses).length) {
      Task_copy.id_status = 1;
    }
    return $scope.saveTask(Task_copy).then(function(response) {
      if (response) {
        Task.id_status = Task_copy.id_status;
        return $scope.$apply();
      }
    });
  };
  $scope.deleteTask = function(Task) {
    Task.html = "";
    return $scope.saveTask(Task);
  };
  $scope.addTask = function() {
    return $.post("tasks/ajax/add", {}, function(id_task) {
      var Task;
      Task = {
        id: id_task,
        id_status: 1,
        type: $scope.type,
        html: "Текст задачи..."
      };
      $scope.Tasks.unshift(Task);
      $scope.$apply();
      $scope.editTask(Task);
      return setTimeout(function() {
        return $scope.bindFileUpload(Task);
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
    return $.each($scope.Tasks, function(i, Task) {
      return $scope.bindFileUpload(Task);
    });
  });
  return $(document).ready(function() {
    return set_scope('Task');
  });
});
