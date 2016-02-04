angular.module "TeacherReview", []
	.filter 'range', () ->
		return (input, total) ->
			total = parseInt total
			for i in [1...total + 1] by 1
				input.push i
			input
	.controller "Main", ($scope) ->
		console.log "here"
		$scope.RatingInfo = []

		$scope.saveReviews = ->
			ajaxStart()
			$.post "reviews/ajax/save", {RatingInfo: $scope.RatingInfo, id_student: $scope.id_student}, (response) ->
				ajaxEnd()
				$scope.form_changed = false
				$scope.$apply()

		$scope.form_changed = false
		angular.element(document).ready ->
			set_scope "TeacherReview"

			$(".teacher-review-textarea").on 'keyup change', ->
					$scope.form_changed = true
					$scope.$apply()

			$(".teacher-rating, .ios7-switch").on 'click', ->
					$scope.form_changed = true
					$scope.$apply()
