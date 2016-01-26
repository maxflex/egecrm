	/*
		Контроллер блока комментариев
	*/
	var old_text // запоминаем старый текст комментария, если он не изменился
	
	
	$(document).ready(function(){
		// Элементы
		comment_add 		= $("#comment-add")
		comment_add_field 	= $("#comment-add-field")
		
		// Добавление нового комментария
		comment_add.on("click", function() {
			comment_add_field.fadeIn(300).focus();
		})
		
		// Уходим из поля добавления комментария
		comment_add_field.on("blur", function() {
			$(this).fadeOut(300)	
		})
		
		// Нажатие ENTER или ESC внутри комментария
		comment_add_field.on("keydown", function(event) {
			// ENTER
			if (event.keyCode == 13) {
				$.post("ajax/AddComment", {
					comment	: comment_add_field.val(), 
					place	: comment_add.attr("place"), 	// место добавления комментариев хранится в аттрибуте PLACE на кнопке добавления
					id_place: comment_add.attr("id_place"), // ID указанного места (может быть ID заявки, например) 
				}, function(response) {
					// Добавляем новый комментарий к уже существующим
					$(".existing-comments").append("<div id='comment-block-" + response.id +"'><span class='glyphicon glyphicon-stop' style='float: left'></span>" 
						+ "<div style='display: initial' data-id='" + response.id + "' id='comment-" + response.id + "'>" + comment_add_field.val()  + "</div> "
						+ "<span class='save-coordinates'>(" + response.user + " " + response.date + ")</span> "
						+ "<span class='glyphicon opacity-pointer glyphicon-pencil no-margin-right' onclick='editComment(" + response.id + ")'></span> "
						+ "<span class='glyphicon opacity-pointer text-danger glyphicon-remove' onclick='deleteComment(" + response.id + ")'></span>"
					)
					// Обнуляем значения
					comment_add_field.blur().fadeOut(300).val("")
					console.log(response)
				}, "json")
			}
			// ECS
			if (event.keyCode == 27) {
				$(this).blur().fadeOut(300)
			}
		})
	})
	
	
	// Редактирование комментариев
	function editComment(id_comment)
	{	
		comment = $("#comment-" + id_comment)
		old_text = comment.text()
		comment.attr("contenteditable", "true").focus()
			.on("keydown", function(event) {
				// ENTER
				if (event.keyCode == 13) {
					$(this).removeAttr("contenteditable")
					$.post("ajax/EditComment", {
						"id" 		: $(this).data('id'),
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
	function deleteComment(id_comment) 
	{
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