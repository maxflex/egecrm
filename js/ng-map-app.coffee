app = angular.module "Map", ["ui.bootstrap"]
	.filter 'toArray', ->
		(obj) ->
			arr = []
			$.each obj, (index, value) ->
				arr.push(value)
			return arr
	.controller "IndexCtrl", ($scope, UserService, $timeout, $http) ->
		bindArguments $scope, arguments
		$timeout ->
			$scope.search = {year: '2017'}
			$("#include-branches").selectpicker({noneSelectedText: "включить филиалы"}).selectpicker('refresh')
			$("#exclude-branches").selectpicker({noneSelectedText: "исключить филиалы"}).selectpicker('refresh')
			$("#subjects-select").selectpicker({noneSelectedText: "предметы", multipleSeparator: ', '}).selectpicker('refresh')
			$(".search-grades").selectpicker({noneSelectedText: "классы", multipleSeparator: ', '}).selectpicker('refresh')
			$scope.initMap()

		$scope.yearLabel = (year) ->
			year + '-' + (parseInt(year) + 1) + ' уч. г.'

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
			$.cookie("map", JSON.stringify($scope.search), { expires: 1, path: '/' });
			$http.get('map/markers').then (response) ->
                console.log(response.data)
                response.data.markers.forEach (marker) ->
                    marker_location = new google.maps.LatLng(marker.lat, marker.lng)
                    new_marker = newMarker(marker.id, marker_location, map, marker.type)


		angular.element(document).ready ->
			set_scope "Map"