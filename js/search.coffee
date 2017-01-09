#Vue.config.devtools = true
$(document).ready ->
#вешеаем событие по клику по кнопке
  $('#searchModalOpen').click ->
    $('#searchModal').modal({keyboard: true})
    delayFunction = ->
      $('#searchQueryInput').focus()
    setTimeout delayFunction, 500
    $('body.modal-open .row').addClass('blur')
    false

  $('#searchModal').on 'hidden.bs.modal', ->
    delayFnc = ->
      $('.blur').removeClass 'blur'
    setTimeout delayFnc, 500

  # компонент поиска
  viewVue = new Vue
    el: '#searchModal'
    data:
      lists: []
      links: {}
      results: -1
      active: 0
      query: ''
      oldquery: ''
      all: 0
      loading: false
    methods:
      loadData:  _.debounce ->
          this.$http.post 'search', {query: this.query}, emulateJSON: true
          .then (success) =>
            this.loading = false
            this.active = 0
            this.all = 0
            this.lists = []
            this.links = {}
            if success.data.result > 0
              this.results = success.data.result
              # Cтуденты
              if success.data.search.students.length > 0
                for item, i in success.data.search.students
                  item.type = 'students'
                  this.all++
                  this.links[this.all] = 'student/' + item.id
                  item.link = this.links[this.all]
                  this.lists.push(item)
              # Представители
              if success.data.search.representatives.length > 0
                for item, i in success.data.search.representatives
                  item.type = 'representatives'
                  this.all++
                  this.links[this.all] = 'student/' + item.id_student
                  item.link = this.links[this.all]
                  this.lists.push(item)
              # Преподавтели
              if success.data.search.tutors.length > 0
                for item, i in success.data.search.tutors
                  item.type = 'tutors'
                  this.all++
                  this.links[this.all] = 'teachers/edit/' + item.id
                  item.link = this.links[this.all]
                  this.lists.push(item)
              # Заявки
              if success.data.search.requests.length > 0
                for item, i in success.data.search.requests
                  item.type = 'requests'
                  this.all++
                  this.links[this.all] = 'requests/edit/' + item.id
                  item.link = this.links[this.all]
                  this.lists.push(item)
              # Договора
              if success.data.search.contracts.length > 0
                for item, i in success.data.search.contracts
                  item.type = 'contracts'
                  this.all++
                  this.links[this.all] = 'student/' + item.id_student
                  item.link = this.links[this.all]
                  this.lists.push(item)
            else
              this.active = 0
              this.all = 0
              this.lists = []
              this.results = 0
          , (error) =>
            this.active = 0
            this.all = 0
            this.lists = []
            this.results = 0
        , 150
      scroll: -> # метод скролит по необходимости до нужной части результата поиска
        $('#searchResult').scrollTop((this.active - 4) * 30)
      keyup: (e) -> #обработка события набора текста
        if e.code == 'ArrowUp'
          e.preventDefault();
          if this.active > 0
            this.active--
          this.scroll()
        else if e.code == 'ArrowDown'
          e.preventDefault();
          if this.active < this.results
            this.active++
          if this.active > 4
            this.scroll()
        else if e.code == 'Enter'
          window.open this.links[this.active] if this.active > 0
        else
          if this.query isnt ''
            if this.oldquery != this.query and this.query.length > 2
              # this.loading = true
              this.loadData()
            this.oldquery = this.query
          else
            this.active = 0
            this.all = 0
            this.lists = []
            this.results = -1
        null
