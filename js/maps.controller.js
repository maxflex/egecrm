var ICON_HOME, ICON_HOME_BLUE, ICON_SCHOOL, ICON_SCHOOL_BLUE, ICON_SEARCH, INIT_COORDS, MAP_CENTER, RECOM_BOUNDS, addMarker, getDistance, getDistance2, infoWindow, newMarker;

ICON_SCHOOL = {
  url: "img/maps/schoolpin.png",
  scaledSize: new google.maps.Size(22, 40),
  origin: new google.maps.Point(0, 0)
};

ICON_SCHOOL_BLUE = {
  url: "img/maps/schoolpin_blue.png",
  scaledSize: new google.maps.Size(22, 40),
  origin: new google.maps.Point(0, 0)
};

ICON_HOME = {
  url: "img/maps/homepin.png",
  scaledSize: new google.maps.Size(22, 40),
  origin: new google.maps.Point(0, 0)
};

ICON_HOME_BLUE = {
  url: "img/maps/homepin_blue.png",
  scaledSize: new google.maps.Size(22, 40),
  origin: new google.maps.Point(0, 0)
};

ICON_SEARCH = {
  url: "img/maps/bluepin.png",
  scaledSize: new google.maps.Size(22, 40),
  origin: new google.maps.Point(0, 0)
};

INIT_COORDS = {
  lat: 55.7387,
  lng: 37.6032
};

MAP_CENTER = new google.maps.LatLng(55.7387, 37.6032);

RECOM_BOUNDS = new google.maps.LatLngBounds(new google.maps.LatLng(INIT_COORDS.lat - 0.5, INIT_COORDS.lng - 0.5, new google.maps.LatLng(INIT_COORDS.lat + 0.5, INIT_COORDS.lng + 0.5)));

newMarker = function(id, latLng, map, type) {
  if (type == null) {
    type = 'school';
  }
  return new google.maps.Marker({
    map: map,
    position: latLng,
    icon: type === 'school' ? ICON_SCHOOL : ICON_HOME,
    id: id,
    type: type
  });
};

addMarker = function(map, latLng, icon) {
  if (icon == null) {
    icon = void 0;
  }
  return new google.maps.Marker({
    map: map,
    position: latLng,
    icon: icon
  });
};

infoWindow = function(contentString) {
  return new google.maps.InfoWindow({
    content: contentString
  });
};

getDistance = function(latLng, callback) {
  return $.get("metro/getDistance", {
    lat: latLng.lat(),
    lng: latLng.lng()
  }, function(response) {
    return callback(response);
  }, "json");
};

getDistance2 = function(latLng, callback) {
  return $.get("metro/getDistance2", {
    lat: latLng.lat(),
    lng: latLng.lng()
  }, function(response) {
    return callback(response);
  }, "json");
};
