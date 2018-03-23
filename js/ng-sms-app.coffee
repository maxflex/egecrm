app = angular.module "Sms", ["ui.bootstrap"]
	.controller "Main", ($scope, $element, $http, PhoneService) ->
		bindArguments $scope, arguments

		$scope.getByPage = (page) ->
			frontendLoadingStart()
			$.post 'sms/ajax/history',
				page: page
				search: $scope.search
			, (response) ->
				frontendLoadingEnd()
				$scope.data = response.data
				$scope.total = response.total
				$scope.$apply()
			, 'json'

		$scope.filter = _.debounce ->
			console.log('debounced')
			$scope.current_page = 1
			$scope.getByPage($scope.current_page)
		, 150

		# Страница изменилась
		$scope.pageChanged = ->
			window.history.pushState {}, '', 'clients/?page=' + $scope.current_page if $scope.current_page > 1
			# Получаем задачи, соответствующие странице и списку
			$scope.getByPage($scope.current_page)

		angular.element(document).ready ->
			setTimeout ->
				$('#entity-phone-phone').attr('placeholder', 'отправить СМС')
			, 300
			$scope.current_page = $scope.currentPage
			$scope.pageChanged()
			set_scope "Sms"
