app = angular.module "Journal", []
	.controller "StudentsCtrl", ($scope) ->
		$scope.getGroup = (id) ->
		  _.findWhere $scope.Groups, id: parseInt(id)
		
		# получить группы из журнала
		
		$scope.getJournalGroups = ->
		  Object.keys _.chain($scope.Journal).groupBy('id_group').value()
		
		$scope.getVisitsByGroup = (id_group) ->
		  id_group = parseInt(id_group)
		  _.where $scope.Journal, id_group: id_group

		$scope.getScheduleByDate = (id_group, lesson_date) ->
			return _.findWhere $scope.getGroup(id_group).Schedule, date: lesson_date



		$scope.inActiveGroup = (id_group) ->
		  id_group = parseInt(id_group)
		  _.where($scope.Groups, id: id_group).length
		
		$scope.getMaxVisits = ->
		  max = -1
		  $.each $scope.Groups, (i, group) ->
		    count = $scope.getVisitsByGroup(group.id).length
		    if $scope.getGroup(group.id).Schedule
		      count += $scope.getGroup(group.id).Schedule.length
		    if count > max
		      max = count
		    return
		  max
		
		$scope.toggleMissingNote = (Schedule) ->
			note = Schedule.missing_note
			note++
			ajaxStart()
			$.post 'ajax/MissingNoteToggle', {
				id_student: $scope.student.id
				id_group: Schedule.id_group
				date: if Schedule.hasOwnProperty('lesson_date') then Schedule.lesson_date else Schedule.date
			}, ((response) ->
				ajaxEnd()
				Schedule.missing_note = response
				$scope.$apply()
				return
			), 'json'
			return
		
		$scope.formatVisitDate = (date) ->
			moment(date).format 'DD.MM.YY'
		
		angular.element(document).ready ->
			set_scope "Journal"