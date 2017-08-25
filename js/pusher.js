var vm, vueInit;

vm = false;

$(document).ready(function() {
  return vm = vueInit();
});

vueInit = function() {
  Vue.config.debug = true;
  Vue.config.async = false;
  Vue.component('phone', {
    props: ['user_id', 'type', 'key', 'cluster'],
    data: function() {
      return {
        show_element: false,
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
        return moment.utc(seconds * 1000).format("mm:ss");
      },
      formatDateTime: function(date) {
        return moment(new Date(date * 1000)).format("DD.MM.YY в HH:mm");
      },
      hangup: function() {
        $.post('mango/hangup', {
          call_id: this.mango.call_id
        });
        return this.endCall();
      },
      callAppeared: function() {
        this.answered_user = false;
        this.show_element = true;
        this.caller = this.mango.caller;
        this.last_call_data = this.mango.last_call_data;
        return this.setHideTimeout();
      },
      setHideTimeout: function(seconds) {
        if (seconds == null) {
          seconds = 15;
        }
        if (this.timer.hide_timeout) {
          clearTimeout(this.timer.hide_timeout);
        }
        return this.timer.hide_timeout = setTimeout(this.endCall, seconds * 1000);
      },
      startCall: function() {},
      endCall: function() {
        clearTimeout(this.timer.hide_timeout);
        return this.show_element = false;
      },
      initPusher: function() {
        var channel, pusher;
        pusher = new Pusher(this.key, {
          encrypted: true,
          cluster: this.cluster
        });
        channel = pusher.subscribe("user_" + this.user_id);
        channel.bind('incoming', (function(_this) {
          return function(data) {
            _this.mango = data;
            _this.$log('mango');
            switch (data.call_state) {
              case 'Appeared':
                return _this.callAppeared();
              case 'Connected':
                return _this.startCall();
            }
          };
        })(this));
        return channel.bind('answered', (function(_this) {
          return function(data) {
            console.log(data);
            if (_this.show_element) {
              console.log('setting answered user to', data.answered_user);
              _this.answered_user = data.answered_user;
              return _this.setHideTimeout(4);
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
