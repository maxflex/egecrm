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
          event.preventDefault();
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

app.directive('sms', function() {
  return {
    restrict: 'E',
    templateUrl: 'directives/sms',
    scope: {
      number: '=',
      templates: '@',
      mode: '@',
      counts: '='
    },
    controller: function($scope, $http, $timeout, Sms, SmsService, UserService, PhoneService) {
      var init, scrollUp;
      bindArguments($scope, arguments);
      $scope.smsCount = function() {
        return SmsCounter.count($scope.message || '').messages;
      };
      $scope.send = function() {
        var promise;
        ajaxStart();
        $scope.sms_sending = true;
        if (promise = SmsService.send($scope.mode, $scope.number, $scope.message, $scope.mass)) {
          promise.then(function(response) {
            ajaxEnd();
            $scope.sms_sending = false;
            $scope.history.unshift(response.data);
            $timeout(function() {
              return $scope.$apply();
            });
            return scrollUp();
          });
        } else {
          ajaxEnd();
        }
        return $scope.message = '';
      };
      $scope.$watch('number', function(newVal, oldVal) {
        $scope.history = SmsService.getHistory(newVal);
        return scrollUp();
      });
      scrollUp = function() {
        return $timeout(function() {
          return $('#sms-history').animate({
            scrollTop: 0
          }, 'fast');
        });
      };
      $scope.setTemplate = function(id_template) {
        return SmsService.getTemplate(id_template, $scope.$parent.student || $scope.$parent.Teacher).then(function(response) {
          return $scope.message = response.data;
        });
      };
      init = function() {
        $scope.SmsService.mass = false;
        $scope.SmsService.to_students = true;
        $scope.SmsService.to_representatives = false;
        $scope.SmsService.to_teachers = true;
        if ($scope.mode) {
          return $scope.SmsService.mode = $scope.mode;
        }
      };
      return init();
    }
  };
});

app.service('PhoneService', function($rootScope) {
  this.call = function(number) {
    return location.href = "sip:" + number.replace(/[^0-9]/g, '');
  };
  this.isMobile = function(number) {
    if (typeof number !== 'string') {
      number = '' + number;
    }
    return number && (parseInt(number[4]) === 9 || parseInt(number[1]) === 9);
  };
  this.clean = function(number) {
    if (typeof number !== 'string') {
      number = '' + number;
    }
    return number.replace(/[^0-9]/gim, "");
  };
  this.format = function(number) {
    if (!number) {
      return;
    }
    number = this.clean(number);
    return '+' + number.substr(0, 1) + ' (' + number.substr(1, 3) + ') ' + number.substr(4, 3) + '-' + number.substr(7, 2) + '-' + number.substr(9, 2);
  };
  this.sms = function(number) {
    $rootScope.sms_number = this.clean(number);
    return lightBoxShow('sms');
  };
  this.isFull = function(number) {
    return this.clean(number).length === 11;
  };
  return this;
});

app.service('PusherService', function($http, $q, UserService) {
  var init;
  this.inited = $q.defer();
  this.bind = function(channel, callback) {
    if (this.pusher === void 0) {
      init();
    }
    return this.inited.promise.then((function(_this) {
      return function() {
        return _this.channel.bind("" + channel, callback);
      };
    })(this));
  };
  init = (function(_this) {
    return function() {
      _this.pusher = new Pusher('a9e10be653547b7106c0', {
        encrypted: true
      });
      return UserService.current_loaded.promise.then(function() {
        _this.channel = _this.pusher.subscribe('user_' + UserService.current_user.id);
        return _this.inited.resolve(true);
      });
    };
  })(this);
  return this;
});

app.service('SmsService', function($rootScope, $http, Sms, PusherService) {
  this.updates = [];
  this.mode = 'default';
  this.post_config = {
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    }
  };
  PusherService.bind('sms', (function(_this) {
    return function(data) {
      _this.updates[data.id] = data.status;
      return $rootScope.$apply();
    };
  })(this));
  this.getStatus = function(sms) {
    var status_class;
    switch (this.updates[sms.id] || sms.id_status) {
      case 103:
        status_class = 'delivered';
        break;
      case 102:
        status_class = 'inway';
        break;
      default:
        status_class = 'not-delivered';
    }
    return status_class;
  };
  this.getHistory = function(number) {
    if (number) {
      return Sms.query({
        number: number
      });
    }
  };
  this.send = function(mode, number, message, mass) {
    var action, data;
    if (message) {
      switch (this.mode) {
        case 'group':
          action = 'sendGroupSms';
          break;
        case 'client':
          action = 'sendGroupSmsClients';
          break;
        case 'teacher':
          action = 'sendGroupSmsTeachers';
          break;
        default:
          action = 'sendSms';
      }
      data = $.param({
        message: message,
        number: number,
        mass: mass
      });
      return $http.post('ajax/' + action, data, this.post_config, 'json');
    }
  };
  this.getTemplate = function(id_template, entity) {
    var data, params;
    params = {};
    if (entity) {
      if (entity.login) {
        params['entity_login'] = entity.login;
      }
      if (entity.password) {
        params['entity_password'] = entity.password;
      }
      if (entity.phone) {
        params['phone'] = entity.phone;
      }
    }
    data = $.param({
      number: id_template,
      params: params
    });
    return $http.post('templates/ajax/get', data, this.post_config);
  };
  return this;
});

app.service('UserService', function($rootScope, $q, $http, $timeout, User) {
  var system_user;
  this.loaded = $q.defer();
  this.current_loaded = $q.defer();
  this.users = User.query((function(_this) {
    return function() {
      return _this.loaded.resolve(true);
    };
  })(this));
  $timeout((function(_this) {
    return function() {
      var user;
      if (user = $rootScope.$$childTail.user) {
        _this.current_user = user;
      }
      return _this.current_loaded.resolve(true);
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
      id: parseInt(user_id)
    }) || system_user;
  };
  this.getLogin = function(user_id) {
    return this.getUser(parseInt(user_id)).login;
  };
  this.getColor = function(user_id, system_color) {
    var user;
    user = this.getUser(parseInt(user_id));
    if (user === system_user && system_color) {
      return system_color;
    } else {
      return user.color;
    }
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
  this.getBannedHaving = function(condition_obj) {
    return _.filter(this.users, function(user) {
      return user.banned === 1 && condition_obj[user.id];
    });
  };
  return this;
});

app.factory('Sms', function($resource) {
  return $resource(':url', {
    id: '@id'
  }, {
    query: {
      method: 'GET',
      url: 'get/sms/:number',
      isArray: true
    }
  });
}).factory('User', function($resource) {
  return $resource('get/users', {
    id: '@id'
  }, {
    create: {
      method: 'POST'
    }
  });
});
