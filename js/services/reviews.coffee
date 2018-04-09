app.service 'ReviewsService', ($rootScope, UserService) ->
	this.UserService = UserService
	this.enum = review_statuses
	this.enum_approved = review_statuses_approved

	this.init = (currentPage, id_teacher = null) ->
		this.id_teacher = id_teacher
		this.search = if $.cookie("reviews") then JSON.parse($.cookie("reviews")) else {}
		this.current_page = currentPage
		this.pageChanged()
		$(".single-select").selectpicker()

	this.formatDateTime = (date) ->
		moment(date).format "DD.MM.YY в HH:mm"

	this.yearLabel = (year) ->
		year + '-' + (parseInt(year) + 1) + ' уч. г.'

	this.refreshCounts = ->
		$timeout ->
			$('.watch-select option').each (index, el) ->
				$(el).data 'subtext', $(el).attr 'data-subtext'
				$(el).data 'content', $(el).attr 'data-content'
			$('.watch-select').selectpicker 'refresh'
		, 100

	this.filter = ->
		$.cookie("reviews", JSON.stringify(this.search), { expires: 365, path: '/' });
		this.current_page = 1
		this.getByPage(this.current_page)

	# Страница изменилась
	this.pageChanged = ->
		window.history.pushState {}, '', 'reviews/?page=' + this.current_page if this.current_page > 1
		# Получаем задачи, соответствующие странице и списку
		this.getByPage(this.current_page)

	this.getByPage = (page) ->
		frontendLoadingStart()
		$.post "ajax/GetReviews",
			page: page
			teachers: this.Teachers
			id_student: this.id_student
		, (response) ->
			frontendLoadingEnd()
			this.Reviews  = response.data
			this.counts = response.counts
			this.$apply()
			this.refreshCounts()
		, "json"
