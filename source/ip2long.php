<?php
	error_reporting(0);
	header('Content-type: text/plain; charset=utf-8');
	foreach($_REQUEST as $k=>$v)
		echo  $k.' '.ip2long(str_replace("_",".",$k))."\n".$v." ".ip2long($v)."\n";
?>