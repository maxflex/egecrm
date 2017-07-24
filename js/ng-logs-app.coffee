app = angular.module "Logs", ["ui.bootstrap"]
	.controller "ListCtrl", ($scope, $timeout, UserService) ->
		$scope.UserService = UserService
		$scope.LogTypes =
			create: 'создание'
			update: 'обновление'
			delete: 'удаление'
			wrong_login: 'неверный логин'
			wrong_password: 'неверный пароль'
			wrong_captcha: 'неверная капча'
			wrong_sms_code: 'неверный код смс'
			sms_code_sent: 'код смс отправлен'
			outside_office: 'вне офиса'
			banned: 'заблокирован'
			login: 'вход'
			url: 'просмотр URL'

		frontendLoadingStart()

		$scope.toJson = (data)->
			JSON.parse(data)

		$scope.$watch 'search.table', (newVal, oldVal) -> 
			$scope.search.column = null if ((newVal && oldVal) || (oldVal && not newVal))

		$scope.refreshCounts = ->
			$timeout ->
				$('.selectpicker option').each (index, el) ->
					$(el).data 'subtext', $(el).attr 'data-subtext'
					$(el).data 'content', $(el).attr 'data-content'
				$('.selectpicker').selectpicker 'refresh'
			, 100

		$scope.filter = ->
			$.cookie("logs", JSON.stringify($scope.search), { expires: 365, path: '/' });
			$scope.current_page = 1
			$scope.pageChanged()

		$scope.keyFilter = (event) ->
			$scope.filter() if event.keyCode is 13

		$timeout ->
			$scope.search = if $.cookie("logs") then JSON.parse($.cookie("logs")) else {}
			load $scope.page
			$scope.current_page = $scope.page

		$scope.pageChanged = ->
			frontendLoadingEnd()
			load $scope.current_page
			window.history.pushState {}, '', 'logs/?page=' + $scope.current_page if $scope.current_page > 1

		load = (page) ->
			$.post "logs/ajaxGetLogs",
				page: page
			, (response) ->
				frontendLoadingEnd();
				$scope.logs = response.data;
				$scope.counts = response.counts;
				$scope.$apply();
				$scope.refreshCounts();
			, 'json'

		angular.element(document).ready ->
			set_scope "Logs"

		$scope.formatDateTime = (date) ->
			return moment(date).format 'DD.MM.YY в HH:mm'

		$scope.getUser = (user_id) ->
			_.find $scope.users, {id: user_id}