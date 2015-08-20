	angular.module "Test", ["ngMap"]
		.controller "MapCtrl", ($scope) ->
			$scope.$on 'mapInitialized', (event, map) ->
				map.setCenter MAP_CENTER
				
				google.maps.event.addListener map, 'click', (event) ->
					marker = addMarker map, event.latLng
					getDistance event.latLng, (response) ->
						$scope.data = response
						$scope.$apply()