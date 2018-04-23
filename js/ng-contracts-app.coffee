app = angular.module "Contracts", ["ui.bootstrap"]
	.filter 'to_trusted', ['$sce', ($sce) ->
		return (text) ->
			return $sce.trustAsHtml(text)
	]
	.filter 'hideZero', ->
		(item) ->
			if item > 0 then item else null
	.controller "ListCtrl", ($scope, $timeout) ->
		$scope.getNumber = (index) ->
			(($scope.current_page - 1) * 30) + (index + 1)

		$timeout ->
			$('.watch-select option').each (index, el) ->
				$(el).data 'subtext', $(el).attr 'data-subtext'
				$(el).data 'content', $(el).attr 'data-content'
			$('.watch-select').selectpicker 'refresh'
		, 500


		$scope.yearLabel = (year) ->
			year + '-' + (parseInt(year) + 1) + ' уч. г.'

		$scope.filter = ->
			$.cookie("contracts", JSON.stringify($scope.search), { expires: 365, path: '/' });
			$scope.current_page = 1
			$scope.getByPage($scope.current_page)

		$scope.pageChanged = ->
			window.history.pushState {}, '', 'contracts/?page=' + $scope.current_page if $scope.current_page > 1
			$scope.getByPage($scope.current_page)

		$scope.getByPage = (page) ->
			frontendLoadingStart()
			$.post "contracts/ajax/GetContracts",
				page: page
			, (response) ->
				frontendLoadingEnd()
				$scope.Contracts  = response.data
				$scope.counts     = response.counts
				$scope.$apply()
			, "json"

		$scope.keyFilter = (event) ->
			$scope.filter() if event.keyCode is 13

		$scope.getDiscountedPrice = (price, discount) ->
			Math.round(price - (price * (discount / 100)))

		$scope.getContractSum = (contract) ->
			return 0 if not contract

			if contract.discount > 0
				return $scope.getDiscountedPrice(contract.sum, contract.discount)
			else
				return contract.sum

		angular.element(document).ready ->
			set_scope "Contracts"
			$scope.search = if $.cookie("contracts") then JSON.parse($.cookie("contracts")) else {}
			$scope.current_page = $scope.currentPage
			$scope.pageChanged()
