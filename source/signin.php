<?php
error_reporting(0);
@$redirect = urlencode($_REQUEST['r']);
require_once('inc/TmsPageManager.class.php');
require_once('inc/TmsTemplate.inc.php');
$ttpl->assign('access_url','http://graph.renren.com/oauth/authorize?display=iframe&state=200&origin=0&client_id='.$config->APIKey.'&redirect_uri='.urlencode($config->RedirectURI."?r=$redirect").'&response_type=code&scope=read_user_album+read_user_feed+admin_page');
$ttpl->assign('hollow_title','hollow_title');
$ttpl->assign('page_lnk_tip','page_lnk_tip');
$ttpl->assign('page_lnk'	,'page_lnk');
$ttpl->assign('sid'			,'hollow_sid');
$ttpl->display("login-metro.html");
?>