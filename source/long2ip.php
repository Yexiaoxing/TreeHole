<?php
	error_reporting(0);
	header('Content-type: text/plain; charset=utf-8');
	foreach($_REQUEST as $k=>$v)
		echo long2ip($k).' '.long2ip($v)."\n";
?>