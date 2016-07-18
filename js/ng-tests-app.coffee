	angular.module "Tests", ['ngSanitize']
		.filter 'unsafe', ($sce) -> 
			$sce.trustAsHtml
		.filter 'range', () ->
			return (input, total) ->
				total = parseInt total
				for i in [1...total + 1] by 1
					input.push i
				input
		.controller "StartCtrl", ($scope, $timeout, $interval) ->
			$timeout ->
				if $scope.final_score is undefined
					$scope.interval = $interval ->
						$scope.time++
					, 1000
				$scope.current_problem = localStorage.getItem("current_problem_#{$scope.Test.id}")
				if $scope.current_problem is null
					$scope.current_problem = 0			
			
			$scope.counter = ->
	            moment({}).seconds($scope.time).format("mm:ss")
			
			$scope.nextProblem = ->
				saveAnswers()
				if ($scope.Test.Problems.length - $scope.current_problem) is 1
					finishTest()
				else
					$scope.current_problem++

			$scope.prevProblem = ->
				saveAnswers()
				$scope.current_problem--
			
			saveAnswers = ->
				$.cookie("answers#{$scope.Test.id}", JSON.stringify($scope.answers), { expires: 3, path: '/' });
			
			finishTest = ->
				$.post "tests/ajaxFinishTest", {id: $scope.Test.id}, (final_score) ->
					$interval.cancel($scope.interval)
					$scope.final_score = final_score

			$scope.back = ->
				redirect "students/tests"
			
			$scope.notFinished = ->
				$scope.final_score is undefined
			
			$scope.$watch 'current_problem', (newVal) ->
				if newVal isnt undefined
					localStorage.setItem("current_problem_#{$scope.Test.id}", newVal)
					$scope.Problem = $scope.Test.Problems[newVal]
			
			angular.element(document).ready ->
				set_scope "Tests"
		.controller "ListCtrl", ($scope) ->
			$scope.formatDate = (date) ->
				moment(date).format 'DD MMMM'
			
			$scope.getTestStatus = (Test) ->
				test_statuses[Test.intermediate]
			
			angular.element(document).ready ->
				set_scope "Tests"
		.controller "AddCtrl", ($scope, $timeout) ->
			$scope.addTest = (Test) ->
				$scope.adding = true
				ajaxStart()
				$.post 'tests/ajaxAdd', 
					Test: $scope.Test
				, (response) ->
					redirect "tests/edit/#{response}"
				, "json"
			
			$scope.saveTest = ->
				$scope.saving = true
				ajaxStart()
				$.post "tests/ajaxEdit", 
					Test: $scope.Test
				, (response) ->
					ajaxEnd()
					$scope.saving = false
					$scope.form_changed = false
					$scope.$apply()
			
			$scope.deleteTest = ->
				bootbox.confirm "Вы уверены, что хотите удалить тест №#{$scope.Test.id}?", (result) ->
					if result is true
						ajaxStart()
						$.post "tests/ajaxDeleteTest", {id_test: $scope.Test.id}, ->
							redirect "tests"
				
			$scope.addProblem = ->
				$scope.form_changed = true
				$scope.Test.Problems.push($scope.NewProblem)
		
			$scope.editingAnswer = (parent_index, index) ->
				$scope.editing_answer and $scope.editing_answer[0] is parent_index and $scope.editing_answer[1] is index
			
			$scope.addAnswer = (Problem, parent_index) ->
				$scope.form_changed = true
				Problem.answers.push('текст ответа...')
				$timeout ->
					$scope.editAnswer(Problem, parent_index, Problem.answers.length - 1)
			
			$scope.setCorrect = (Problem, index) ->
				if typeof $scope.a is "object"
					$scope.a.destroy()
					delete $scope.a
				$scope.form_changed = true
				$scope.editing_answer = undefined
				if Problem.correct_answer == index
					Problem.correct_answer = -1
				else
					Problem.correct_answer = index
			
			$scope.deleteAnswer = (Problem, index) ->
				if Problem.correct_answer
					Problem.correct_answer = -1 if Problem.correct_answer == index
					Problem.correct_answer-- if Problem.correct_answer > index
				$scope.a.destroy()
				delete $scope.a
				$scope.form_changed = true
				$scope.editing_answer = undefined
				Problem.answers.splice(index, 1)
			
			$scope.deleteProblem = (Problem, index) ->
				$scope.e.destroy()
				delete $scope.e
				$scope.editing_problem = undefined
				$scope.Test.Problems.splice(index, 1)
				if Problem.id
					$.post "tests/ajaxDeleteProblem", 
						id_problem: Problem.id
			
			$scope.editAnswer = (Problem, parent_index, index) ->
				console.log(parent_index, index)
				answer = Problem.answers[index]
				$scope.editing_answer = [parent_index, index]
				$scope.old_html = answer
				if typeof $scope.a is "object"
					$scope.a.destroy()

				$scope.a = CKEDITOR.replace "answer-#{parent_index}-#{index}",
					language: 'ru'
					height: 150
					title: "testy"
					extraPlugins: 'pastebase64,panel,button,panelbutton,colorbutton'
						
				$scope.a.setData answer
				
				$scope.a.on 'contentDom', ->
					$scope.a.document.on 'keydown', (event) ->
						event = event.data.$
						if (event.which == 13 && (event.ctrlKey||event.metaKey)|| (event.which == 19))
							Problem.answers[index] = $scope.a.getData()
							$scope.a.destroy()
							delete $scope.a
							$scope.editing_answer = undefined
							$scope.form_changed = true
							$scope.$apply()
							# $scope.saveTask(Task)
						if event.which is 27
							Problem.answers[index] += " "
							$scope.a.destroy()
							delete $scope.a
							$scope.editing_answer = undefined
							$scope.$apply()
				$scope.a.on 'instanceReady', (event) ->
					$scope.a.focus().select
					$scope.a.execCommand 'selectAll'
					
			$scope.editProblem = (Problem, index) ->
				$scope.editing_problem = Problem
				$scope.old_html = Problem.problem
				if typeof $scope.e is "object"
					$scope.e.destroy()

				$scope.e = CKEDITOR.replace "problem-#{index}",
					language: 'ru'
					height: 250
					title: "testy"
					extraPlugins: 'pastebase64,panel,button,panelbutton,colorbutton'
						
				$scope.e.setData Problem.problem
				
				$scope.e.on 'contentDom', ->
					$scope.e.document.on 'keydown', (event) ->
						event = event.data.$
						if (event.which == 13 && (event.ctrlKey||event.metaKey)|| (event.which == 19))
							Problem.problem = $scope.e.getData()
							$scope.e.destroy()
							delete $scope.e
							$scope.editing_problem = undefined
							$scope.form_changed = true
							$scope.$apply()
							# $scope.saveTask(Task)
						if event.which is 27
							Problem.problem += " "
							$scope.e.destroy()
							delete $scope.e
							$scope.editing_problem = undefined
							$scope.$apply()
				$scope.e.on 'instanceReady', (event) ->
					$scope.e.focus().select
					$scope.e.execCommand 'selectAll'
			
			angular.element(document).ready ->
				$(".form-change-control").on 'keyup change', 'input, select', ->
					$scope.form_changed = true
					$scope.$apply()
				$timeout ->
					$scope.$broadcast('angucomplete-alt:clearInput')
				set_scope 'Tests'