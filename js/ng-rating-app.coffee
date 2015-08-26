	angular.module "Rating", ['ngSanitize']
		.filter 'reverse', ->
			(items) ->
				if items
					return items.slice().reverse()
		.filter 'unsafe', ($sce) -> 
			$sce.trustAsHtml
		.controller "MainCtrl", ($scope) ->
			$scope.BranchLoad = []
			$scope.addLoad = (id_branch) ->
				$scope.BranchLoad[id_branch] = initIfNotSet($scope.BranchLoad[id_branch])
				$scope.BranchLoad[id_branch].push
					color: 1
					id_branch: id_branch
				$.post "ajax/BranchLoadAdd", {id_branch: id_branch} 
			$scope.changeLoad = (id_branch, index) ->
				$.post "ajax/BranchLoadChange", {id_branch: id_branch, index: index}
				if $scope.BranchLoad[id_branch][index].color is 3
					$scope.BranchLoad[id_branch].splice index, 1
				else
					$scope.BranchLoad[id_branch][index].color++
				
			angular.element(document).ready ->
				set_scope "Rating"