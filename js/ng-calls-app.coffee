	angular.module "Calls", []
		.config [
		  '$compileProvider'
		  ($compileProvider) ->
		    $compileProvider.aHrefSanitizationWhitelist /^\s*(https?|ftp|mailto|chrome-extension|sip):/
		    # Angular before v1.2 uses $compileProvider.urlSanitizationWhitelist(...)
		    return
		]
		.controller "MissedCtrl", ($scope, $timeout, $http) ->
			$timeout ->
				set_scope 'Calls'
				
			$scope.sipNumber = (number) ->
				number = number.toString() 
				return "sip:" + number.replace(/[^0-9]/g, '')

			$scope.callSip = (number) ->
				number = $scope.sipNumber(number)
				location.href = number
			
			$scope.formatTime = (time) ->
				moment(time * 1000).format "DD.MM.YY в HH:mm"