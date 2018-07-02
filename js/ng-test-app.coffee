app = angular.module "Test", ["ngMap"]
		.filter 'range', () ->
			return (input, total) ->
				total = parseInt total
				for i in [1...total + 1] by 1
					input.push i
				input
		.controller "Egecentr", ($scope) ->
			$scope.formatDate = (d) ->
				moment(d).format "DD MMM"
			angular.element(document).ready ->
				set_scope "Test"
				$.post "ajax/Egecentr", {}, (response) ->
					$scope.data_2014 = response
					$scope.$apply()
				, "json"
		.controller "MapCtrl", ($scope) ->
			$scope.$on 'mapInitialized', (event, map) ->
				map.setCenter MAP_CENTER

				google.maps.event.addListener map, 'click', (event) ->
					marker = addMarker map, event.latLng
					getDistance event.latLng, (response) ->
						$scope.data = response
						$scope.$apply()
		.controller "MapNewCtrl", ($scope) ->
			markers = []

			unsetAllMarkers = ->
				console.log 'unsetting', markers
				$.each markers, (index, marker) ->
					marker.setMap null
				markers = []

			setClosestMetroMarkers = (data, map) ->
				$.each data, (index, metro) ->
					marker = addMarker map, new google.maps.LatLng(metro.lat, metro.lng), ICON_SEARCH
					markers.push marker

			$scope.$on 'mapInitialized', (event, map) ->
				map.setCenter MAP_CENTER

				google.maps.event.addListener map, 'click', (event) ->
					unsetAllMarkers()
					marker = addMarker map, event.latLng
					markers.push marker
					getDistance2 event.latLng, (response) ->
						$scope.data = response
						setClosestMetroMarkers(response, map)
						$scope.$apply()
