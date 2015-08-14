<?php
//	phpinfo();
	
	$str = "792527272105";
	
	$part1 = substr($str, 1, 3);
	$part2 = substr($str, 4, 3);
	$part3 = substr($str, 7, 2);
	$part4 = substr($str, 9, 2);
	
	echo "+7 ($part1) $part2-$part3-$part4";