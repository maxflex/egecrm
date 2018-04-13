var app;

app = angular.module("StudentProfile", []).controller("BalanceCtrl", function($scope) {
  $scope.yearLabel = function(year) {
    return year + '-' + (parseInt(year) + 1) + ' уч. г.';
  };
  $scope.reverseObjKeys = function(obj) {
    return Object.keys(obj).reverse();
  };
  $scope.setYear = function(year) {
    return $scope.selected_year = year;
  };
  $scope.totalSum = function(date) {
    var total_sum;
    total_sum = 0;
    $.each($scope.Balance[$scope.selected_year], function(d, items) {
      var day_sum;
      if (d > date) {
        return;
      }
      day_sum = 0;
      items.forEach(function(item) {
        return day_sum += item.sum;
      });
      return total_sum += day_sum;
    });
    return total_sum;
  };
  return angular.element(document).ready(function() {
    return $.post("requests/ajax/LoadBalance", {
      id_student: $scope.id_student
    }, function(response) {
      ['Balance', 'years', 'selected_year'].forEach(function(field) {
        return $scope[field] = response[field];
      });
      return $scope.$apply();
    }, "json").controller("PhotoCtrl", function($scope, $timeout) {
      var bindCropper, bindFileUpload;
      $scope.picture_version = 1;
      $scope.dialog = function(id) {
        $("#" + id).modal('show');
      };
      $scope.closeDialog = function(id) {
        $("#" + id).modal('hide');
      };
      $scope.deletePhoto = function() {
        return bootbox.confirm('Удалить фото?', function(result) {
          if (result === true) {
            ajaxStart();
            return $.post("students/ajax/deletePhoto", {
              student_id: $scope.Student.id
            }, function(response) {
              ajaxEnd();
              $scope.Student.has_photo_cropped = false;
              $scope.Student.has_photo_original = false;
              $scope.Student.photo_cropped_size = 0;
              $scope.Student.photo_original_size = 0;
              $('.add-photo-badge').show();
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
          formData.append('student_id', $scope.Student.id);
          ajaxStart();
          return $.ajax('upload/croppedStudent', {
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
              ajaxEnd();
              $scope.Student.has_photo_cropped = true;
              $scope.Student.photo_cropped_size = response;
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
            student_id: $scope.Student.id,
            maxFileSize: 10000000
          },
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
            $scope.Student.photo_extension = response.result.extension;
            $scope.Student.photo_original_size = response.result.size;
            $scope.Student.photo_cropped_size = 0;
            $scope.Student.has_photo_original = true;
            $scope.Student.has_photo_cropped = false;
            $scope.picture_version++;
            $('.add-photo-badge').hide();
            $scope.$apply();
            return bindCropper();
          }
        });
      };
      $scope.showPhotoEditor = function() {
        $scope.dialog('change-photo');
        return $timeout(function() {
          return $('#photo-edit').cropper('resize');
        }, 100);
      };
      return angular.element(document).ready(function() {
        bindCropper();
        bindFileUpload();
        return set_scope("StudentProfile");
      });
    });
  });
});
