ang_scope = false;
app = angular.module "Tendency", []
		.filter 'toArray', ->
			(obj) ->
				arr = []
				$.each obj, (index, value) ->
					arr.push(value)
				return arr
		.controller "IndexCtrl", ($scope, $timeout) ->
            $scope.go = ->
                console.log('go', $scope.search)
                $scope.count = undefined
                $scope.loading = true
                $.post("tendency/AjaxSearch", {search: $scope.search})
                .then (response) ->
                    response = JSON.parse(response)
                    $scope.loading = false
                    $scope.count = response.count
                    $scope.contracts_count = response.contracts_count
                    $scope.$apply()
            angular.element(document).ready ->
                console.log('test')
                $timeout ->
                    $("#subjects").selectpicker
                        noneSelectedText: "предметы"
                        multipleSeparator: '+'
                    $("#grades").selectpicker
                        noneSelectedText: "классы"
                        multipleSeparator: ', '
    				ang_scope = angular.element('[ng-app=Tendency]').scope()
