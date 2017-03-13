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
            $rootScope[$scope.entityType.toLowerCase() + '_comments_loaded'] = true;
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

app.directive('phones', function() {
  return {
    restrict: 'E',
    templateUrl: 'directives/phone',
    scope: {
      entity: '=',
      entityType: '@'
    },
    controller: function($scope, $timeout, $attrs, $interval, $element, PhoneService, UserService) {
      var getFieldName, infoTemplate, init, recodringLink;
      bindArguments($scope, arguments);
      $scope.$watch('entity', function(newVal) {
        return init();
      });
      init = function() {
        $scope.level = PhoneService.level($scope.entity);
        return $timeout(function() {
          return PhoneService.addMask($element);
        });
      };
      $scope.max_level = PhoneService.fields.length;
      if ($attrs.hasOwnProperty('disabled')) {
        $scope.is_disabled = true;
      }
      if ($attrs.hasOwnProperty('withComment')) {
        $scope.with_comment = true;
      }
      if ($attrs.hasOwnProperty('withoutButtons')) {
        $scope.without_buttons = true;
      }
      $scope.nextLevel = function() {
        return $scope.level++;
      };
      $scope.info = function(number) {
        $scope.api_number = number;
        $scope.mango_info = null;
        infoTemplate().modal('show');
        if ($scope.isOpened === false) {
          infoTemplate().on('hidden.bs.modal', function() {
            $scope.isOpened = true;
            if ($scope.audio) {
              $scope.audio.pause();
              $scope.audio = null;
              $scope.is_playing_stage = 'stop';
              return $scope.is_playing = null;
            }
          });
        }
        return PhoneService.info(number).then(function(result) {
          $scope.mango_info = result;
          return $timeout(function() {
            return $scope.$apply();
          });
        });
      };
      $scope.time = function(seconds) {
        return moment(0).seconds(seconds).format("mm:ss");
      };
      $scope.getNumberTitle = function(number) {
        if (number === PhoneService.clean($scope.api_number)) {
          return 'текущий номер';
        }
        return number;
      };
      $scope.is_playing_stage = 'stop';
      $scope.isOpened = false;
      recodringLink = function(recording_id) {
        var api_key, api_salt, sha256, sign, timestamp;
        api_key = 'goea67jyo7i63nf4xdtjn59npnfcee5l';
        api_salt = 't9mp7vdltmhn0nhnq0x4vwha9ncdr8pa';
        timestamp = moment().add(5, 'minute').unix();
        sha256 = new jsSHA('SHA-256', 'TEXT');
        sha256.update(api_key + timestamp + recording_id + api_salt);
        sign = sha256.getHash('HEX');
        return "https://app.mango-office.ru/vpbx/queries/recording/link/" + recording_id + "/play/" + api_key + "/" + timestamp + "/" + sign;
      };
      $scope.intervalStart = function() {
        return $scope.interval = $interval(function() {
          if ($scope.audio) {
            $scope.current_time = angular.copy($scope.audio.currentTime);
            $scope.prc = (($scope.current_time * 100) / $scope.audio.duration).toFixed(2);
            if (parseInt($scope.prc) === 100) {
              return $scope.stop();
            }
          }
        }, 10);
      };
      $scope.intervalCancel = function() {
        return $interval.cancel($scope.interval);
      };
      $scope.initAudio = function(recording_id) {
        if ($scope.is_playing) {
          $scope.stop();
        }
        $scope.audio = new Audio(recodringLink(recording_id));
        $scope.current_time = 0;
        $scope.prc = 0;
        $scope.is_playing_stage = 'start';
        return $scope.is_playing = recording_id;
      };
      $scope.pause = function() {
        $scope.intervalCancel();
        if ($scope.audio) {
          $scope.audio.pause();
        }
        return $scope.is_playing_stage = 'pause';
      };
      $scope.play = function(recording_id) {
        if (!$scope.isPlaying(recording_id)) {
          $scope.initAudio(recording_id);
        }
        if ($scope.is_playing_stage === 'play') {
          return $scope.pause();
        } else {
          $scope.audio.play();
          $scope.is_playing_stage = 'play';
          return $scope.intervalStart();
        }
      };
      $scope.isPlaying = function(recording_id) {
        return $scope.is_playing === recording_id;
      };
      $scope.stop = function() {
        $scope.prc = 0;
        $scope.is_playing = null;
        $scope.audio.pause();
        $scope.audio = null;
        $scope.is_playing_stage = 'stop';
        return $scope.intervalCancel();
      };
      $scope.setCurentTime = function(e) {
        var time, width;
        width = angular.element(e.target).width();
        $scope.prc = (e.offsetX * 100) / width;
        time = ($scope.audio.duration * $scope.prc) / 100;
        return $scope.audio.currentTime = time;
      };
      $scope.phoneMaskControl = function(event) {
        var checkDublicate, filled, input, number;
        input = $(event.target);
        number = input.val();
        if (PhoneService.isSame(number, $scope.entity[getFieldName(input)])) {
          return;
        }
        filled = input.val() && !number.match(/_/);
        checkDublicate = !$attrs.hasOwnProperty('untrackDuplicate');
        if (filled) {
          input.trigger('blur');
          if (checkDublicate) {
            return PhoneService.checkDublicate(number, $scope.$parent.id_request).then(function(result) {
              if (result === 'true') {
                ang_scope && (ang_scope.phone_duplicate = result);
                return input.addClass('has-error-bold');
              } else {
                ang_scope && (ang_scope.phone_duplicate = null);
                return input.removeClass('has-error-bold');
              }
            });
          }
        } else {
          input.removeClass('has-error-bold');
          return ang_scope && (ang_scope.phone_duplicate = null);
        }
      };
      getFieldName = function(el) {
        return el.attr('id').replace('entity-phone-', '');
      };
      return infoTemplate = function() {
        return $("#api-phone-info-" + $scope.entityType, $element);
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
      counts: '=',
      groupId: '=',
      mass: '='
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
        if (promise = SmsService.send($scope.number, $scope.message)) {
          promise.then(function(response) {
            ajaxEnd();
            $scope.sms_sending = false;
            if ($scope.mass) {
              notifySuccess('Отправлено ' + response.data + ' СМС');
              return lightBoxHide();
            } else {
              $scope.history.unshift(response.data);
              $timeout(function() {
                return $scope.$apply();
              });
              return scrollUp();
            }
          });
        } else {
          ajaxEnd();
        }
        return $scope.message = '';
      };
      $scope.$watch('number', function(newVal, oldVal) {
        if (newVal) {
          $scope.history_loading = true;
          return SmsService.getHistory(newVal).$promise.then(function(response) {
            $scope.history = response;
            $scope.history_loading = false;
            return scrollUp();
          });
        }
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
        return _.extend($scope.SmsService.params, {
          to_students: true,
          to_representatives: false,
          to_teachers: true,
          mode: $scope.mode,
          groupId: $scope.groupId
        });
      };
      return init();
    }
  };
});

app.service('GroupService', function() {
  this.getYears = function(groups) {
    if (groups) {
      return _.uniq(_.pluck(groups, 'year'));
    }
    return [];
  };
  return this;
});

app.service('PhoneService', function($rootScope) {
  this.fields = ['phone', 'phone2', 'phone3'];
  this.call = function(number) {
    if (typeof number !== 'string') {
      number = '' + number;
    }
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
  this.level = function(entity) {
    var field, i, len, level, ref;
    level = 0;
    if (entity) {
      ref = this.fields;
      for (i = 0, len = ref.length; i < len; i++) {
        field = ref[i];
        if (entity[field]) {
          level++;
        }
      }
    }
    return level;
  };
  this.info = function(number) {
    return $.post('mango/stats', {
      number: this.clean(number)
    }, function(result) {
      return result;
    }, 'json');
  };
  this.isSame = function(a, b) {
    return this.clean(a) === this.clean(b);
  };
  this.checkDublicate = function(number, id_request) {
    return $.post('ajax/checkPhone', {
      phone: number,
      id_request: id_request
    }, function(result) {
      return result;
    });
  };
  this.addMask = function(context) {
    return $(".phone-masked", context).mask('+7 (999) 999-99-99', {
      autoclear: false
    });
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
  this.params = {};
  this.post_config = {
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    }
  };
  PusherService.bind('sms', (function(_this) {
    return function(data) {
      _this.updates[parseInt(data.id)] = parseInt(data.status);
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
    return Sms.query({
      number: number
    });
  };
  this.send = function(number, message) {
    var action, data;
    if (message) {
      switch (this.params.mode) {
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
      _.extend(this.params, {
        message: message,
        number: number
      });
      data = $.param(this.params);
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
      return _.filter(this.users, function(user) {
        return user.rights.indexOf(34) === -1;
      });
    } else {
      return this.users;
    }
  };
  this.getBannedUsers = function() {
    return _.filter(this.users, function(user) {
      return user.rights.indexOf(34) !== -1;
    });
  };
  this.getBannedHaving = function(condition_obj) {
    return _.filter(this.users, function(user) {
      return user.rights.indexOf(34) !== -1 && condition_obj && condition_obj[user.id];
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

app.value('Workplaces', {
  0: 'не активен в системе ЕГЭ-Центре',
  1: 'активен в системе ЕГЭ-Центра',
  2: 'ведет занятия в ЕГЭ-Центре',
  3: 'ранее работал в ЕГЭ-Центре'
}).value('TaskTypes', {
  0: 'crm',
  1: 'seo'
});
