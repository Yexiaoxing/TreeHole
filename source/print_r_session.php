<?php
session_start();
header('Content-type: text/plain; charset=utf-8');
echo "SESSION:\n";print_r($_SESSION);
if($_REQUEST['act']=='session_distory') session_destroy();
?>