// Generated by CoffeeScript 1.10.0
var requestsLiveUpdate, vueInit;

$(document).ready(function() {
  var vm;
  return vm = vueInit();
});

requestsLiveUpdate = function() {
  var channel, pusher;
  pusher = new Pusher('a9e10be653547b7106c0', {
    encrypted: true
  });
  channel = pusher.subscribe("requests");
  return channel.bind('incoming', (function(_this) {
    return function(data) {
      var animate_speed, request_count, request_counter;
      console.log(data);
      request_count = $('#request-count');
      request_counter = $('#request-counter');
      animate_speed = 7000;
      request_counter.removeClass('text-success').removeClass('text-danger').css('opacity', 1);
      if (data !== null) {
        request_count.text(parseInt(request_count.text()) - 1);
        request_count.animate({
          'background-color': '#158E51'
        }, animate_speed / 2).animate({
          'background-color': '#777'
        }, animate_speed / 2);
        return request_counter.text('-1').addClass('text-success').animate({
          opacity: 0
        }, animate_speed);
      } else {
        request_count.text(parseInt(request_count.text()) + 1);
        request_count.animate({
          'background-color': '#A94442'
        }, animate_speed / 2).animate({
          'background-color': '#777'
        }, animate_speed / 2);
        return request_counter.text('+1').addClass('text-danger').animate({
          opacity: 0
        }, animate_speed);
      }
    };
  })(this));
};

vueInit = function() {
  Vue.config.debug = true;
  Vue.config.async = false;
  Vue.component('phone', {
    props: ['user_id'],
    data: function() {
      return {
        show_element: false,
        connected: false,
        determined: false,
        timer: {
          hide_timeout: void 0,
          interval: void 0,
          diff: 0
        },
        mango: {},
        caller: false,
        last_call_data: false,
        answered_user: false
      };
    },
    template: '#phone-template',
    methods: {
      time: function(seconds) {
        return moment({}).seconds(seconds).format("mm:ss");
      },
      formatDateTime: function(date) {
        return moment(date).format("DD.MM.YY в HH:mm");
      },
      hangup: function() {
        $.post('mango/hangup', {
          call_id: this.mango.call_id
        });
        return this.endCall();
      },
      callAppeared: function() {
        this.show_element = true;
        this.determined = false;
        this.caller = false;
        this.last_call_data = false;
        $.post('mango/getCaller', {
          phone: this.mango.from.number
        }, (function(_this) {
          return function(response) {
            _this.caller = response;
            _this.determined = true;
            return _this.setHideTimeout();
          };
        })(this), 'json');
        return $.post('mango/getLastCallData', {
          phone: this.mango.from.number
        }, (function(_this) {
          return function(response) {
            return _this.last_call_data = response;
          };
        })(this), 'json');
      },
      setHideTimeout: function(seconds) {
        if (!seconds) {
          seconds = 100;
        }
        if (this.timer.hide_timeout) {
          clearTimeout(this.timer.hide_timeout);
        }
        return this.timer.hide_timeout = setTimeout(this.endCall, seconds * 1000);
      },
      startCall: function() {
        return this.connected = true;
      },
      endCall: function() {
        return $.post('mango/getAnsweredUser', {
          phone: this.mango.from.number
        }, (function(_this) {
          return function(response) {
            _this.answered_user = response;
            _this.last_call_data = false;
            return setTimeout(function() {
              this.show_element = false;
              this.connected = false;
              this.answered_user = false;
              return clearTimeout(this.timer.hide_timeout);
            }, 2000);
          };
        })(this), 'json');
      },
      saveState: function() {
        var answered_user, caller_type;
        answered_user = this.mango.to.extension;
        if (answered_user === this.user_id) {
          caller_type = this.caller.type ? this.caller.type : '';
          return $.post('mango/saveCallState', {
            phone: this.mango.from.number,
            user_id: this.user_id
          });
        }
      },
      initPusher: function() {
        var channel, pusher;
        pusher = new Pusher('a9e10be653547b7106c0', {
          encrypted: true
        });
        channel = pusher.subscribe("user_" + this.user_id);
        return channel.bind('incoming', (function(_this) {
          return function(data) {
            console.log('MANGO RECEIVED', data);
            _this.mango = data;
            switch (data.call_state) {
              case 'Appeared':
                return _this.callAppeared();
              case 'Connected':
                _this.saveState();
                return _this.startCall();
              case 'Disconnected':
                return _this.endCall();
            }
          };
        })(this));
      }
    },
    computed: {
      call_length: function() {
        return moment(parseInt(this.timer.diff) * 1000).format('mm:ss');
      },
      number: function() {
        return "+" + this.mango.from.number;
      }
    },
    ready: function() {
      return this.initPusher();
    }
  });
  return new Vue({
    el: '.phone-app'
  });
};
