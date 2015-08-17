	angular.module "Group", []
		.controller "EditCtrl", ($scope) ->
			
			$scope.countSubjects = (Contract) ->
				Object.keys(Contract.subjects).length
			
			$scope.addStudent = (id_student) ->
				if id_student not in $scope.Group.students
					$scope.Group.students.push(id_student)
					$scope.form_changed = true
			
			$scope.removeStudent = (id_student) ->
				$.each $scope.Group.students, (index, data) ->
					if data is id_student
						console.log data, index
						$scope.Group.students.splice index, 1
						$scope.form_changed = true
						$scope.$apply()
							
			$scope.studentAdded = (id_student) ->
				id_student in $scope.Group.students
			
			$scope.getStudent = (id_student) ->
				Student = (i for i in $scope.Students when i.id is id_student)[0]
				Student.last_name + " " + Student.first_name + " " + Student.middle_name
			
			$scope.search = 
				grade: ""
				id_branch: ""
				id_subject: ""
				
			$scope.clientsFilter = (Student) ->
				return (Student.Contract.grade is parseInt($scope.search.grade) or not $scope.search.grade) and 
					(parseInt($scope.search.id_branch) in Student.branches or not $scope.search.id_branch) and
					(Student.Contract.subjects and (parseInt($scope.search.id_subject) of Student.Contract.subjects or not $scope.search.id_subject))
			
			angular.element(document).ready ->
				set_scope "Group"
				frontendLoadingEnd()
				
			$(document).ready ->
				$("#group-edit").on 'keyup change', 'input, select, textarea', ->
					$scope.form_changed = true
					$scope.$apply()
			
			$(".save-button").on "click", ->
					ajaxStart()
					$scope.saving = true
					$scope.$apply()
					
					$.post "groups/ajax/save", $scope.Group, (response) ->
						if $scope.Group.id
							ajaxEnd()
							$scope.saving = false
							$scope.form_changed = false
							$scope.$apply()
						else
							redirect "groups/edit/#{response}"	
						
		.controller "ListCtrl", ($scope) ->
			