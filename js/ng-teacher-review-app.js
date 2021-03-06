var app;

app = angular.module("TeacherReview", ["ui.bootstrap"]).filter('toArray', function() {
  return function(obj) {
    var arr;
    arr = [];
    $.each(obj, function(index, value) {
      return arr.push(value);
    });
    return arr;
  };
}).filter('range', function() {
  return function(input, total) {
    var i, j, ref;
    total = parseInt(total);
    for (i = j = 1, ref = total + 1; j < ref; i = j += 1) {
      input.push(i);
    }
    return input;
  };
}).filter('hideZero', function() {
  return function(item) {
    if (item > 0) {
      return item;
    } else {
      return null;
    }
  };
}).controller('StudentReviews', function($scope, $timeout) {
  $scope["enum"] = review_statuses;
  $scope.enum_approved = review_statuses_approved;
  $scope.formatDateTime = function(date) {
    return moment(date).format("DD.MM.YY в HH:mm");
  };
  $scope.yearLabel = function(year) {
    return year + '-' + (parseInt(year) + 1) + ' уч. г.';
  };
  $scope.filter = function() {
    $.cookie("reviews", JSON.stringify($scope.search), {
      expires: 365,
      path: '/'
    });
    $scope.current_page = 1;
    return $scope.getByPage($scope.current_page);
  };
  $scope.pageChanged = function() {
    if ($scope.current_page > 1) {
      window.history.pushState({}, '', 'reviews/?page=' + $scope.current_page);
    }
    return $scope.getByPage($scope.current_page);
  };
  $scope.getByPage = function(page) {
    frontendLoadingStart();
    return $.post("ajax/GetReviews", {
      page: page,
      teachers: $scope.Teachers,
      id_student: $scope.id_student
    }, function(response) {
      frontendLoadingEnd();
      $scope.Reviews = response.data;
      $scope.counts = response.counts;
      return $scope.$apply();
    }, "json");
  };
  return angular.element(document).ready(function() {
    set_scope("TeacherReview");
    $scope.search = $.cookie("reviews") ? JSON.parse($.cookie("reviews")) : {};
    $scope.current_page = $scope.currentPage;
    $scope.pageChanged();
    return $(".single-select").selectpicker();
  });
}).controller("Reviews", function($scope, $timeout, UserService) {
  bindArguments($scope, arguments);
  $scope.UserService = UserService;
  $scope["enum"] = review_statuses;
  $scope.enum_approved = review_statuses_approved;
  $scope.formatDateTime = function(date) {
    return moment(date).format("DD.MM.YY в HH:mm");
  };
  $scope.yearLabel = function(year) {
    return year + '-' + (parseInt(year) + 1) + ' уч. г.';
  };
  $scope.refreshCounts = function() {
    return $timeout(function() {
      $('.watch-select option').each(function(index, el) {
        $(el).data('subtext', $(el).attr('data-subtext'));
        return $(el).data('content', $(el).attr('data-content'));
      });
      return $('.watch-select').selectpicker('refresh');
    }, 100);
  };
  $scope.filter = function() {
    $.cookie("reviews", JSON.stringify($scope.search), {
      expires: 365,
      path: '/'
    });
    $scope.current_page = 1;
    return $scope.getByPage($scope.current_page);
  };
  $scope.pageChanged = function() {
    if ($scope.current_page > 1) {
      window.history.pushState({}, '', 'reviews/?page=' + $scope.current_page);
    }
    return $scope.getByPage($scope.current_page);
  };
  $scope.getByPage = function(page) {
    frontendLoadingStart();
    return $.post("ajax/GetReviews", {
      page: page,
      teachers: $scope.Teachers,
      id_student: $scope.id_student
    }, function(response) {
      frontendLoadingEnd();
      $scope.Reviews = response.data;
      $scope.counts = response.counts;
      $scope.$apply();
      return $scope.refreshCounts();
    }, "json");
  };
  return angular.element(document).ready(function() {
    set_scope("TeacherReview");
    $scope.search = $.cookie("reviews") ? JSON.parse($.cookie("reviews")) : {};
    $scope.current_page = $scope.currentPage;
    $scope.pageChanged();
    return $(".single-select").selectpicker();
  });
}).controller("Main", function($scope) {
  $scope.toggleReviewUser = function() {
    var new_user_id;
    new_user_id = $scope.id_user_review === $scope.user.id ? 0 : $scope.user.id;
    ajaxStart();
    return $.post('ajax/UpdateStudentReviewUser', {
      'id_student': $scope.Student.id,
      'id_user_new': new_user_id
    }, function() {
      ajaxEnd();
      $scope.id_user_review = new_user_id;
      return $scope.$apply();
    });
  };
  $scope.findUser = function(id) {
    return _.findWhere($scope.users, {
      id: id
    });
  };
  $scope["enum"] = review_statuses;
  $scope.enum_approved = review_statuses_approved;
  $scope.RatingInfo = [];
  $scope.setRating = function(field, rating) {
    if ($scope.RatingInfo[field] && $scope.RatingInfo[field] === rating) {
      return $scope.RatingInfo[field] = 0;
    } else {
      return $scope.RatingInfo[field] = rating;
    }
  };
  $scope.saveReviews = function() {
    ajaxStart();
    return $.post("reviews/ajax/save", {
      RatingInfo: $scope.RatingInfo,
      id_student: $scope.Student.id,
      id_subject: $scope.id_subject,
      id_teacher: $scope.Teacher.id,
      year: $scope.year
    }, function(response) {
      if (!$scope.RatingInfo.id) {
        $scope.RatingInfo.id = response;
      }
      ajaxEnd();
      $scope.form_changed = false;
      return $scope.$apply();
    });
  };
  $scope.toggleEnum = function(ngModel, status, ngEnum) {
    var status_id, statuses;
    statuses = Object.keys(ngEnum);
    status_id = statuses.indexOf(ngModel[status].toString());
    status_id++;
    if (status_id > (statuses.length - 1)) {
      status_id = 0;
    }
    ngModel[status] = statuses[status_id];
    return $scope.form_changed = true;
  };
  $scope.form_changed = false;
  return angular.element(document).ready(function() {
    set_scope("TeacherReview");
    $(".teacher-review-textarea, .watch-change").on('keyup change', function() {
      $scope.form_changed = true;
      return $scope.$apply();
    });
    return $(".teacher-rating, .ios7-switch").on('click', function() {
      $scope.form_changed = true;
      return $scope.$apply();
    });
  });
});
