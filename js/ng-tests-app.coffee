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
						$scope.time--
						console.log($scope.time)
						finishTest() if $scope.time <= 0
					, 1000
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
			
			$scope.setProblem = (index) ->
				$scope.current_problem = index
			
			saveAnswers = ->
				$.post "tests/ajaxSaveAnswers", {id: $scope.Test.id, answers: $scope.answers}, (response) ->
					$scope.server_answers = angular.copy($scope.answers)
					$scope.$apply()
# 				$.cookie("answers#{$scope.Test.id}", JSON.stringify($scope.answers), { expires: 3, path: '/' });
			
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
					$scope.Problem = $scope.Test.Problems[newVal]
			
			angular.element(document).ready ->
				set_scope "Tests"
		.controller "ListCtrl", ($scope) ->
			$scope.formatDate = (date) ->
				moment(date).format 'DD MMMM'
			
			$scope.getTestStatus = (Test) ->
				test_statuses[Test.intermediate]
			
			$scope.timeLeft = (StudentTest, Test) ->
				timestamp_end = moment(StudentTest.date_start).add(Test.minutes, 'minutes').unix()
				seconds = timestamp_end - moment().unix()
				moment({}).seconds(seconds).format("mm:ss")
			
			
			
			setInterval ->
				$scope.$apply()
			, 1000
			
			$scope.testDisplay = (StudentTest) ->
				StudentTest.isFinished || StudentTest.inProgress
			
			$scope.getStudentAnswer = (Problem, StudentTest) ->
				if StudentTest.answers and StudentTest.answers[Problem.id] isnt undefined
					if StudentTest.answers[Problem.id] == Problem.correct_answer
						return true
					else
						return false
				return undefined
			
			$scope.getTestHint = (Problem, StudentTest) ->
				answer = $scope.getStudentAnswer(Problem, StudentTest)
				if answer isnt undefined
					return 'ответ установлен'
				else
					return 'ответ не установлен'
			
			$scope.getCurrentScore = (Test, StudentTest) ->
				count = 0
				$.each Test.Problems, (index, Problem) ->
					if $scope.getStudentAnswer(Problem, StudentTest)
						count += Problem.score
				return count
			
			$scope.formatTestDate = (StudentTest) ->
				moment(StudentTest.date_start).format('DD.MM.YY в HH:mm')	
			
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

					new_problems = _.filter $scope.Test.Problems, (problem) -> !problem.id
					if new_problems.length is response.length
						for i of response
							new_problems[i]['id'] = response[i]

					$scope.saving = false
					$scope.form_changed = false
					$scope.$apply()
				, "json"
			
			$scope.deleteTest = ->
				bootbox.confirm "Вы уверены, что хотите удалить тест №#{$scope.Test.id}?", (result) ->
					if result is true
						ajaxStart()
						$.post "tests/ajaxDeleteTest", {id_test: $scope.Test.id}, ->
							redirect "tests"
				
			$scope.addProblem = ->
				$scope.form_changed = true
				$scope.Test.Problems.push angular.copy $scope.NewProblem
		
			$scope.editingAnswer = (parent_index, index) ->
				$scope.editing_answer and $scope.editing_answer[0] is parent_index and $scope.editing_answer[1] is index
			
			$scope.addAnswer = (Problem, parent_index) ->
				$scope.form_changed = true
				Problem.answers.push('текст ответа...')
				$timeout ->
					$scope.editAnswer(Problem, parent_index, Problem.answers.length - 1)
			
			$scope.setCorrect = (Problem, index) ->
				if typeof $scope.a is "object"

					# соханение контента тоже
					Problem.answers[index] = $scope.a.getData()

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

				# если какой то ответ редактировался и не сохранено то его сохраняем сначала.
				Problem.answers[$scope.editing_answer[1]] = $scope.a.getData() if $scope.a

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
			
			$scope.editIntro = ->
				$scope.old_html = $scope.Test.intro
				if typeof $scope.t is "object"
					$scope.t.destroy()

				$scope.t = CKEDITOR.replace "test-intro",
					language: 'ru'
					height: 250
					title: "testy"
					extraPlugins: 'pastebase64,panel,button,panelbutton,colorbutton'
						
				$scope.t.setData $scope.Test.intro
				
				$scope.t.on 'contentDom', ->
					$scope.t.document.on 'keydown', (event) ->
						event = event.data.$
						if (event.which == 13 && (event.ctrlKey||event.metaKey)|| (event.which == 19))
							console.log 'hererere'
							$scope.Test.intro = $scope.t.getData()
							$scope.t.destroy()
							delete $scope.t
							$scope.form_changed = true
							$scope.$apply()
							# $scope.saveTask(Task)
						if event.which is 27
							$scope.Test.intro += " "
							$scope.t.destroy()
							delete $scope.t
							$scope.$apply()
				$scope.t.on 'instanceReady', (event) ->
					$scope.t.focus().select
					$scope.t.execCommand 'selectAll'
			
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