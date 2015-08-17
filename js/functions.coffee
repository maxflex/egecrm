	set_scope = (app_name) ->
		@ang_scope = angular.element("[ng-app='#{app_name}']").scope()
		
	phoneCorrect = (element) ->
		# пустой номер телефона – это тоже правильный номер телефона
		return false if not $("#" + element).val()
		    
	    # если есть нижнее подчеркивание, то номер заполнен не полностью
		not_filled = $("#" + element).val().match(/_/)
		not_filled is null
		
	isMobilePhone = (element) ->
		phone = $("#" + element).val()
		
		return false if not phone
		
		not phone.indexOf "+7 (9"
