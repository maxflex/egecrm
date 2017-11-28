app = angular.module "Clients", ["ui.bootstrap"]
	.filter 'to_trusted', ['$sce', ($sce) ->
        return (text) ->
            return $sce.trustAsHtml(text)
	]
	.filter 'hideZero', ->
        (item) ->
            if item > 0 then item else null
	.filter 'toArray', ->
		(obj) ->
			arr = []
			$.each obj, (index, value) ->
				arr.push(value)
			return arr
	.controller "SubjectsCtrl", ($scope, $timeout, PhoneService) ->
		bindArguments $scope, arguments

		angular.element(document).ready ->
			set_scope "Clients"
			$scope.search = if $.cookie("clients_subjects") then JSON.parse($.cookie("clients_subjects")) else {}
			$scope.current_page = $scope.currentPage
			$scope.pageChanged()
			$timeout ->
				$(".single-select").selectpicker()
			, 300

		$scope.totalSum = ->
			if $scope.contract_subjects and $scope.contract_subjects.length
				sum = 0
				$scope.contract_subjects.forEach (cs) -> sum += cs.subject_sum
				return sum

		$scope.yearLabel = (year) ->
			'договоры на ' + year + '-' + (parseInt(year) + 1) + ' год'

		$scope.filter = ->
			$.cookie("clients_subjects", JSON.stringify($scope.search), { expires: 365, path: '/' });
			$scope.current_page = 1
			$scope.getByPage($scope.current_page)

		# Страница изменилась
		$scope.pageChanged = ->
			window.history.pushState {}, '', 'clients/subjects?page=' + $scope.current_page if $scope.current_page > 1
			# Получаем задачи, соответствующие странице и списку
			$scope.getByPage($scope.current_page)

		$scope.getByPage = (page) ->
			frontendLoadingStart()
			$.post "clients/ajax/GetSubjects",
				page: page
			, (response) ->
				frontendLoadingEnd()
				$scope.contract_subjects = response.data
				$scope.count = response.count
				$scope.$apply()
			, "json"

		$scope.getNumber = (index) ->
			(($scope.current_page - 1) * 100) + (index + 1)
	.controller "ListCtrl", ($scope, $timeout, PhoneService) ->
		bindArguments $scope, arguments
		$scope.yearLabel = (year) ->
			'договоры на ' + year + '-' + (parseInt(year) + 1) + ' год'

		$scope.getNumber = (index) ->
			(($scope.current_page - 1) * 30) + (index + 1)

		$scope.refreshCounts = ->
			$timeout ->
				$('.watch-select option').each (index, el) ->
					$(el).data 'subtext', $(el).attr 'data-subtext'
					$(el).data 'content', $(el).attr 'data-content'
				$('.watch-select').selectpicker 'refresh'
			, 100

		$scope.filter = ->
			$.cookie("clients", JSON.stringify($scope.search), { expires: 365, path: '/' });
			$scope.current_page = 1
			$scope.getByPage($scope.current_page)

		$scope.sort = ->
			if $scope.search.order is undefined   then $scope.search.order = 'asc'
			else if $scope.search.order is 'asc'  then $scope.search.order = 'desc'
			else if $scope.search.order is 'desc' then delete $scope.search.order
			$scope.filter()

		# Страница изменилась
		$scope.pageChanged = ->
			window.history.pushState {}, '', 'clients/?page=' + $scope.current_page if $scope.current_page > 1
			# Получаем задачи, соответствующие странице и списку
			$scope.getByPage($scope.current_page)

		$scope.getByPage = (page) ->
			frontendLoadingStart()
			$.post "clients/ajax/GetStudents",
				page: page
			, (response) ->
				frontendLoadingEnd()
				$scope.Students  = response.data
				$scope.counts = response.counts
				$scope.totals = response.totals
				$scope.$apply()
				$scope.refreshCounts()
			, "json"

		$scope.payment_options = {}
		$scope.splitPaymentsOptions = (year) ->
			return if not year
			return $scope.payment_options[year] if $scope.payment_options[year] isnt undefined
			year = parseInt(year)
			options =
				'1-0': [],
				'2-0': [_paymentDate(year + 1, '01-27')],
				'3-0': [_paymentDate(year, '11-20'), _paymentDate(year + 1, '02-20')],
				'3-1': [_paymentDate(year, '11-27'), _paymentDate(year + 1, '02-27')],
				'8-0': [_paymentDate(year, '10-15'), _paymentDate(year, '11-15'), _paymentDate(year, '12-15'),
					_paymentDate(year + 1, '01-15'), _paymentDate(year + 1, '02-15'), _paymentDate(year + 1, '03-15'), _paymentDate(year + 1, '04-15')]
			$scope.payment_options[year] = options
			options

		_paymentDate = (year, date) -> moment(parseInt(year) + '-' + date).format('DD.MM.YY')

		$scope.getPaymentLabel = (dates) ->
			len = dates.length + 1
			payment = 'платеж'
			if len > 1 && len <= 4
				payment += 'а'
			if len > 4
				payment += 'ей'
			str = len + ' ' + payment
			if dates.length > 0
				str += ': '
				if len == 8
					str += 'ежемесячно 15 числа'
				else
					dates.forEach (date, index) ->
						str += date
						if ((index + 1) != dates.length)
							str += ', '
			return str

		angular.element(document).ready ->
			set_scope "Clients"
			$scope.search = if $.cookie("clients") then JSON.parse($.cookie("clients")) else {}
			$scope.current_page = $scope.currentPage
			$scope.pageChanged()
			$(".single-select").selectpicker()