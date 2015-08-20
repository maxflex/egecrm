	angular.module "Task", ['ngSanitize']
		.filter 'reverse', ->
			(items) ->
				if items
					return items.slice().reverse()
		.controller "ListCtrl", ($scope) ->
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
					extraPlugins: 'pastebase64'
						
				$scope.e.setData Task.html
				
				$scope.e.on 'contentDom', ->
					$scope.e.document.on 'keydown', (event) ->
						event = event.data.$
						if (event.which == 13 && (event.ctrlKey||event.metaKey)|| (event.which == 19))
							Task.html = $scope.e.getData()
							$scope.e.destroy()
							delete $scope.e
							$scope.$apply()
							$scope.saveTask(Task)
						if event.which is 27
							Task.html += " "
							$scope.e.destroy()
							delete $scope.e
							$scope.$apply()
				$scope.e.on 'instanceReady', (event) ->
					$scope.e.focus().select
					$scope.e.execCommand 'selectAll'
				$scope.e.on 'blur', (event) ->
					Task.html += " "
					$scope.e.destroy()
					delete $scope.e
					$scope.$apply()
				
			$scope.editingTask = (Task) ->
				Task.id is $scope.editing_task
			
			$scope.addTask = ->
				$.post "tasks/ajax/add", {}, (id_task) ->
					Task = 
						id: id_task
						id_status: 1
						html: "Текст задачи..."
						
					$scope.Tasks.push Task 
					$scope.$apply()
					$scope.editTask Task
			
			$scope.saveTask = (Task) ->
				$.post "tasks/ajax/save", {Task: Task}
			
			$(document).ready ->
				set_scope 'Task'
				