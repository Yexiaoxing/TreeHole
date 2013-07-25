<?php
require_once('TmsTemplate.class.php'); //包含TmsTemplate类文件
$ttpl = new TmsTemplate(); //建立TmsTemplate实例对象$ttpl
$ttpl->template_dir = dirname(__FILE__)."/../html/"; //设置模板目录
//----------------------------------------------------
// 左右边界符，默认为"<?" "? >"
//----------------------------------------------------
$ttpl->left_delimiter = "<?";
$ttpl->right_delimiter = "?>";

?>