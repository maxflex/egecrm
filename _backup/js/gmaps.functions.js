
	// Функция создания маркера
	function newMarker(id, type, latLng) {
		return new google.maps.Marker({
					"position"	: latLng,
					"type"		: type,
					"id"		: id
				});
	}