angular
	.module "Print", []
	.controller "UsersCtrl", ($scope) ->
		$scope.changeStatus = (Task) ->
			if Task.id_status is 2
				Task.id_status = 0
			else
				Task.id_status++
			$.post "print/ajax/ChangeStatus",
				id_status: 	Task.id_status,
				id_task:	Task.id		
			
		$scope.formatDate = (date) ->
			moment(date).format "DD MMMM"
	.controller "TeachersCtrl", ($scope) ->
		$scope.PrintTask = 
			files: []
			comment: ""
			id_group: ""
			id_lesson: ""
		
		$scope.formatDate = (date) ->
			moment(date).format "DD MMMM"
		
		$scope.changeGroup = ->
			id_group = parseInt $scope.PrintTask.id_group
			$scope.PrintTask.id_lesson = ""
			if !id_group
				$scope.GroupLessons = []
				return false
			else
				$scope.GroupLessons = _.findWhere($scope.Groups, {id: parseInt($scope.PrintTask.id_group)}).FutureSchedule

		$scope.bindFileUpload = ->
			# загрузка файла договора
			$('#fileupload').fileupload({
				dataType: 'json',
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
					if response.result isnt "ERROR"
# 						PrintTask.files = initIfNotSet(PrintTask.files)
						$scope.PrintTask.files.push(response.result)
# 						$scope.saveTask(PrintTask)
						$scope.$apply()
					else
						notifyError("Ошибка загрузки")
				,
				fail: (e, data) ->
					$.each data.messages, (index, error) ->
						notifyError error
			})
		
		$scope.addPrintTask = ->
			if !$scope.PrintTask.id_group
				notifyError "Укажите группу"
				$("#id-group").addClass("has-error").focus()
				return
			else
				$("#id-group").removeClass("has-error")
				
			if !$scope.PrintTask.id_lesson
				notifyError "Укажите занятие"
				$("#id-lesson").addClass("has-error").focus()
				return
			else
				$("#id-lesson").removeClass("has-error")
			
			if !$scope.PrintTask.files.length
				notifyError "Добавьте файлы для печати"
				return
			
			ajaxStart()
			$scope.adding = true
			$.post "print/ajax/addTask", $scope.PrintTask, (response) ->
				redirect "print"
		$(document).ready ->
			set_scope 'Print'
			$scope.bindFileUpload()

			if localStorage.getItem('print_hint_shown') is null and $scope.for_teachers and (not $scope.PrintTasks or not $scope.PrintTasks.length)
				#initialize instance
				enjoyhint_instance = new EnjoyHint({})
					
				#simple config. 
				#Only one step - highlighting(with description) "New" button 
				#hide EnjoyHint after a click on the button.
				enjoyhint_script_steps = [
					selector: '#add-task'
					event: 'click'
					description:	'Чтобы добавить новое задание на печать,<br>' +
									'нажмите «добавить задание» в правом верхнем углу'
					right: 15
					left: -15
					skipButton:
						className: 'btn btn-success btn-lg pull-right'
						text: 'ПОНЯТНО'
				]
				#set script config
				enjoyhint_instance.set enjoyhint_script_steps
				#run Enjoyhint script
				enjoyhint_instance.run()
				
				localStorage.setItem 'print_hint_shown', true
			
			
			
