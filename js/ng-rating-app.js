// Generated by CoffeeScript 1.9.3
angular.module("Rating", ['ngSanitize']).filter('reverse', function() {
  return function(items) {
    if (items) {
      return items.slice().reverse();
    }
  };
}).filter('unsafe', function($sce) {
  return $sce.trustAsHtml;
}).controller("MainCtrl", function($scope) {
  $scope.BranchLoad = [];
  $scope.addLoad = function(id_branch) {
    $scope.BranchLoad[id_branch] = initIfNotSet($scope.BranchLoad[id_branch]);
    $scope.BranchLoad[id_branch].push({
      color: 1,
      id_branch: id_branch
    });
    return $.post("ajax/BranchLoadAdd", {
      id_branch: id_branch
    });
  };
  $scope.changeLoad = function(id_branch, index) {
    $.post("ajax/BranchLoadChange", {
      id_branch: id_branch,
      index: index
    });
    if ($scope.BranchLoad[id_branch][index].color === 3) {
      return $scope.BranchLoad[id_branch].splice(index, 1);
    } else {
      return $scope.BranchLoad[id_branch][index].color++;
    }
  };
  return angular.element(document).ready(function() {
    return set_scope("Rating");
  });
});
