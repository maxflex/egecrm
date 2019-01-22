	app = angular.module "Calls", []
		.config [
		  '$compileProvider'
		  ($compileProvider) ->
		    $compileProvider.aHrefSanitizationWhitelist /^\s*(https?|ftp|mailto|chrome-extension|sip|tel):/
		    # Angular before v1.2 uses $compileProvider.urlSanitizationWhitelist(...)
		    return
		]
		.controller "MissedCtrl", ($scope, $timeout, $http, PhoneService) ->
			bindArguments $scope, arguments

			$timeout ->
				set_scope 'Calls'

			$scope.formatTime = (time) ->
				moment(time * 1000).format "DD.MM.YY в HH:mm"

			$scope.callDuration = (seconds) ->
				return '' if not seconds
				format = 's сек'
				format = 'm мин ' + format if seconds > 60
				format = 'H час ' + format if seconds > 3600
				# format = if minutes >= 60 then 'H час m мин' else 'm мин'
				moment.duration(seconds, 'seconds').format(format)

			$scope.deleteCall = (call) ->
				$.post 'calls/ajax/delete',
					entry_id: call.entry_id
				, ->
					$scope.missed = _.without $scope.missed, call
					$scope.$apply()
