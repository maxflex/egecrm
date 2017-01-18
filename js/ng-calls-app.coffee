	app = angular.module "Calls", []
		.config [
		  '$compileProvider'
		  ($compileProvider) ->
		    $compileProvider.aHrefSanitizationWhitelist /^\s*(https?|ftp|mailto|chrome-extension|sip):/
		    # Angular before v1.2 uses $compileProvider.urlSanitizationWhitelist(...)
		    return
		]
		.controller "MissedCtrl", ($scope, $timeout, $http, PhoneService) ->
			bindArguments $scope, arguments

			$timeout ->
				set_scope 'Calls'

			$scope.formatTime = (time) ->
				moment(time * 1000).format "DD.MM.YY в HH:mm"

			$scope.deleteCall = (call) ->
				$.post 'calls/ajax/delete',
					entry_id: call.entry_id
				, ->
					$scope.missed = _.without $scope.missed, call
					$scope.$apply()