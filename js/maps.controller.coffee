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
	
	# Функция создания маркера
	newMarker = (id, latLng, map, type = 'school') ->
		new google.maps.Marker
		    map: map
		    position: latLng
		    icon: if type is 'school' then ICON_SCHOOL else ICON_HOME
		    id: id
		    type: type