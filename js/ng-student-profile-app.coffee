angular.module "StudentProfile", []
    .controller "PhotoCtrl", ($scope, $timeout) ->
        $scope.picture_version = 1

        $scope.dialog = (id) ->
            $("##{id}").modal 'show'
            return

        $scope.closeDialog = (id) ->
            $("##{id}").modal 'hide'
            return

        $scope.deletePhoto = ->
            bootbox.confirm 'Удалить фото?', (result) ->
                if result is true
                    ajaxStart()
                    $.post "students/ajax/deletePhoto",
                        student_id: $scope.Student.id
                    , (response) ->
                        ajaxEnd()
                        $scope.Student.has_photo_cropped = false
                        $scope.Student.has_photo_original = false
                        $scope.Student.photo_cropped_size = 0
                        $scope.Student.photo_original_size = 0
                        $('.add-photo-badge').show()
                        $scope.$apply()

        $scope.formatBytes = (bytes) ->
            if bytes < 1024
                bytes + ' Bytes'
            else if bytes < 1048576
                (bytes / 1024).toFixed(1) + ' KB'
            else if bytes < 1073741824
                (bytes / 1048576).toFixed(1) + ' MB'
            else
                (bytes / 1073741824).toFixed(1) + ' GB'

        $scope.saveCropped = ->
            $('#photo-edit').cropper('getCroppedCanvas').toBlob (blob) ->
                formData = new FormData
                formData.append 'croppedImage', blob
                formData.append 'student_id', $scope.Student.id
                ajaxStart()
                $.ajax 'upload/croppedStudent',
                    method: 'POST'
                    data: formData
                    processData: false
                    contentType: false
                    dataType: 'json'
                    success: (response) ->
                        ajaxEnd()
                        $scope.Student.has_photo_cropped = true
                        $scope.Student.photo_cropped_size = response
                        $scope.picture_version++
                        $scope.$apply()
                        $scope.closeDialog('change-photo')

        bindCropper = ->
            $('#photo-edit').cropper 'destroy'
            $('#photo-edit').cropper
                aspectRatio: 4 / 5
                minContainerHeight: 700
                minContainerWidth: 700
                minCropBoxWidth: 240
                minCropBoxHeight: 300
                preview: '.img-preview'
                viewMode: 1
                crop: (e) ->
                    width = $('#photo-edit').cropper('getCropBoxData').width
                    if width >= 240
                        $('.cropper-line, .cropper-point').css 'background-color', '#158E51'
                    else
                        $('.cropper-line, .cropper-point').css 'background-color', '#D9534F'

        bindFileUpload = ->
            # загрузка файла договора
            $('#fileupload').fileupload
                formData:
                    student_id: $scope.Student.id
                    maxFileSize: 10000000, # 10 MB
                # начало загрузки
                send: ->
                    NProgress.configure({ showSpinner: true })
                ,
                # во время загрузки
                progress: (e, data) ->
                    NProgress.set(data.loaded / data.total)
                ,
                # всегда по окончании загрузки (неважно, ошибка или успех)
                always: ->
                    NProgress.configure({ showSpinner: false })
                    ajaxEnd()
                ,
                done: (i, response) ->
                    response.result = JSON.parse response.result
                    $scope.Student.photo_extension     = response.result.extension
                    $scope.Student.photo_original_size = response.result.size
                    $scope.Student.photo_cropped_size  = 0
                    $scope.Student.has_photo_original  = true
                    $scope.Student.has_photo_cropped   = false
                    $scope.picture_version++
                    $('.add-photo-badge').hide()
                    $scope.$apply()
                    bindCropper()
                ,

        # show photo editor
        $scope.showPhotoEditor = ->
            $scope.dialog('change-photo')
            # rare bug fix
            $timeout ->
                $('#photo-edit').cropper 'resize'
            , 100

        angular.element(document).ready ->
            bindCropper()
            bindFileUpload()

            set_scope "StudentProfile"
