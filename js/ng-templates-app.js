var app;

app = angular.module("Templates", []).controller("ListCtrl", function($scope) {
  $scope.mode = 1;
  $scope.getTemplates = function() {
    return _.where($scope.Templates, {
      type: $scope.mode
    });
  };
  $scope.toggle = function(n, Template) {
    var index;
    index = Template.who.indexOf(n);
    if (index === -1) {
      console.log(index, 'here');
      return Template.who.push(n);
    } else {
      return Template.who.splice(index, 1);
    }
  };
  $scope.inWho = function(n, Template) {
    var index;
    index = Template.who.indexOf(n);
    return index !== -1;
  };
  $scope.save = function() {
    ajaxStart();
    return $.post("templates/ajax/save", {
      templates: $scope.Templates
    }, function() {
      $scope.form_changed = false;
      $scope.$apply();
      return ajaxEnd();
    });
  };
  $scope.form_changed = false;
  return angular.element(document).ready(function() {
    set_scope("Templates");
    return $("[ng-app='Templates']").on('keyup change', 'input, select, textarea', function() {
      $scope.form_changed = true;
      return $scope.$apply();
    });
  });
});
