app.directive('comments', function() {
  return {
    restrict: 'E',
    templateUrl: 'directives/comment',
    scope: {
      user: '=',
      entityId: '=',
      trackLoading: '=',
      entityType: '@'
    },
    controller: function($rootScope, $scope, $timeout, UserService) {
      var bindDraggable, bindDraggableAll, focusModal;
      $scope.UserService = UserService;
      $scope.show_max = 4;
      $scope.show_all_comments = false;
      $scope.is_dragging = false;
      bindDraggableAll = function() {
        return $timeout(function() {
          return $scope.getComments().forEach(function(comment) {
            return bindDraggable(comment.id);
          });
        });
      };
      bindDraggable = function(comment_id) {
        $("#comment-" + comment_id).draggable({
          revert: 'invalid',
          activeClass: 'drag-active',
          start: function(e, ui) {
            $scope.is_dragging = true;
            return $scope.$apply();
          },
          stop: function(e, ui) {
            $scope.is_dragging = false;
            return $scope.$apply();
          }
        });
        return $("#comment-delete-" + $scope.entityType + "-" + $scope.entityId).droppable({
          tolerance: 'pointer',
          hoverClass: 'hovered',
          drop: function(e, ui) {
            return $scope.remove($(ui.draggable).data('comment-id'));
          }
        });
      };
      $scope.showAllComments = function() {
        $scope.show_all_comments = true;
        $timeout(function() {
          return bindDraggableAll();
        });
        return focusModal();
      };
      $scope.getComments = function() {
        if ($scope.comments) {
          if ($scope.show_all_comments || $scope.comments.length <= $scope.show_max) {
            return $scope.comments;
          } else {
            return _.last($scope.comments, $scope.show_max - 1);
          }
        } else {
          return [];
        }
      };
      $scope.$watch('entityId', function(newVal, oldVal) {
        if ($scope.entityType && $scope.entityId) {
          return $.post("get/comments/" + $scope.entityType + "/" + $scope.entityId, {}, function(response) {
            $scope.comments = response;
            if ($scope.trackLoading) {
              $rootScope.loaded_comments++;
            }
            return $timeout(function() {
              return bindDraggableAll();
            });
          }, 'json');
        }
      });
      $scope.formatDateTime = function(date) {
        return moment(date).format("DD.MM.YY в HH:mm");
      };
      $scope.startCommenting = function(event) {
        $scope.start_commenting = true;
        return $timeout(function() {
          return $(event.target).parent().find('input').focus();
        });
      };
      $scope.endCommenting = function() {
        $scope.comment = '';
        return $scope.start_commenting = false;
      };
      $scope.remove = function(comment_id) {
        return $.post("ajax/DeleteComment", {
          "id": comment_id
        }, function() {
          $scope.comments = _.without($scope.comments, _.findWhere($scope.comments, {
            id: comment_id
          }));
          return $timeout(function() {
            return bindDraggableAll();
          });
        });
      };
      $scope.edit = function(comment, event) {
        var element, old_text;
        old_text = comment.comment;
        element = $(event.target);
        element.unbind('keydown').unbind('blur');
        element.attr('contenteditable', 'true').focus().on('keydown', function(e) {
          console.log(old_text);
          if (e.keyCode === 13) {
            $(this).removeAttr('contenteditable').blur();
            comment.comment = $(this).text();
            $.post('ajax/EditComment', {
              id: comment.id,
              comment: comment.comment
            });
          }
          if (e.keyCode === 27) {
            return $(this).blur();
          }
        }).on('blur', function(e) {
          if (element.attr('contenteditable')) {
            console.log(old_text);
            return element.removeAttr('contenteditable').html(old_text);
          }
        });
      };
      $scope.submitComment = function(event) {
        if (event.keyCode === 13) {
          $.post('ajax/AddComment', {
            comment: $scope.comment,
            id_user: $scope.user.id,
            id_place: $scope.entityId,
            place: $scope.entityType
          }, function(response) {
            $scope.comments.push(response);
            return $timeout(function() {
              return bindDraggableAll();
            }, 400);
          }, 'json');
          $scope.endCommenting();
          focusModal();
        }
        if (event.keyCode === 27) {
          return $(event.target).blur();
        }
      };
      return focusModal = function() {
        if ($('.modal:visible').length) {
          $('.modal:visible').focus();
        }
      };
    }
  };
});

app.service('UserService', function($rootScope, $http, $timeout) {
  var system_user;
  this.current_user = $rootScope.$$childTail.user;
  $http.get('get/users', {}).then((function(_this) {
    return function(response) {
      return _this.users = response.data;
    };
  })(this));
  system_user = {
    color: '#999999',
    login: 'system',
    id: 0,
    banned: 0
  };
  this.get = function(user_id) {
    return this.getUser(user_id);
  };
  this.getUser = function(user_id) {
    return _.findWhere(this.users, {
      id: user_id
    }) || system_user;
  };
  this.getLogin = function(user_id) {
    return this.getUser(parseInt(user_id)).login;
  };
  this.getColor = function(user_id) {
    return this.getUser(parseInt(user_id)).color;
  };
  this.getWithSystem = function(only_active) {
    var users;
    if (only_active == null) {
      only_active = true;
    }
    users = this.getAll(only_active);
    users.unshift(system_user);
    return users;
  };
  this.getAll = function(only_active) {
    if (only_active == null) {
      only_active = true;
    }
    if (only_active) {
      return _.where(this.users, {
        banned: 0
      });
    } else {
      return this.users;
    }
  };
  this.getBannedUsers = function() {
    return _.where(this.users, {
      banned: 1
    });
  };
  return this;
});
