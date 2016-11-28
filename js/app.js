app.requires.push('ngResource');

app.filter('formatDateTime', function() {
  return function(date) {
    return moment(date).format('DD.MM.YY в HH:mm');
  };
});
