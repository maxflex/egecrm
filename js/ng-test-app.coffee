	angular.module "Test", ["ngMap"]
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
		.controller "ClientsMapCtrl", ($scope) ->
			$scope.filters =
				branches_invert: [],
				branches: [],
				subjects: [],
				grades: [],
				marker_home: true,
				marker_school: true,
			
			$scope.markers = []
			$scope.info_windows = []
			
			$scope.inArray = (id_branch, arr) ->
				console.log parseInt(id_branch), arr
				parseInt(id_branch) in arr
			
			$scope.toggleFilter = (filter, id, index) ->
				id = parseInt id
				switch filter
					when "branch_invert" 
						if id in $scope.filters.branches_invert
							$scope.filters.branches_invert.splice $.inArray(id, $scope.filters.branches_invert), 1
						else
							$scope.filters.branches_invert.push id
					when "branch" 
						if id in $scope.filters.branches
							$scope.filters.branches.splice $.inArray(id, $scope.filters.branches), 1
						else
							$scope.filters.branches.push id
					when "grade" 
						if id in $scope.filters.grades
							$scope.filters.grades.splice $.inArray(id, $scope.filters.grades), 1
						else
							$scope.filters.grades.push id
					when "subject" 
						if id in $scope.filters.subjects
							$scope.filters.subjects.splice $.inArray(id, $scope.filters.subjects), 1
						else
							$scope.filters.subjects.push id
				$scope.runRequest()
			
			$scope.runRequest = ->
				frontendLoadingStart()
				$.get "ajax/clientsMap", $scope.filters, (response) ->
					frontendLoadingEnd()
					for marker in $scope.markers
						marker.setMap null
					$scope.markers = []
					$scope.info_windows = []
					if response
						for Student in response
							continue if Student.markers is undefined 
							for marker in Student.markers
								if marker.type is "school" and not $scope.filters.marker_school then continue
								if marker.type is "home" and not $scope.filters.marker_home then continue
								new_marker = newMarker marker.id,
									new google.maps.LatLng marker.lat, marker.lng
									$scope.gmap
									marker.type
								
								new_marker.id_owner = marker.id_owner
								
								$scope.markers.push new_marker
								
								$scope.info_windows[new_marker.id] = new google.maps.InfoWindow
									content: """
										<a target='_blank' href='student/#{Student.id}'>#{Student.last_name} #{Student.first_name} #{Student.middle_name}</a>
										<div>#{Student.Contract.grade} класс, #{Student.subjects_string}</div>
										<div>#{Student.branches_string}</div>
										"""
								new_marker.setMap $scope.gmap
								new_marker.addListener "click", ->
									
									if $scope.prev_info_window_id isnt undefined and $scope.info_windows[$scope.prev_info_window_id] isnt undefined
										$scope.info_windows[$scope.prev_info_window_id].close()
									
									$scope.prev_info_window_id = this.id
									$scope.info_windows[this.id].open $scope.gmap, this
									for m in $scope.markers
										if m.id_owner is this.id_owner
											m.setMap null
											if m.type is "school"
												m.type = "school_blue"
												m.icon = ICON_SCHOOL_BLUE
											else
												m.type = "home_blue"
												m.icon = ICON_HOME_BLUE
											m.setMap($scope.gmap)
										else
											switch m.type 
												when "home_blue"
													m.type = "home"
													m.icon = ICON_HOME
													m.setMap null
													m.setMap $scope.gmap
												when "school_blue"
													m.type = "home"
													m.icon = ICON_SCHOOL
													m.setMap null
													m.setMap $scope.gmap
					$scope.$apply()
				, "json"
			
			$scope.$on 'mapInitialized', (event, map) ->
				map.setCenter MAP_CENTER
				$scope.gmap = map
			
			angular.element(document).ready ->
				set_scope "Test"
								
# 				google.maps.event.addListener map, 'click', (event) ->
# 					marker = addMarker map, event.latLng
# 					getDistance event.latLng, (response) ->
# 						$scope.data = response
# 						$scope.$apply()