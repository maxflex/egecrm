app = angular.module "Activity", []
	.controller "IndexCtrl", ($scope, $http, $timeout, UserService) ->
		$scope.UserService = UserService

		$timeout ->
			set_scope 'Activity'
			$scope.search  = {}
			$scope.refreshCounts()

		$scope.formatMinutes = (minutes) ->
			format = if minutes >= 60 then 'H час m мин' else 'm мин'
			moment.duration(minutes, 'minutes').format(format)

		$scope.refreshCounts = ->
			$timeout ->
				$('.selectpicker option').each (index, el) ->
					$(el).data 'subtext', $(el).attr 'data-subtext'
					$(el).data 'content', $(el).attr 'data-content'
				$('.selectpicker').selectpicker 'refresh'
			, 600

		$scope.show = ->
			# $scope.frontend_loading = true
			$http.get "activity/get/#{$scope.search.user_id}/#{$scope.search.date}"
				.then (response) ->
					# $scope.frontend_loading = false
					$scope.data = response.data