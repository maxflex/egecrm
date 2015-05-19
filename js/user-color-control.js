$(document).ready(function() {
	$("#user-list").on("change", function() {
		$(this).attr("style", $(this).children(":selected").attr("style"));
	});
})