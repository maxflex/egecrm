app.requires.push('ngResource');

app.filter('formatDateTime', function() {
  return function(date) {
    return moment(date).format('DD.MM.YY в HH:mm');
  };
}).filter('byYear', function() {
  return function(items, year) {
    return _.where(items, {
      year: year
    });
  };
}).filter('orderObjectBy', function() {
  return function(items, field, reverse) {
    var filtered;
    filtered = [];
    angular.forEach(items, function(item) {
      return filtered.push(item);
    });
    filtered.sort(function(a, b) {
      var ref;
      return (ref = a[field] > b[field]) != null ? ref : {
        1: -1
      };
    });
    if (reverse) {
      filtered.reverse();
    }
    return filtered;
  };
});
