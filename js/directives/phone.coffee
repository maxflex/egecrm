app.directive 'phones', ->
	restrict: 'E'
	templateUrl: 'directives/phone'
	scope:
		entity:      '='
		entityType:  '@'
	controller: ($scope, $timeout, $attrs, $interval, $element, PhoneService, UserService) ->
		bindArguments $scope, arguments
		$scope.$watch 'entity', (newVal) -> init()

		init = ->
			$scope.level = PhoneService.level $scope.entity
			$timeout -> PhoneService.addMask $element

		$scope.max_level         = PhoneService.fields.length
		$scope.is_disabled       = true if $attrs.hasOwnProperty 'disabled'
		$scope.with_comment      = true if $attrs.hasOwnProperty 'withComment'
		$scope.without_buttons   = true if $attrs.hasOwnProperty 'withoutButtons'

		$scope.nextLevel = ->
			$scope.level++

		# информация по api
		$scope.info = (number) ->
			$scope.api_number = number
			$scope.mango_info = null
			infoTemplate().modal 'show'
			if $scope.isOpened == false
				infoTemplate().on 'hidden.bs.modal', ->
					$scope.isOpened = true
					if $scope.audio
						$scope.audio.pause()
						$scope.audio = null
						$scope.is_playing_stage = 'stop'
						$scope.is_playing = null

			PhoneService
				.info number
				.then (result) ->
					$scope.mango_info = result
					$timeout ->
						$scope.$apply()

		$scope.time = (seconds) ->
			moment(0).seconds(seconds).format("mm:ss")

		$scope.getNumberTitle = (number) ->
			return 'текущий номер' if number is PhoneService.clean($scope.api_number)
			number

		$scope.is_playing_stage = 'stop'
		$scope.isOpened = false;

		recodringLink = (recording_id) ->
			api_key   = 'goea67jyo7i63nf4xdtjn59npnfcee5l'
			api_salt  = 't9mp7vdltmhn0nhnq0x4vwha9ncdr8pa'
			timestamp = moment().add(5, 'minute').unix()

			sha256 = new jsSHA('SHA-256', 'TEXT')
			sha256.update(api_key + timestamp + recording_id + api_salt)
			sign = sha256.getHash('HEX')

			return "https://app.mango-office.ru/vpbx/queries/recording/link/#{recording_id}/play/#{api_key}/#{timestamp}/#{sign}"

		$scope.intervalStart = () ->
			$scope.interval = $interval ->
				if $scope.audio
					$scope.current_time = angular.copy $scope.audio.currentTime
					$scope.prc = (($scope.current_time * 100) /  $scope.audio.duration).toFixed(2)
					$scope.stop() if parseInt($scope.prc) == 100
			, 10

		$scope.intervalCancel = () ->
			$interval.cancel $scope.interval

		# инициализируем аудио
		$scope.initAudio = (recording_id) ->
			$scope.stop() if $scope.is_playing
			$scope.audio = new Audio recodringLink(recording_id)
			$scope.current_time = 0
			$scope.prc = 0
			$scope.is_playing_stage = 'start'
			$scope.is_playing = recording_id

		# ставим на паузу
		$scope.pause = ->
			$scope.intervalCancel()
			$scope.audio.pause() if $scope.audio
			$scope.is_playing_stage = 'pause'

		# воспроизводим звук
		$scope.play = (recording_id) ->
			$scope.initAudio(recording_id) if not $scope.isPlaying(recording_id)
			if $scope.is_playing_stage is 'play'
				$scope.pause()
			else
				$scope.audio.play()
				$scope.is_playing_stage = 'play'
				$scope.intervalStart()

		# указатель воспроизведения
		$scope.isPlaying = (recording_id) ->
			$scope.is_playing is recording_id

		# полная остановка процесса воспроизведения
		$scope.stop = ->
			$scope.prc = 0
			$scope.is_playing = null
			$scope.audio.pause()
			$scope.audio = null
			$scope.is_playing_stage = 'stop'
			$scope.intervalCancel()

		# прокрутка
		$scope.setCurentTime = (e) ->
			width = angular.element e.target
						.width()
			$scope.prc = (e.offsetX * 100) / width;
			time = ($scope.audio.duration * $scope.prc) / 100
			$scope.audio.currentTime = time

		$scope.phoneMaskControl = (event) ->
			input = $ event.target
			number = input.val()
			# @strange-behavior
			# keyup fired on value init
			return if PhoneService.isSame number, $scope.entity[getFieldName input]
			filled = input.val() && not number.match /_/
			checkDublicate = ! $attrs.hasOwnProperty 'untrackDuplicate'

			if filled
				input.trigger 'blur'
				if checkDublicate
					PhoneService.checkDublicate number, $scope.$parent.id_request
					.then (result) ->
						if result is  'true'
							ang_scope and ang_scope.phone_duplicate = result
							input.addClass 'has-error-bold'
						else
							ang_scope and ang_scope.phone_duplicate = null
							input.removeClass 'has-error-bold'
			else
				input.removeClass 'has-error-bold'
				ang_scope && ang_scope.phone_duplicate = null

		getFieldName = (el) ->
			el.attr('id').replace 'entity-phone-', ''

		infoTemplate = ->
			$ "#api-phone-info-#{$scope.entityType}", $element
