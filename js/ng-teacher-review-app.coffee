review_statuses =
	0: 'не опубликован'
	1: 'опубликован'
	2: 'отзыв не собирать'
	
angular.module "TeacherReview", ["ui.bootstrap"]
	.filter 'range', () ->
		return (input, total) ->
			total = parseInt total
			for i in [1...total + 1] by 1
				input.push i
			input
	.filter 'hideZero', ->
        (item) ->
            if item > 0 then item else null
	.controller "Reviews", ($scope, $timeout) ->
		$scope.toggleUser = (user_id)->
			user_id = if $scope.Student.id_user_review is $scope.user.id then 0 else $scope.user.id
			$.post "ajax/updateStudentReviewUser", {'student_id' : $scope.Student.id, 'user_id' : user_id}, ->
				user = _.findWhere $scope.users, {id : user_id}
				user_data = {id_user_review: user.id, user_login: user.login, color: user.color}

				_.extend $scope.Student, user_data
				_.extend Review.Student, user_data for Review in $scope.Reviews


				$scope.$apply()

		$scope.enum = review_statuses
		
		$scope.formatDateTime = (date) ->
			moment(date).format "DD.MM.YY в HH:mm"
		
		$scope.yearLabel = (year) ->
			year + '-' + (parseInt(year) + 1) + ' уч. г.'
		
		$scope.refreshCounts = ->
			$timeout ->
				$('.watch-select option').each (index, el) ->
					$(el).data 'subtext', $(el).attr 'data-subtext'
					$(el).data 'content', $(el).attr 'data-content'
			$('.watch-select').selectpicker 'refresh'
			, 100

		$scope.filter = ->
			$.cookie("reviews", JSON.stringify($scope.search), { expires: 365, path: '/' });
			$scope.current_page = 1
			$scope.getByPage($scope.current_page)
		
		# Страница изменилась
		$scope.pageChanged = ->
			console.log $scope.currentPage
			window.history.pushState {}, '', 'reviews/?page=' + $scope.current_page if $scope.current_page > 1
			# Получаем задачи, соответствующие странице и списку
			$scope.getByPage($scope.current_page)
		
		$scope.getByPage = (page) ->
			frontendLoadingStart()
			$.post "ajax/GetReviews",
				page: page
				teachers: $scope.Teachers
				id_student: $scope.id_student
			, (response) ->
				frontendLoadingEnd()
				$scope.Reviews  = response.data
				$scope.counts = response.counts
				$scope.$apply()
				$scope.refreshCounts()
			, "json"
						 
		angular.element(document).ready ->
			set_scope "TeacherReview"
			$scope.search = if $.cookie("reviews") then JSON.parse($.cookie("reviews")) else {}
			$scope.current_page = $scope.currentPage
			$scope.pageChanged()
			$(".single-select").selectpicker()
				
	.controller "Main", ($scope) -> 
		$scope.enum = review_statuses
			
		$scope.RatingInfo = []
		
		$scope.setRating = (field, rating) ->
			if $scope.RatingInfo[field]
				$scope.RatingInfo[field] = 0
			else
				$scope.RatingInfo[field] = rating
		
		$scope.saveReviews = ->
			ajaxStart()
			$.post "reviews/ajax/save",
				RatingInfo: $scope.RatingInfo 
				id_student: $scope.Student.id
				id_subject: $scope.id_subject
				id_teacher: $scope.Teacher.id
				year: $scope.year
			, (response) ->
				$scope.RatingInfo.id = response if not $scope.RatingInfo.id
				ajaxEnd()
				$scope.form_changed = false
				$scope.$apply()
		
		$scope.toggleEnum = (ngModel, status, ngEnum) ->
            statuses = Object.keys(ngEnum)
            status_id = statuses.indexOf ngModel[status].toString()
            status_id++
            status_id = 0 if status_id > (statuses.length - 1)
            ngModel[status] = statuses[status_id]
            $scope.form_changed = true
		
		$scope.form_changed = false
		angular.element(document).ready ->
			set_scope "TeacherReview"

			$(".teacher-review-textarea").on 'keyup change', ->
					$scope.form_changed = true
					$scope.$apply()

			$(".teacher-rating, .ios7-switch").on 'click', ->
					$scope.form_changed = true
					$scope.$apply()
