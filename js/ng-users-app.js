var app;

app = angular.module("Users", ['colorpicker.module', 'ngSanitize']).filter('to_trusted', [
  '$sce', function($sce) {
    return function(text) {
      return $sce.trustAsHtml(text);
    };
  }
]).controller("ListCtrl", function($scope, $http, $timeout) {
  var refreshCounts;
  $timeout(function() {
    return $('.watch-select').selectpicker();
  });
  refreshCounts = function() {
    return $timeout(function() {
      $('.watch-select option').each(function(index, el) {
        $(el).data('subtext', $(el).attr('data-subtext'));
        return $(el).data('content', $(el).attr('data-content'));
      });
      return $('.watch-select').selectpicker('refresh', 100);
    });
  };
  $scope.toggleRights = function(User, right) {
    var NewUser, data;
    NewUser = angular.copy(User);
    right = parseInt(right);
    if ($scope.allowed(NewUser, right)) {
      NewUser.rights = _.reject(NewUser.rights, function(val) {
        return val === right;
      });
    } else {
      NewUser.rights.push(right);
    }
    data = {};
    data[NewUser.id] = NewUser;
    data[NewUser.id].rights = NewUser.rights.length ? NewUser.rights : [''];
    return $.post("users/ajax/save", {
      Users: data
    }, function(response) {
      if (response === 'success') {
        User.rights = NewUser.rights;
        $scope.$apply();
        return refreshCounts();
      }
    });
  };
  $scope.getCounts = function(right) {
    var count;
    if (right == null) {
      right = false;
    }
    if (right === false) {
      return $scope.ActiveUsers.length + $scope.BannedUsers.length;
    }
    count = _.reject($scope.ActiveUsers, function(User) {
      return User.rights.indexOf(parseInt(right)) === -1;
    }).length;
    count += _.reject($scope.BannedUsers, function(User) {
      return User.rights.indexOf(parseInt(right)) === -1;
    }).length;
    return count || '';
  };
  $scope.allowed = function(User, right) {
    return User.rights.indexOf(parseInt(right)) !== -1;
  };
  return angular.element(document).ready(function() {
    return set_scope('Users');
  });
}).controller("EditCtrl", function($scope, $timeout, PhoneService) {
  var bindCropper, bindFileUpload;
  $scope.PhoneService = PhoneService;
  $scope.has_pswd_error = false;
  $scope.psw_filled = false;
  $scope.picture_version = 1;
  $scope.toggleRights = function(right) {
    if ($scope.allowed(right)) {
      return $scope.User.rights = _.reject($scope.User.rights, function(val) {
        return val === right;
      });
    } else {
      return $scope.User.rights.push(right);
    }
  };
  $scope.allowed = function(right) {
    return $scope.User.rights.indexOf(right) !== -1;
  };
  $scope.clone_user = function() {
    return $scope.old_data = angular.copy($.extend($scope.User, {
      new_password: '',
      new_password_repeat: ''
    }));
  };
  $scope.$watchCollection('[User.new_password, User.new_password_repeat]', function() {
    var has_pswd_error, j, len, p1, p2, ref, x;
    p1 = $scope.User.new_password;
    p2 = $scope.User.new_password_repeat;
    if (p1 || p2) {
      $scope.psw_filled = true;
      ref = [p1, p2];
      for (j = 0, len = ref.length; j < len; j++) {
        x = ref[j];
        has_pswd_error = !x || (x && !(x.match('^[a-zA-Z0-9_]{10,}$') && x.match('[a-zA-Z]+') && x.match('[0-9]+') && x.match('[_]+')));
        if (has_pswd_error) {
          break;
        }
      }
      return $scope.has_pswd_error = (p1 !== p2) || has_pswd_error;
    } else {
      return $scope.psw_filled = false;
    }
  });
  $scope.save = function() {
    ajaxStart();
    return $.post("users/ajax/save", {
      Users: {
        102: $scope.User
      }
    }, function(response) {
      ajaxEnd();
      $scope.clone_user();
      $scope.form_changed = false;
      return $scope.$apply();
    });
  };
  angular.element(document).ready(function() {
    set_scope('Users');
    $scope.clone_user();
    bindCropper();
    bindFileUpload();
    return $scope.$watchCollection('User', function(new_val) {
      return $scope.form_changed = !angular.equals($scope.old_data, new_val);
    });
  });
  $scope.dialog = function(id) {
    $("#" + id).modal('show');
  };
  $scope.closeDialog = function(id) {
    $("#" + id).modal('hide');
  };
  $scope.deletePhoto = function() {
    return bootbox.confirm('Удалить фото пользователя?', function(result) {
      if (result === true) {
        ajaxStart();
        return $.post("users/ajax/deletePhoto", {
          user_id: $scope.User.id
        }, function(response) {
          ajaxEnd();
          $scope.User.has_photo_cropped = false;
          $scope.User.has_photo_original = false;
          $scope.User.photo_cropped_size = 0;
          $scope.User.photo_original_size = 0;
          return $scope.$apply();
        });
      }
    });
  };
  $scope.formatBytes = function(bytes) {
    if (bytes < 1024) {
      return bytes + ' Bytes';
    } else if (bytes < 1048576) {
      return (bytes / 1024).toFixed(1) + ' KB';
    } else if (bytes < 1073741824) {
      return (bytes / 1048576).toFixed(1) + ' MB';
    } else {
      return (bytes / 1073741824).toFixed(1) + ' GB';
    }
  };
  $scope.saveCropped = function() {
    return $('#photo-edit').cropper('getCroppedCanvas').toBlob(function(blob) {
      var formData;
      formData = new FormData;
      formData.append('croppedImage', blob);
      formData.append('user_id', $scope.User.id);
      ajaxStart();
      return $.ajax('upload/cropped', {
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
          ajaxEnd();
          $scope.User.has_photo_cropped = true;
          $scope.User.photo_cropped_size = response;
          $scope.picture_version++;
          $scope.$apply();
          return $scope.closeDialog('change-photo');
        }
      });
    });
  };
  bindCropper = function() {
    $('#photo-edit').cropper('destroy');
    return $('#photo-edit').cropper({
      aspectRatio: 4 / 5,
      minContainerHeight: 700,
      minContainerWidth: 700,
      minCropBoxWidth: 240,
      minCropBoxHeight: 300,
      preview: '.img-preview',
      viewMode: 1,
      crop: function(e) {
        var width;
        width = $('#photo-edit').cropper('getCropBoxData').width;
        if (width >= 240) {
          return $('.cropper-line, .cropper-point').css('background-color', '#158E51');
        } else {
          return $('.cropper-line, .cropper-point').css('background-color', '#D9534F');
        }
      }
    });
  };
  bindFileUpload = function() {
    return $('#fileupload').fileupload({
      formData: {
        user_id: $scope.User.id
      },
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
        response.result = JSON.parse(response.result);
        $scope.User.photo_extension = response.result.extension;
        $scope.User.photo_original_size = response.result.size;
        $scope.User.photo_cropped_size = 0;
        $scope.User.has_photo_original = true;
        $scope.User.has_photo_cropped = false;
        $scope.picture_version++;
        $scope.$apply();
        return bindCropper();
      }
    });
  };
  return $scope.showPhotoEditor = function() {
    $scope.dialog('change-photo');
    return $timeout(function() {
      return $('#photo-edit').cropper('resize');
    }, 100);
  };
}).controller("CreateCtrl", function($scope, $http) {
  $scope.user_exists = false;
  $scope.has_pswd_error = true;
  $scope.psw_filled = false;
  $scope.$watchCollection('[User.new_password, User.new_password_repeat]', function() {
    var has_pswd_error, j, len, p1, p2, ref, x;
    p1 = $scope.User.new_password;
    p2 = $scope.User.new_password_repeat;
    if (p1 || p2) {
      $scope.psw_filled = true;
      ref = [p1, p2];
      for (j = 0, len = ref.length; j < len; j++) {
        x = ref[j];
        has_pswd_error = !x || (x && !(x.match('^[a-zA-Z0-9_]{10,}$') && x.match('[a-zA-Z]+') && x.match('[0-9]+') && x.match('[_]+')));
        if (has_pswd_error) {
          break;
        }
      }
      return $scope.has_pswd_error = (p1 !== p2) || has_pswd_error;
    } else {
      return $scope.psw_filled = false;
    }
  });
  $scope.checkExistance = function() {
    if ($scope.User.login.length) {
      return $.post("users/ajax/exists", {
        login: $scope.User.login
      }).then(function(response) {
        $scope.user_exists = response > 0;
        return $scope.$apply();
      });
    } else {
      return $scope.user_exists = false;
    }
  };
  $scope.requiredFilled = function() {
    return $scope.psw_filled && !$scope.has_pswd_error && $scope.User.login && $scope.User.login.length && !$scope.user_exists;
  };
  return $scope.save = function() {
    ajaxStart();
    return $.post("users/ajax/create", {
      user: $scope.User
    }, function(response) {
      ajaxEnd();
      return redirect("users/edit/" + response);
    });
  };
});
