	ICON_SCHOOL = 
		url: "img/maps/schoolpin.png"
		scaledSize: new google.maps.Size(22,40)
		origin: new google.maps.Point(0,0)
	
	ICON_HOME = 
		url: "img/maps/homepin.png",
		scaledSize: new google.maps.Size(22,40), 
		origin: new google.maps.Point(0,0)
		
	ICON_SEARCH =
		url: "img/maps/bluepin.png",
		scaledSize: new google.maps.Size(22,40)
		origin: new google.maps.Point(0,0)
	
	INIT_COORDS = 
		lat: 55.7387
		lng: 37.6032
	
	MAP_CENTER = new google.maps.LatLng 55.7387, 37.6032
	
	RECOM_BOUNDS = 
		new google.maps.LatLngBounds \
			new google.maps.LatLng INIT_COORDS.lat-0.5, INIT_COORDS.lng-0.5, \
			new google.maps.LatLng INIT_COORDS.lat+0.5, INIT_COORDS.lng+0.5
	
	# Функция создания маркера
	newMarker = (id, latLng, map, type = 'school') ->
		new google.maps.Marker
		    map: map
		    position: latLng
		    icon: if type is 'school' then ICON_SCHOOL else ICON_HOME
		    id: id
		    type: type
			
	# Просто добавляет метку, без всякого функционала
	addMarker = (map, latLng) ->
		new google.maps.Marker
			map: map
			position: latLng
	
	
	# GEO
	getDistance = (latLng) ->
		$.get "metro/getDistance", 
			lat: latLng.lat()
			lng: latLng.lng(),
			(response) ->
				console.log response
			