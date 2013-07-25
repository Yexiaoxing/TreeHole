<?php
$config->APIKey			= 'b8496e8ade724bebad02aa49ec441955';	//你的API Key，请自行申请
$config->SecretKey		= '647b82f561564ee88507c5e9e2b37aba';	//你的API 密钥
$config->RedirectURI	= 'http://hollow.sinaapp.com/admin.php';	//你的API Redirect URI
$sid = $_GET['sid'];
$type= $_GET['type'];
$start_index= $_GET['i'];
$start_index = $start_index?$start_index:0;
$sid = $sid?$sid:'RR{C4AC263B-A3F1-EF65-07AA-19484FDCECBC}';
$sid = preg_replace('/^[\-\{]*([A-Za-z0-9]{8})-{0,1}([A-Za-z0-9]{4})-{0,1}([A-Za-z0-9]{4})-{0,1}([A-Za-z0-9]{4})-{0,1}([A-Za-z0-9]{12})[\-\}]*$/iu','RR{$1-$2-$3-$4-$5}',$sid);
$sid = preg_replace('/^[\-\{]*([A-Za-z0-9]{2})[\{\-]{0,1}([A-Za-z0-9]{8})-{0,1}([A-Za-z0-9]{4})-{0,1}([A-Za-z0-9]{4})-{0,1}([A-Za-z0-9]{4})-{0,1}([A-Za-z0-9]{12})[\-\}]*$/iu','$1{$2-$3-$4-$5-$6}',$sid);
if(empty($sid)) { die("Param Miss."); }

require_once('inc/TmsPageManager.class.php');
$pm = new TmsPageManager();
switch( $type ) {
case 'name':
	$key = 'hollow_sname';
	break;
case 'sid':
default:
	$key = 'hollow_sid';
	break;
}
if ( !$_info = $pm->db_get_hollow_info( array($key=>$sid) ) ) { die("Param Error."); }
$_record = $pm->db_get_status($_info['hollow_id'], $start_index, 20);
foreach( $_record as $k => $v ) {
		$_record[$k]['record_time'] = date('Y/m/d G:i:s A',$_record[$k]['record_time']);
		$_record[$k]['record_ip'] = long2ip($_record[$k]['record_ip']);
}

switch( strtoupper( substr( $_info['hollow_sid'], 0, 2 ) ) ) {
case 'WB':
	break;
case 'QZ':
	break;
case 'RR':
default:
	$_info['page_lnk_tip'] = $_info['hollow_title'].'人人主页';
	$_info['page_lnk'] = 'http://page.renren.com/'.$_info['page_id'];
	break;
}
$tpl = preg_replace('/[^\w\d]/','',$_GET['tpl']);
$tpl = $tpl?$tpl:'metro';
require_once('inc/TmsTemplate.inc.php');
$ttpl->assign('hollow_title',$_info['hollow_title']);
$ttpl->assign('page_lnk_tip',$_info['page_lnk_tip']);
$ttpl->assign('page_lnk'	,$_info['page_lnk']);
$ttpl->assign('hollow_record',$_record);
$ttpl->assign('sid'			,$_info['hollow_sid']);
$ttpl->assign('analytics_code',$ttpl->fetch('analytics.html'));
$ttpl->display("renren_app-$tpl.html");
?>