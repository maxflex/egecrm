	/*
		Контроллер блока комментариев
	*/
	
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
					$(".existing-comments").append("<span class='glyphicon glyphicon-stop'></span>" 
						+ comment_add_field.val() + " <span class='save-coordinates'>(" + response.user + " " + response.date + ")</span>")
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