<?php
	$datetime1 = time();
	$datetime2 = strtotime("2016-01-12 18:00:00");
	$interval  = $datetime1 - $datetime2;
	$minutes   = round($interval / 60);
	
	var_dump($minutes);