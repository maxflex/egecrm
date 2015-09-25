	angular.module("Notification", ["ngAnimate"])
		.filter('reverse', function() {
			return function(items) {
				return items.slice().reverse();
			};
		})
		.controller("ListCtrl", function($scope) {
			// Сколько от сейчас
			$scope.fromNow = function(timestamp) {
				moment.locale('ru');

				date = moment.unix(timestamp).format("YYYY-MM-DD HH:mm");
				
				return moment().to(date);
			}
		})