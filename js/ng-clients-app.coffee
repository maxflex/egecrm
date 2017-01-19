app = angular.module "Clients", ["ui.bootstrap"]
	.filter 'to_trusted', ['$sce', ($sce) ->
        return (text) ->
            return $sce.trustAsHtml(text)
	]
	.filter 'hideZero', ->
        (item) ->
            if item > 0 then item else null
	.filter 'toArray', ->
		(obj) ->
			arr = []
			$.each obj, (index, value) ->
				arr.push(value)
			return arr
	.controller "ListCtrl", ($scope, $timeout, PhoneService) ->
		bindArguments $scope, arguments
		$scope.yearLabel = (year) ->
			'договоры на ' + year + '-' + (parseInt(year) + 1) + ' год'
		
		$scope.getNumber = (index) ->
			(($scope.current_page - 1) * 30) + (index + 1)
		
		$scope.refreshCounts = ->
			$timeout ->
				$('.watch-select option').each (index, el) ->
					$(el).data 'subtext', $(el).attr 'data-subtext'
					$(el).data 'content', $(el).attr 'data-content'
				$('.watch-select').selectpicker 'refresh'
			, 100

		$scope.filter = ->
			$.cookie("clients", JSON.stringify($scope.search), { expires: 365, path: '/' });
			$scope.current_page = 1
			$scope.getByPage($scope.current_page)
		
		# Страница изменилась
		$scope.pageChanged = ->
			window.history.pushState {}, '', 'clients/?page=' + $scope.current_page if $scope.current_page > 1
			# Получаем задачи, соответствующие странице и списку
			$scope.getByPage($scope.current_page)
		
		$scope.getByPage = (page) ->
			frontendLoadingStart()
			$.post "clients/ajax/GetStudents",
				page: page
			, (response) ->
				frontendLoadingEnd()
				$scope.Students  = response.data
				$scope.counts = response.counts
				$scope.$apply()
				$scope.refreshCounts()
			, "json"
						 
		angular.element(document).ready ->
			set_scope "Clients"
			$scope.search = if $.cookie("clients") then JSON.parse($.cookie("clients")) else {}
			$scope.current_page = $scope.currentPage
			$scope.pageChanged()
			$(".single-select").selectpicker()
			
		$scope.to_students = true
		$scope.to_representatives = false