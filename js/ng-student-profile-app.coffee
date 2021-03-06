app = angular.module "StudentProfile", []
    .controller "TeacherLk", ($scope) ->
        $scope.getCabinet = (id) ->
            _.findWhere($scope.all_cabinets, {id: parseInt(id)})

        $scope.setLessonsYear = (year) -> $scope.selected_lesson_year = year

        $scope.yearLabel = (year) -> year + '-' + (parseInt(year) + 1) + ' уч. г.'

        $scope.getLessonIndex = (index, GroupLessons) ->
            index++
            GroupLessons = _.sortBy(GroupLessons, 'date_time')
            cancelled_count = _.where(GroupLessons.slice(0, index), {cancelled: 1}).length
            report_count = _.where(GroupLessons.slice(0, index), {is_report: true}).length
            return (index - cancelled_count - report_count)

        menus = ['Lessons']

        $scope.setMenu = (menu, complex_data) ->
            $scope.current_menu = menu
            $.each menus, (index, value) ->
                _loadData(index, menu, value, complex_data)

        _postData = (menu) ->
            menu: menu
            id_student: $scope.id_student

        _loadData = (menu, selected_menu, ngModel, complex_data) ->
            if $scope[ngModel] is undefined and menu is selected_menu
                $.post "ajax/TeacherLkMenu", _postData(menu), (response) ->
                    if complex_data
                        _.each response, (value, field) ->
                            $scope[field] = value
                    else
                        $scope[ngModel] = response
                    $scope.$apply()
                , "json"
        angular.element(document).ready ->
            set_scope "StudentProfile"
            $scope.setMenu(0, true)
            $scope.$apply()

	.controller "BalanceCtrl", ($scope) ->
		$scope.yearLabel = (year) -> year + '-' + (parseInt(year) + 1) + ' уч. г.'

		$scope.reverseObjKeys = (obj) -> Object.keys(obj).reverse()

		$scope.setYear = (year) -> $scope.selected_year = year

		$scope.totalSum = (date) ->
			total_sum = 0
			$.each $scope.Balance[$scope.selected_year], (d, items) ->
				return if (d > date)
				day_sum = 0
				items.forEach (item) -> day_sum += item.sum
				total_sum += day_sum
			total_sum

		angular.element(document).ready ->
			$.post "requests/ajax/LoadBalance", {id_student: $scope.id_student}, (response) ->
				['Balance', 'years', 'selected_year'].forEach (field) -> $scope[field] = response[field]
				$scope.$apply()
			, "json"
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
