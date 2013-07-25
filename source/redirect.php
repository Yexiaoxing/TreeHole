<?php
@$redi= $_REQUEST['r'];
@$act = $_REQUEST["s"];
include_once('inc/TmsPageManager.class.php');
require_once('inc/TmsTemplate.inc.php');
$pm = new TmsPageManager();
if( isset($_GET["code"]) ) {				# 初次授权登录 转换code为refresh_token
	if($pm->sign_in($_GET["code"],'rr_authorize_code')) {
		if(empty($redi)) $redi = "admin.php";
		$ttpl->assign('redirect_url', $redi);
		$ttpl->display("redirect.html");
	}
	exit();
} else if( !$pm->signed() ) {				# 未登录 跳转登录
	header('location: signin.php?r='.urlencode($redi)); exit();
} else if( $act == 'signout' ) {	# 更换用户
	$pm->sign_out(); header('location: redirect.php?r='.urlencode($redi));
} else if( (!empty($redi)) ) {	# 普通访问 列出主页信息
	$ttpl->assign('redirect_url', $redi);
	$ttpl->display("redirect.html");
	exit();
} else {	# 普通访问 列出主页信息
	$ttpl->assign('redirect_url', 'admin.php');
	$ttpl->display("redirect.html");
	exit();
}
?>