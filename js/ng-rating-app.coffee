	angular.module "Rating", ['ngSanitize']
		.filter 'reverse', ->
			(items) ->
				if items
					return items.slice().reverse()
		.filter 'unsafe', ($sce) -> 
			$sce.trustAsHtml
		.controller "MainCtrl", ($scope) ->
			$scope.BranchLoad = []
						
			$scope.setRating = (rating_type) ->
				$scope.rating_type = rating_type
# 				$.cookie "rating_type", rating_type, { expires: 365, path: '/' }
				switch rating_type
					when 2 then $scope.data = $scope.data2
					when 3 then $scope.data = $scope.data3
					else $scope.data = $scope.data1
				console.log rating_type, $scope.data
				$scope.$apply()
			
			$scope.addLoad = (id_branch) ->
				$scope.BranchLoad[id_branch] = initIfNotSet($scope.BranchLoad[id_branch])
				$scope.BranchLoad[id_branch].push
					color: 1
					id_branch: id_branch
				$.post "ajax/BranchLoadAdd", {id_branch: id_branch} 
			
			$scope.addLoadFull = (id_branch, grade, id_subject) ->
				$scope.BranchLoad[grade] = initIfNotSet $scope.BranchLoad[grade]
				$scope.BranchLoad[grade][id_subject] = initIfNotSet $scope.BranchLoad[grade][id_subject]
				
				$scope.BranchLoad[grade][id_subject].push
					color: 1
					id_branch: id_branch
					grade: grade
					id_subject: id_subject
				$.post "ajax/BranchLoadAdd", {id_branch: id_branch, grade: grade, id_subject: id_subject}
			
			$scope.addLoadSubject = (id_branch, grade, id_subject) ->
				$scope.BranchLoad[grade] = initIfNotSet $scope.BranchLoad[grade]
				$scope.BranchLoad[grade][id_branch] = initIfNotSet $scope.BranchLoad[grade][id_branch]
				
				$scope.BranchLoad[grade][id_branch].push
					color: 1
					id_branch: id_branch
					grade: grade
					id_subject: id_subject
				$.post "ajax/BranchLoadAdd", {id_branch: id_branch, grade: grade, id_subject: id_subject}
			
			$scope.changeLoad = (id_branch, index) ->
				$.post "ajax/BranchLoadChange", {id_branch: id_branch, index: index}
				if $scope.BranchLoad[id_branch][index].color is 3
					$scope.BranchLoad[id_branch].splice index, 1
				else
					$scope.BranchLoad[id_branch][index].color++
			
			$scope.changeLoadFull = (id_branch, grade, id_subject, index) ->
				$.post "ajax/BranchLoadChangeFull", {id_branch: id_branch, grade: grade, id_subject: id_subject, index: index}
				if $scope.BranchLoad[grade][id_subject][index].color is 3
					$scope.BranchLoad[grade][id_subject].splice index, 1
				else
					$scope.BranchLoad[grade][id_subject][index].color++
			
			$scope.changeLoadSubject = (id_branch, grade, id_subject, index) ->
				$.post "ajax/BranchLoadChangeFull", {id_branch: id_branch, grade: grade, id_subject: id_subject, index: index}
				if $scope.BranchLoad[grade][id_branch][index].color is 3
					$scope.BranchLoad[grade][id_branch].splice index, 1
				else
					$scope.BranchLoad[grade][id_branch][index].color++
			
			$scope.sum = (data) ->
				if data isnt undefined
					total_score = 0
					$.each data, (i, score) ->
						total_score += score
					if $scope.rating_type is 1 then total_score.toFixed 1 else total_score
					
			angular.element(document).ready ->
				set_scope "Rating"
				
				$scope.rating_type = 1
				$scope.setRating $scope.rating_type