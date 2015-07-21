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
		
		$.post("ajax/changeRequestUser", {"id_request" : id_request, "id_user_new" : id_user_new});
	}