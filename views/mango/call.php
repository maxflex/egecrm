<script>
	$(document).ready(function() {
		  // Enable pusher logging - don't include this in production
	    Pusher.log = function(message) {
	      if (window.console && window.console.log) {
	        window.console.log(message);
	      }
	    };
	
	    var pusher = new Pusher('a9e10be653547b7106c0', {
	      encrypted: true
	    });
	    var channel = pusher.subscribe('test_channel');
	    channel.bind('my_event', function(data) {
	      alert(data.message);
	    });
	})
</script>