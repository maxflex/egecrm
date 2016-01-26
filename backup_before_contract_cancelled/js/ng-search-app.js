	angular.module("Search", ["ngAnimate"])
		.filter('reverse', function() {
			return function(items) {
				return items.slice().reverse();
			};
		})
		.controller("ResultsCtrl", function($scope) {
			setTimeout(function(){
				console.log($scope.requests)
			}, 1000)
		})