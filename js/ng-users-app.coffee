app = angular.module "Users", ['colorpicker.module', 'ngSanitize']
	.filter 'to_trusted', ['$sce', ($sce) ->
		return (text) ->
			return $sce.trustAsHtml(text)
	]
	.controller "ListCtrl", ($scope, $http, $timeout) ->
		$timeout -> $('.watch-select').selectpicker()

		refreshCounts = ->
			$timeout ->
				$('.watch-select option').each (index, el) ->
	                $(el).data 'subtext', $(el).attr 'data-subtext'
	                $(el).data 'content', $(el).attr 'data-content'
	            $('.watch-select').selectpicker 'refresh'
	        , 100

		$scope.toggleRights = (User, right) ->
			NewUser = angular.copy(User)
			right = parseInt(right)
			if $scope.allowed(NewUser, right)
				NewUser.rights = _.reject NewUser.rights, (val) -> val is right
			else
				NewUser.rights.push(right)
			data = {}
			data[NewUser.id] = NewUser
			data[NewUser.id].rights = if NewUser.rights.length then NewUser.rights else ['']
			$.post "users/ajax/save",
				Users: data
			, (response) ->
				if response is 'success'
					User.rights = NewUser.rights
					$scope.$apply()
					refreshCounts()

		$scope.getCounts = (right = false) ->
			return ($scope.ActiveUsers.length + $scope.BannedUsers.length) if right is false
			count = _.reject($scope.ActiveUsers, (User) ->
				return User.rights.indexOf(parseInt(right)) is -1
			).length
			count += _.reject($scope.BannedUsers, (User) ->
				return User.rights.indexOf(parseInt(right)) is -1
			).length
			count or ''

		$scope.allowed = (User, right) ->
			User.rights.indexOf(parseInt(right)) isnt -1

		angular.element(document).ready -> set_scope 'Users'
	.controller "EditCtrl", ($scope, $timeout, PhoneService) ->
		$scope.PhoneService     = PhoneService
		$scope.picture_version  = 1


		bindIpMask = -> $(".ip-mask").inputmask("Regex", {regex: "[0-9]{1,3}[\.][0-9]{1,3}[\.][0-9]{1,3}[\.][0-9]{1,3}"});

		$scope.toggleRights = (right) ->
			if $scope.allowed(right)
				$scope.User.rights = _.reject $scope.User.rights, (val) -> val is right
			else
				$scope.User.rights.push(right)

		$scope.addIp = ->
			$scope.User.ips.push
				ip_from: ''
				ip_to: ''
				confirm_by_sms: false
			$timeout -> bindIpMask()

		$scope.removeIp = (index) ->
			$scope.User.ips.splice(index, 1)

		$scope.allowed = (right) ->
			$scope.User.rights.indexOf(right) isnt -1

		$scope.clone_user = ->
			$scope.old_data = angular.copy $scope.User

		$scope.save = ->
			ajaxStart()
			$.post "users/ajax/save",
				Users: [$scope.User]
			, (response) ->
				ajaxEnd()
				$scope.clone_user()
				$scope.form_changed = false
				$scope.$apply()

		angular.element(document).ready ->
			set_scope 'Users'
			$scope.clone_user()
			bindCropper()
			bindFileUpload()
			bindIpMask()
			$scope.$watchCollection 'User', (new_val) ->
				$scope.form_changed = !angular.equals($scope.old_data, new_val)

		#
		#   все что связано с фото
		#

		$scope.dialog = (id) ->
			$("##{id}").modal 'show'
			return

		$scope.closeDialog = (id) ->
			$("##{id}").modal 'hide'
			return

		$scope.deletePhoto = ->
			bootbox.confirm 'Удалить фото пользователя?', (result) ->
				if result is true
					ajaxStart()
					$.post "users/ajax/deletePhoto",
						user_id: $scope.User.id
					, (response) ->
						ajaxEnd()
						$scope.User.has_photo_cropped = false
						$scope.User.has_photo_original = false
						$scope.User.photo_cropped_size = 0
						$scope.User.photo_original_size = 0
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
				formData.append 'user_id', $scope.User.id
				ajaxStart()
				$.ajax 'upload/cropped',
					method: 'POST'
					data: formData
					processData: false
					contentType: false
					dataType: 'json'
					success: (response) ->
						ajaxEnd()
						$scope.User.has_photo_cropped = true
						$scope.User.photo_cropped_size = response
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
					user_id: $scope.User.id
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
					$scope.User.photo_extension     = response.result.extension
					$scope.User.photo_original_size = response.result.size
					$scope.User.photo_cropped_size  = 0
					$scope.User.has_photo_original  = true
					$scope.User.has_photo_cropped   = false
					$scope.picture_version++
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

	.controller "CreateCtrl", ($scope, $http) ->
		angular.element(document).ready -> set_scope 'Users'

		$scope.user_exists = false

		$scope.checkExistance = ->
			if $scope.User.login.length
				$.post "users/ajax/exists",
					login: $scope.User.login
				.then (response) ->
					$scope.user_exists = response > 0
					$scope.$apply()
			else
				$scope.user_exists = false

		$scope.requiredFilled = ->
			$scope.User.login and $scope.User.login.length and !$scope.user_exists

		$scope.save = ->
			ajaxStart()
			$.post "users/ajax/create",
				user: $scope.User
			, (response) ->
				ajaxEnd()
				# redirect "users/edit/#{response}"
