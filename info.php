<?php
//	phpinfo();
	
	$m = new Memcached();
	$m->addServer('localhost', 11211);
	
	$m->add("test", "1231");
	
	echo $m->getResultCode();