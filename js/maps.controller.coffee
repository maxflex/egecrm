	ICON_SCHOOL = 
		url: "img/maps/schoolpin.png"
		scaledSize: new google.maps.Size(22,40)
		origin: new google.maps.Point(0,0)
	
	ICON_SCHOOL_BLUE = 
		url: "img/maps/schoolpin_blue.png"
		scaledSize: new google.maps.Size(22,40)
		origin: new google.maps.Point(0,0)
	
	ICON_HOME = 
		url: "img/maps/homepin.png",
		scaledSize: new google.maps.Size(22,40), 
		origin: new google.maps.Point(0,0)
	
	ICON_HOME_BLUE = 
		url: "img/maps/homepin_blue.png",
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
	addMarker = (map, latLng, icon = undefined) ->
		new google.maps.Marker
			map: map
			position: latLng
			icon: icon
	
	infoWindow = (contentString) ->
		new google.maps.InfoWindow
		    content: contentString
	
	# GEO
	getDistance = (latLng, callback) ->
		$.get "metro/getDistance", 
			lat: latLng.lat()
			lng: latLng.lng(),
			(response) ->
				callback(response)
		, "json"
	
	# GEO2
	getDistance2 = (latLng, callback) ->
		$.get "metro/getDistance2", 
			lat: latLng.lat()
			lng: latLng.lng(),
			(response) ->
				callback(response)
		, "json"
			