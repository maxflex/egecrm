	angular.module "Testing", ['angucomplete-alt']
		.filter 'range', () ->
			return (input, total) ->
				total = parseInt total
				for i in [1...total + 1] by 1
					input.push i
				input
		.controller "ListCtrl", ($scope) ->
			$scope.formatDate = (date) ->
				moment(date).format 'DD MMMM'
				
			angular.element(document).ready ->
				set_scope "Testing"
		.controller "StudentsCtrl", ($scope) ->
			$scope.testingStarted = (Testing) ->
				moment("#{Testing.date} #{Testing.start_time}").unix() <= Math.floor(Date.now() / 1000)
				
			$scope.addTesting = (Testing) ->
				Testing.adding = true
				$.post 'testing/ajaxAddStudent', 
					id_testing: Testing.id
					id_subject: Testing.selected_subject
					grade: $scope.grade
				, (response) ->
					Testing.Students = initIfNotSet Testing.Students
					Testing.Students.push response
					$scope.$apply()
					$('.subject-select').selectpicker('refresh')
				, "json"
					
			
			$scope.getTesting = (Testing) ->
				_.findWhere(Testing.Students, {id_student: $scope.id_student})
			
			$scope.getAllSubjects = (Testing) ->
				subject_ids = []
				if $scope.grade is 11 and Testing.subjects_11 isnt null
					$.each Testing.subjects_11, (id_subject, value) ->
						subject_ids.push(id_subject) if id_subject > 0
				else if $scope.grade is 9 and Testing.subjects_9 isnt null
					$.each Testing.subjects_9, (id_subject, value) ->
						subject_ids.push(id_subject) if id_subject > 0
				subject_ids
			
			
			$scope.getAllSubjects = (Testing) ->
				subject_ids = []
				if $scope.grade is 11 and Testing.subjects_11 isnt null
					$.each Testing.subjects_11, (id_subject, value) ->
						subject_ids.push(id_subject) if id_subject > 0
				else if $scope.grade is 9 and Testing.subjects_9 isnt null
					$.each Testing.subjects_9, (id_subject, value) ->
						subject_ids.push(id_subject) if id_subject > 0
				subject_ids
			
			$scope.totalTestsCount = (Testing) ->
				Object.keys(Testing.subjects_9).length + Object.keys(Testing.subjects_11).length
			
			$scope.isAvailable = (Testing, id_subject) ->
				if $scope.grade is 11
					id_subject in Object.keys(Testing.subjects_11)
				else
					id_subject in Object.keys(Testing.subjects_9)
			
# 			$scope.getAvailable = (Testing) ->
# 				subject_ids = []
# 				if $scope.TestingData.grade is 11
# 					$.each Testing.subjects_11, (id_subject, value) ->
# 						subject_ids.push(id_subject) if parseInt(id_subject) in $scope.TestingData.subject_ids
# 				else
# 					$.each Testing.subjects_9, (id_subject, value) ->
# 						subject_ids.push(id_subject) if parseInt(id_subject) in $scope.TestingData.subject_ids
# 				subject_ids
				
			$scope.formatDate = (date) ->
				moment(date).format 'DD MMMM'
			angular.element(document).ready ->
				$(".subject-select").selectpicker()
				set_scope "Testing"
		.controller "AddCtrl", ($scope) ->
			
			$scope.formatDay = (date) ->
				day = moment(date).format "ddd"
				month = moment(date).format ", DD MMMM"
				
				day.toUpperCase() + month
			
			$scope.subjectChecked = (grade, id_subject) ->
				
				if grade is 11
					arr = $scope.Testing.subjects_11
				else  
					arr = $scope.Testing.subjects_9
				
				return $.inArray(parseInt(id_subject), arr) >= 0
				
			
			$scope.deleteTesting = (id_testing) ->
				bootbox.confirm "Вы уверены, что хотите удалить тестирование №#{id_testing}?", (result) ->
					if result is true
						ajaxStart()
						$.post "testing/ajaxDelete", {id_testing: id_testing}, ->
							redirect "testing"
			
			$scope.changeDate = ->
				$scope.cabinet_load = undefined
				$.post "testing/ajaxChangeDate", 
					id: $scope.Testing.id
					date: $scope.Testing.date
				, (response) ->
					$scope.cabinet_load = response
					$scope.$apply()
				, "json"
			
			$scope.refreshSelect = ->
				setTimeout ->
					$('#subject-add-student').selectpicker('refresh')
				, 100
			
			$scope.notEnoughTime = (minutes) ->
				return true if !$scope.Testing || !$scope.Testing.start_time or !$scope.Testing.end_time
				
				date_start = new Date('2015-09-01 ' + $scope.Testing.start_time)
				date_end = new Date('2015-09-01 ' + $scope.Testing.end_time)
				
				minutes_start 	= (date_start.getHours() * 60) + date_start.getMinutes()
				minutes_end 	= (date_end.getHours() * 60) + date_end.getMinutes()
				
# 				console.log (minutes_end - minutes_start), minutes
				(minutes_end - minutes_start) < minutes
			
			$scope.addStudent = ->
				return if !$scope.selectedSubjectGrade or !$scope.selectedStudent
				
				$scope.form_changed = true
				
				data = $scope.selectedSubjectGrade.split '|'
				
				$scope.Testing.Students = initIfNotSet $scope.Testing.Students
				$scope.Testing.Students.push
					id_student: $scope.selectedStudent.originalObject.id
					id_subject: data[0]
					grade:      data[1]


				$.post 'testing/ajaxGetStudentGroupsBySubject',
					id_student: $scope.selectedStudent.originalObject.id
					id_subject: data[0]
					grade:      data[1]
				, (response) ->
                    l = $scope.Testing.Students.length
                    $scope.Testing.Students[l-1].group_ids = response
                    $scope.$apply()
				, "json"


				$scope.selectedSubjectGrade = undefined
				$scope.selectedStudent = undefined
				
				setTimeout ->
					$scope.$broadcast('angucomplete-alt:clearInput')
					$scope.$apply()
					$('#subject-add-student').selectpicker('refresh')
				, 50
				
				return false
			
			$scope.getStudent = (id_student) ->
				_.findWhere $scope.Students,
					id: id_student
			 
			$scope.deleteStudent = (id_student) ->
				$scope.form_changed = true
				$scope.Testing.Students = _.without($scope.Testing.Students, _.findWhere($scope.Testing.Students, {id_student: id_student}))
			
			$scope.form_changed = false
			
			$scope.saveTesting = ->
				$scope.saving = true
				ajaxStart()
				$.post "testing/ajaxSave", 
					Testing: $scope.Testing
				, (response) ->
					ajaxEnd()
					$scope.saving = false
					$scope.form_changed = false
					$scope.$apply()
			
			$scope.addTesting = ->
				ajaxStart()
				$scope.adding = true
				$.post "testing/ajaxAdd", 
					Testing: $scope.Testing
				, (response) ->
					console.log response
					redirect "testing/"
				, "json"
					
			angular.element(document).ready ->
				$(".form-change-control").on 'keyup change', 'input, select', ->
					$scope.form_changed = true
					$scope.$apply()

				$scope.changeDate()	if $scope.Testing isnt undefined
				$('#subject-add-student').selectpicker()
				set_scope "Testing"