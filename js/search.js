Vue.config.devtools = true;

$(document).ready(function() {
  var viewVue;
  $('#searchModalOpen').click(function() {
    var delayFunction;
    $('#searchModal').modal({
      keyboard: true
    });
    delayFunction = function() {
      return $('#searchQueryInput').focus();
    };
    setTimeout(delayFunction, 500);
    $('body.modal-open .row').addClass('blur');
    return false;
  });
  $('#searchModal').on('hidden.bs.modal', function() {
    var delayFnc;
    delayFnc = function() {
      return $('.blur').removeClass('blur');
    };
    return setTimeout(delayFnc, 500);
  });
  return viewVue = new Vue({
    el: '#searchModal',
    data: {
      lists: [],
      links: {},
      results: -1,
      active: 0,
      query: '',
      oldquery: '',
      all: 0,
      loading: false
    },
    methods: {
      showResponder: function(e) {},
      loadData: _.debounce(function() {
        return this.$http.post('search', {
          query: this.query
        }).then((function(_this) {
          return function(success) {
            var i, item, j, k, l, len, len1, len2, len3, len4, m, n, ref, ref1, ref2, ref3, ref4, results;
            _this.loading = false;
            _this.active = 0;
            _this.all = 0;
            _this.lists = [];
            _this.links = {};
            console.log('data', success);
            if (success.data.result > 0) {
              _this.results = success.data.result;
              if (success.data.search.students.length > 0) {
                ref = success.data.search.students;
                for (i = j = 0, len = ref.length; j < len; i = ++j) {
                  item = ref[i];
                  item.type = 'students';
                  _this.all++;
                  _this.links[_this.all] = 'student/' + item.id;
                  item.link = _this.links[_this.all];
                  _this.lists.push(item);
                }
              }
              if (success.data.search.representatives.length > 0) {
                ref1 = success.data.search.representatives;
                for (i = k = 0, len1 = ref1.length; k < len1; i = ++k) {
                  item = ref1[i];
                  item.type = 'representatives';
                  _this.all++;
                  _this.links[_this.all] = 'student/' + item.student_id;
                  item.link = _this.links[_this.all];
                  _this.lists.push(item);
                }
              }
              if (success.data.search.tutors.length > 0) {
                ref2 = success.data.search.tutors;
                for (i = l = 0, len2 = ref2.length; l < len2; i = ++l) {
                  item = ref2[i];
                  item.type = 'tutors';
                  _this.all++;
                  _this.links[_this.all] = 'teachers/edit/' + item.id;
                  item.link = _this.links[_this.all];
                  _this.lists.push(item);
                }
              }
              if (success.data.search.requests.length > 0) {
                ref3 = success.data.search.requests;
                for (i = m = 0, len3 = ref3.length; m < len3; i = ++m) {
                  item = ref3[i];
                  item.type = 'requests';
                  _this.all++;
                  _this.links[_this.all] = 'requests/edit/' + item.id;
                  item.link = _this.links[_this.all];
                  _this.lists.push(item);
                }
              }
              if (success.data.search.contracts.length > 0) {
                ref4 = success.data.search.contracts;
                results = [];
                for (i = n = 0, len4 = ref4.length; n < len4; i = ++n) {
                  item = ref4[i];
                  item.type = 'contracts';
                  _this.all++;
                  _this.links[_this.all] = 'requests/edit/' + item.id;
                  item.link = _this.links[_this.all];
                  results.push(_this.lists.push(item));
                }
                return results;
              }
            } else {
              _this.active = 0;
              _this.all = 0;
              _this.lists = [];
              return _this.results = 0;
            }
          };
        })(this), (function(_this) {
          return function(error) {
            _this.active = 0;
            _this.all = 0;
            _this.lists = [];
            return _this.results = 0;
          };
        })(this));
      }, 100),
      scroll: function() {
        var totalObject;
        totalObject = Object.keys(this.links).length;
        return $('#searchResult').scrollTop((this.active - 4) * 30);
      },
      keyup: function(e) {
        if (e.code === 'ArrowUp') {
          e.preventDefault();
          if (this.active > 0) {
            this.active--;
          }
          this.scroll();
        } else if (e.code === 'ArrowDown') {
          e.preventDefault();
          if (this.active < this.results) {
            this.active++;
          }
          if (this.active > 4) {
            this.scroll();
          }
        } else if (e.code === 'Enter') {
          if (this.active > 0) {
            window.open(this.links[this.active]);
          }
        } else {
          if (this.query !== '' || this.query !== ' ') {
            if (this.oldQuery !== this.query && this.query.length > 2) {
              this.loading = true;
              this.loadData();
            }
            this.oldquery = this.query;
          } else {
            this.active = 0;
            this.all = 0;
            this.lists = [];
            this.results = -1;
          }
        }
        return null;
      }
    }
  });
});
