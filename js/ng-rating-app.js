var app;

app = angular.module("Rating", ['ngSanitize']).filter('reverse', function() {
  return function(items) {
    if (items) {
      return items.slice().reverse();
    }
  };
}).filter('unsafe', function($sce) {
  return $sce.trustAsHtml;
}).controller("MainCtrl", function($scope) {
  $scope.BranchLoad = [];
  $scope.setRating = function(rating_type) {
    $scope.rating_type = rating_type;
    switch (rating_type) {
      case 2:
        $scope.data = $scope.data2;
        break;
      case 3:
        $scope.data = $scope.data3;
        break;
      default:
        $scope.data = $scope.data1;
    }
    console.log(rating_type, $scope.data);
    return $scope.$apply();
  };
  $scope.addLoad = function(id_branch) {
    $scope.BranchLoad[id_branch] = initIfNotSet($scope.BranchLoad[id_branch]);
    $scope.BranchLoad[id_branch].push({
      color: 1,
      id_branch: id_branch
    });
    ajaxStart();
    return $.post("ajax/BranchLoadAdd", {
      id_branch: id_branch
    }, function() {
      return ajaxEnd();
    });
  };
  $scope.addLoadFull = function(id_branch, grade, id_subject) {
    $scope.BranchLoad[grade] = initIfNotSet($scope.BranchLoad[grade]);
    $scope.BranchLoad[grade][id_subject] = initIfNotSet($scope.BranchLoad[grade][id_subject]);
    $scope.BranchLoad[grade][id_subject].push({
      color: 1,
      id_branch: id_branch,
      grade: grade,
      id_subject: id_subject
    });
    ajaxStart();
    return $.post("ajax/BranchLoadAdd", {
      id_branch: id_branch,
      grade: grade,
      id_subject: id_subject
    }, function() {
      return ajaxEnd();
    });
  };
  $scope.addLoadSubject = function(id_branch, grade, id_subject) {
    $scope.BranchLoad[grade] = initIfNotSet($scope.BranchLoad[grade]);
    $scope.BranchLoad[grade][id_branch] = initIfNotSet($scope.BranchLoad[grade][id_branch]);
    $scope.BranchLoad[grade][id_branch].push({
      color: 1,
      id_branch: id_branch,
      grade: grade,
      id_subject: id_subject
    });
    ajaxStart();
    return $.post("ajax/BranchLoadAdd", {
      id_branch: id_branch,
      grade: grade,
      id_subject: id_subject
    }, function() {
      return ajaxEnd();
    });
  };
  $scope.changeLoad = function(id_branch, index) {
    ajaxStart();
    $.post("ajax/BranchLoadChange", {
      id_branch: id_branch,
      index: index
    }, function() {
      return ajaxEnd();
    });
    if ($scope.BranchLoad[id_branch][index].color === 3) {
      return $scope.BranchLoad[id_branch].splice(index, 1);
    } else {
      return $scope.BranchLoad[id_branch][index].color++;
    }
  };
  $scope.changeLoadFull = function(id_branch, grade, id_subject, index) {
    ajaxStart();
    $.post("ajax/BranchLoadChangeFull", {
      id_branch: id_branch,
      grade: grade,
      id_subject: id_subject,
      index: index
    }, function() {
      return ajaxEnd();
    });
    if ($scope.BranchLoad[grade][id_subject][index].color === 3) {
      return $scope.BranchLoad[grade][id_subject].splice(index, 1);
    } else {
      return $scope.BranchLoad[grade][id_subject][index].color++;
    }
  };
  $scope.changeLoadSubject = function(id_branch, grade, id_subject, index) {
    ajaxStart();
    $.post("ajax/BranchLoadChangeFull", {
      id_branch: id_branch,
      grade: grade,
      id_subject: id_subject,
      index: index
    }, function() {
      return ajaxEnd();
    });
    if ($scope.BranchLoad[grade][id_branch][index].color === 3) {
      return $scope.BranchLoad[grade][id_branch].splice(index, 1);
    } else {
      return $scope.BranchLoad[grade][id_branch][index].color++;
    }
  };
  $scope.sum = function(data) {
    var total_score;
    if (data !== void 0) {
      total_score = 0;
      $.each(data, function(i, score) {
        return total_score += score;
      });
      if ($scope.rating_type === 1) {
        return total_score.toFixed(1);
      } else {
        return total_score;
      }
    }
  };
  return angular.element(document).ready(function() {
    set_scope("Rating");
    $scope.rating_type = 1;
    return $scope.setRating($scope.rating_type);
  });
});
