var app;

app = angular.module("Test", ["ngMap"]).filter('range', function() {
  return function(input, total) {
    var i, j, ref;
    total = parseInt(total);
    for (i = j = 1, ref = total + 1; j < ref; i = j += 1) {
      input.push(i);
    }
    return input;
  };
}).controller("TmpCtrl", function($scope, $timeout) {
  $timeout(function() {
    return $scope.initMap();
  });
  return $scope.initMap = function() {
    var map;
    map = new google.maps.Map(document.getElementById("gmap"), {
      center: new google.maps.LatLng(55.7387, 37.6032),
      scrollwheel: false,
      zoom: 11,
      disableDefaultUI: true,
      clickableLabels: false,
      clickableIcons: false,
      zoomControl: true,
      zoomControlOptions: {
        position: google.maps.ControlPosition.LEFT_BOTTOM,
        scaleControl: true
      }
    });
    return $scope.Markers.forEach(function(marker) {
      var marker_location, new_marker;
      marker_location = new google.maps.LatLng(marker.lat, marker.lng);
      new_marker = newMarker(marker.id, marker_location, map, marker.type);
      return new_marker.addListener('click', function() {
        return window.open('https://lk.ege-repetitor.ru/client/' + marker.markerable_id, '_blank');
      });
    });
  };
}).controller("Egecentr", function($scope) {
  $scope.formatDate = function(d) {
    return moment(d).format("DD MMM");
  };
  return angular.element(document).ready(function() {
    set_scope("Test");
    return $.post("ajax/Egecentr", {}, function(response) {
      $scope.data_2014 = response;
      return $scope.$apply();
    }, "json");
  });
}).controller("MapCtrl", function($scope) {
  return $scope.$on('mapInitialized', function(event, map) {
    map.setCenter(MAP_CENTER);
    return google.maps.event.addListener(map, 'click', function(event) {
      var marker;
      marker = addMarker(map, event.latLng);
      return getDistance(event.latLng, function(response) {
        $scope.data = response;
        return $scope.$apply();
      });
    });
  });
}).controller("MapNewCtrl", function($scope) {
  var markers, setClosestMetroMarkers, unsetAllMarkers;
  markers = [];
  unsetAllMarkers = function() {
    console.log('unsetting', markers);
    $.each(markers, function(index, marker) {
      return marker.setMap(null);
    });
    return markers = [];
  };
  setClosestMetroMarkers = function(data, map) {
    return $.each(data, function(index, metro) {
      var marker;
      marker = addMarker(map, new google.maps.LatLng(metro.lat, metro.lng), ICON_SEARCH);
      return markers.push(marker);
    });
  };
  return $scope.$on('mapInitialized', function(event, map) {
    map.setCenter(MAP_CENTER);
    return google.maps.event.addListener(map, 'click', function(event) {
      var marker;
      unsetAllMarkers();
      marker = addMarker(map, event.latLng);
      markers.push(marker);
      return getDistance2(event.latLng, function(response) {
        $scope.data = response;
        setClosestMetroMarkers(response, map);
        return $scope.$apply();
      });
    });
  });
});
