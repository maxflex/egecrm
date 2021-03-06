var ajaxEnd, ajaxStart, clearSelect, deleteTeacher, emailMode, isMobilePhone, objectToArray, phoneCorrect, set_scope;

set_scope = function(app_name) {
  return this.ang_scope = angular.element("[ng-app='" + app_name + "']").scope();
};

phoneCorrect = function(element) {
  var not_filled;
  if (!$("#" + element).val()) {
    return false;
  }
  not_filled = $("#" + element).val().match(/_/);
  return not_filled === null;
};

deleteTeacher = function(id_teacher) {
  return bootbox.confirm("Вы уверены, что хотите удалить преподавателя №" + id_teacher + "?", function(result) {
    if (result === true) {
      ajaxStart();
      $.post("teachers/ajax/delete", {
        id_teacher: id_teacher
      });
      return window.history.go(-1);
    }
  });
};

objectToArray = function(Obj) {
  return $.map(Obj, function(value, index) {
    return [value];
  });
};

isMobilePhone = function(element) {
  var phone;
  phone = $("#" + element).val();
  if (!phone) {
    return false;
  }
  return !phone.indexOf("+7 (9");
};

emailMode = function(mode) {
  $("#email-mode").val(mode);
  switch (mode) {
    case 2:
      $(".email-group-controls").show();
      return $(".email-template-list").hide();
  }
};

ajaxStart = function(element) {
  if (element == null) {
    element = false;
  }
  if (element !== false) {
    $(".ajax-" + element + "-button").attr("disabled", "disabled");
  }
  return NProgress.start();
};

ajaxEnd = function(element) {
  if (element == null) {
    element = false;
  }
  if (element !== false) {
    $(".ajax-" + element + "-button").removeAttr("disabled");
  }
  return NProgress.done();
};

clearSelect = function(ms, callback) {
  if (ms == null) {
    ms = 50;
  }
  if (callback == null) {
    callback = void 0;
  }
  return setTimeout(function() {
    $("option[value^='?']").remove();
    if (callback !== void 0) {
      return callback();
    }
  }, ms);
};
