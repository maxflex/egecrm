app = angular.module "Test", ["ngMap"]
		.filter 'range', () ->
			return (input, total) ->
				total = parseInt total
				for i in [1...total + 1] by 1
					input.push i
				input
		.controller "TmpCtrl", ($scope, $timeout) ->
			$timeout -> $scope.initMap()

			$scope.initMap = ->
				map = new google.maps.Map document.getElementById("gmap"),
				center: new google.maps.LatLng(55.7387, 37.6032)
				scrollwheel: false,
				zoom: 11
				disableDefaultUI: true
				clickableLabels: false
				clickableIcons: false
				zoomControl: true
				zoomControlOptions:
					position: google.maps.ControlPosition.LEFT_BOTTOM
					scaleControl: true
				$scope.Markers.forEach (marker) ->
					marker_location = new google.maps.LatLng(marker.lat, marker.lng)
					new_marker = newMarker(marker.id, marker_location, map, marker.type)
					new_marker.addListener 'click', ->
						window.open('https://lk.ege-repetitor.ru/client/' + marker.markerable_id, '_blank')

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
