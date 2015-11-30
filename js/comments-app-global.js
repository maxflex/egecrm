	/*
		Контроллер блока комментариев
	*/
	var old_text // запоминаем старый текст комментария, если он не изменился
	
	
	$(document).ready(function(){
		setTimeout(initComments, 300)
	})
	
	
	function initComments() {
		// Элементы
		comment_add 		= $(".comment-add")
		comment_add_field 	= $(".comment-add-field")
		
		// Добавление нового комментария
		comment_add.on("click", function() {
			$(this).hide()
			$("#comment-add-field-" + $(this).attr("id_place") + ", #comment-add-login-" + $(this).attr("id_place")).fadeIn(300).focus();
		})
		
		// Уходим из поля добавления комментария
		comment_add_field.on("blur", function() {
			$(this).hide()
			$(this).parent().children().first().hide()
			$(this).parent().parent().children().first().fadeIn(300)
		})
		
		// Нажатие ENTER или ESC внутри комментария
		comment_add_field.on("keydown", function(event) {
			t = $(this)
			// ENTER
			if (event.keyCode == 13) {
				$.post("ajax/AddComment", {
					comment	: $(this).val(), 
					place	: $("#comment-add-" + $(this).attr("request")).attr("place"), 	// место добавления комментариев хранится в аттрибуте PLACE на кнопке добавления
					id_place: $("#comment-add-" + $(this).attr("request")).attr("id_place"), // ID указанного места (может быть ID заявки, например) 
				}, function(response) {
					// Добавляем новый комментарий к уже существующим
					// scope = angular.element("[ng-app='Request']").scope()
					scope = ang_scope
					switch (t.data('place')) {
						case "REQUEST_EDIT_REQUEST": {
							comments = scope.request_comments
							break
						}
						case "REQUEST_EDIT_STUDENT": {
							comments = scope.student_comments
							break
						}
						case "GROUP_EDIT": {
							comments = scope.Group.Comments
							break
						}
						case "REQUEST_LIST": {
							var comments						
							$.each(scope.requests, function(i, v) {
								if (v.id == t.attr("request")) {
									scope.requests[i].Comments = initIfNotSet(scope.requests[i].Comments)
									comments = scope.requests[i].Comments
									return
								}	
							})
							break
						}
					}
					
					comments = initIfNotSet(comments)
					
					comments.push({
						'id': 			response.id,
						'User': 		response.User,
						'coordinates': 	response.coordinates,
						'comment': 		t.val()
					})
					
					scope.$apply()
					
					// Обнуляем значения
					t.blur().fadeOut(300).val("")
					console.log(response)
				}, "json")
			}
			// ECS
			if (event.keyCode == 27) {
				$(this).blur().fadeOut(300)
			}
		})
	}
	
	
	// Редактирование комментариев
	function editComment(elem)
	{	
		//id_comment = $(elem).data('id')
		id_comment = $(elem).attr("commentid");
		
		comment = $("#comment-" + id_comment)
		old_text = comment.text()
		comment.attr("contenteditable", "true").focus()
			.on("keydown", function(event) {
				// ENTER
				if (event.keyCode == 13) {
					$(this).removeAttr("contenteditable")
					$.post("ajax/EditComment", {
						"id" 		: id_comment,
						"comment"	: $(this).text()
					})
				}
			})
			.on("blur", function(event) {
				if ($(this).attr("contenteditable")) {
					$(this).removeAttr("contenteditable").html(old_text)
				}
			})
		
		// курсор в конец content-editable
		setEndOfContenteditable('comment-' + id_comment)
	}
	
	
	/**
	 * Удалить коммент.
	 * 
	 */
	function deleteComment(elem) 
	{
		id_comment = $(elem).data('id')
		
		comment_block = $("#comment-block-" + id_comment)
	
		comment_block.slideUp(300, function() {
			$(this).remove()
		})
		
		$.post("ajax/DeleteComment", {"id" : id_comment})
	}
	
	/**
	 * Переместить курсор редактирования в конец content-editable.
	 * 
	 */
	function setEndOfContenteditable(contentEditableElement)
	{
		contentEditableElement = document.getElementById(contentEditableElement);//This is the element that you want to move the caret to the end of

	    var range,selection;
	    if(document.createRange)//Firefox, Chrome, Opera, Safari, IE 9+
	    {
	        range = document.createRange();//Create a range (a range is a like the selection but invisible)
	        range.selectNodeContents(contentEditableElement);//Select the entire contents of the element with the range
	        range.collapse(false);//collapse the range to the end point. false means collapse to end rather than the start
	        selection = window.getSelection();//get the selection object (allows you to change selection)
	        selection.removeAllRanges();//remove any selections already made
	        selection.addRange(range);//make the range you have just created the visible selection
	    }
	    else if(document.selection)//IE 8 and lower
	    { 
	        range = document.body.createTextRange();//Create a range (a range is a like the selection but invisible)
	        range.moveToElementText(contentEditableElement);//Select the entire contents of the element with the range
	        range.collapse(false);//collapse the range to the end point. false means collapse to end rather than the start
	        range.select();//Select the range (make it the visible selection
	    }
	}