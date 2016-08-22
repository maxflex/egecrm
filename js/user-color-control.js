	$(document).ready(function() {
		bindUserColorControl();
	})

	function bindUserColorControl() {
		$(".user-list").on("change", function() {
			selected = $(this).children(":selected")
			if (!selected.val()) {
				$(this).removeAttr("style")
			} else {
				$(this).attr("style", $(this).children(":selected").attr("style"));				
			}
		});
	}
	
	function changeUserColor(elem) {
		id_request = $(elem).data("rid");
		id_user_new = $(elem).val();		
		setTimeout(function() {
			$("#request-user-select-" + id_request).hide()
			$("#request-user-display-" + id_request).show()
		}, 10)
		$.post("ajax/changeRequestUser", {"id_request" : id_request, "id_user_new" : id_user_new});
	}
	
	
	function setRequestListUser(elem) {
		if (Number.isInteger(elem))
			id_user = elem
		else
			id_user = $(elem).val();

		if (id_user == undefined) id_user = '';

		console.log("here", id_user);
		$.cookie("id_user_list", id_user, { expires: 365, path: '/' });
		
		$("li.active").first().children().first().click();
	}