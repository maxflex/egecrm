	set_scope = (app_name) ->
		@ang_scope = angular.element("[ng-app='#{app_name}']").scope()
		
	phoneCorrect = (element) ->
		# пустой номер телефона – это тоже правильный номер телефона
		return false if not $("#" + element).val()
		    
	    # если есть нижнее подчеркивание, то номер заполнен не полностью
		not_filled = $("#" + element).val().match(/_/)
		not_filled is null
	
	deleteTeacher = (id_teacher) ->
		bootbox.confirm "Вы уверены, что хотите удалить преподавателя №#{id_teacher}?", (result) ->
			if result is true
				ajaxStart()
				$.post "teachers/ajax/delete", {id_teacher: id_teacher}
				window.history.go -1
		
	objectToArray = (Obj) ->
		$.map Obj, (value, index) ->
		    return [value]
			
	isMobilePhone = (element) ->
		phone = $("#" + element).val()
		
		return false if not phone
		
		not phone.indexOf "+7 (9"
	
	ajaxStart = (element = false) ->
		if element isnt false
			$(".ajax-#{element}-button").attr("disabled", "disabled")
		NProgress.start()

	ajaxEnd = (element = false) ->
		if element isnt false
			#setTimeout ->
			$(".ajax-#{element}-button").removeAttr("disabled")
			#, 500
		NProgress.done()
