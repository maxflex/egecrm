	app = angular.module "Task", ['ngSanitize']
		.filter 'reverse', ->
			(items) ->
				if items
					return items.slice().reverse()
		.filter 'unsafe', ($sce) ->
			$sce.trustAsHtml
		.controller "ListCtrl", ($scope, TaskTypes) ->
			bindArguments $scope, arguments
			$scope.editing_tasks = []

			$scope.editTask = (Task) ->
				$scope.editing_task = Task.id
				$scope.old_html = Task.html
				if typeof @e is "object"
					$scope.e.destroy()

				$scope.e = CKEDITOR.replace "task-#{Task.id}",
					language: 'ru'
					height: 500
					title: "testy"
					extraPlugins: 'pastebase64,panel,button,panelbutton,colorbutton'

				$scope.e.setData Task.html

				$scope.e.on 'contentDom', ->
					$scope.e.document.on 'keydown', (event) ->
						event = event.data.$
						if (event.which == 13 && (event.ctrlKey||event.metaKey)|| (event.which == 19))
							Task.html = $scope.e.getData()
							$scope.e.destroy()
							delete $scope.e
							$scope.editing_task = undefined
							$scope.$apply()
							$scope.saveTask(Task)
						if event.which is 27
							Task.html += " "
							$scope.e.destroy()
							delete $scope.e
							$scope.editing_task = undefined
							$scope.$apply()
				$scope.e.on 'instanceReady', (event) ->
					$scope.e.focus().select
					$scope.e.execCommand 'selectAll'

			$scope.editingTask = (Task) ->
				Task.id is $scope.editing_task

			$scope.toggleTaskStatus = (Task) ->
				Task_copy = {id: Task.id, id_status: Task.id_status}
				# добавляем возможность пропускать удаленные (идущие не подряд) статусы
				task_statuses = Object.keys($scope.task_statuses).map(Number)
				Task_copy.id_status = task_statuses[task_statuses.indexOf(Task_copy.id_status) + 1]

				if not Task_copy.id_status
					Task_copy.id_status = 1

				$scope.saveTask(Task_copy).then (response) ->
					console.log(response)
					if response
						Task.id_status = Task_copy.id_status
						$scope.$apply()

			$scope.toggleType = (Task) ->
				Task_copy = {id: Task.id, type: Task.type}
				Task_copy.type = (Task_copy.type+1)%2

				$scope.saveTask(Task_copy).then (response) ->
					if response
						Task.type = Task_copy.type
						$scope.$apply()

			$scope.deleteTask = (Task) ->
				Task.delete = 1
				$scope.saveTask {id: Task.id, delete: 1}


			$scope.addTask = ->
				$.post "tasks/ajax/add", {}, (id_task) ->
					Task =
						id: id_task
						id_status: 1
						type: $scope.type
						html: "Текст задачи..."

					$scope.Tasks.unshift Task
					$scope.$apply()
					$scope.editTask Task
					setTimeout ->
						$scope.bindFileUpload Task
					, 100

			$scope.bindFileUpload = (Task) ->
				# загрузка файла договора
				$('#fileupload' + Task.id).fileupload({
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
							Task.files = initIfNotSet(Task.files)
							Task.files.push(response.result)
							$scope.saveTask(Task)
							$scope.$apply()
						else
							notifyError("Ошибка загрузки")
					,
					fail: (e, data) ->
						$.each data.messages, (index, error) ->
							notifyError error
				})

			$scope.deleteTaskFile = (Task, id) ->
			    Task.files.splice id, 1
			    $scope.saveTask(Task)

			$scope.saveTask = (Task) ->
				$.post "tasks/ajax/save", {Task: Task}

			angular.element(document).ready ->
				$.each $scope.Tasks, (i, Task) ->
					$scope.bindFileUpload Task

			$(document).ready ->
				set_scope 'Task'