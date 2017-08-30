var app;

app = angular.module("Map", ["ui.bootstrap"]).filter('toArray', function() {
  return function(obj) {
    var arr;
    arr = [];
    $.each(obj, function(index, value) {
      return arr.push(value);
    });
    return arr;
  };
}).controller("IndexCtrl", function($scope, UserService, $timeout, $http) {
  bindArguments($scope, arguments);
  $timeout(function() {
    $scope.search = {
      year: '2017'
    };
    $("#include-branches").selectpicker({
      noneSelectedText: "включить филиалы"
    }).selectpicker('refresh');
    $("#exclude-branches").selectpicker({
      noneSelectedText: "исключить филиалы"
    }).selectpicker('refresh');
    $("#subjects-select").selectpicker({
      noneSelectedText: "предметы",
      multipleSeparator: ', '
    }).selectpicker('refresh');
    $(".search-grades").selectpicker({
      noneSelectedText: "классы",
      multipleSeparator: ', '
    }).selectpicker('refresh');
    return $scope.initMap();
  });
  $scope.yearLabel = function(year) {
    return year + '-' + (parseInt(year) + 1) + ' уч. г.';
  };
  $scope.initMap = function() {
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
        position: google.maps.ControlPosition.LEFT_BOTTOM
      },
      scaleControl: true
    });
    $.cookie("map", JSON.stringify($scope.search), {
      expires: 1,
      path: '/'
    });
    return $http.get('map/markers').then(function(response) {
      console.log(response.data);
      return response.data.markers.forEach(function(marker) {
        var infowindow, marker_location, new_marker;
        marker_location = new google.maps.LatLng(marker.lat, marker.lng);
        new_marker = newMarker(marker.id, marker_location, map, marker.type);
        infowindow = new google.maps.InfoWindow({
          content: marker.last_name + ' ' + marker.first_name[0] + '. ' + marker.middle_name[0] + '.'
        });
        new_marker.addListener('mouseover', function() {
          return infowindow.open(map, this);
        });
        new_marker.addListener('mouseout', function() {
          return infowindow.close();
        });
        return new_marker.addListener('click', function() {
          return window.open('https://lk.ege-centr.ru/student/' + marker.id_owner, '_blank');
        });
      });
    });
  };
  return angular.element(document).ready(function() {
    return set_scope("Map");
  });
});
