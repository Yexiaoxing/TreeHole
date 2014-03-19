<?php
$sid = $_GET['sid'];
$type= $_GET['type'];
$sid = $sid?$sid:'RR{C4AC263B-A3F1-EF65-07AA-19484FDCECBC}';
$sid = preg_replace('/^[\-\{]*([A-Za-z0-9]{8})-{0,1}([A-Za-z0-9]{4})-{0,1}([A-Za-z0-9]{4})-{0,1}([A-Za-z0-9]{4})-{0,1}([A-Za-z0-9]{12})[\-\}]*$/iu','RR{$1-$2-$3-$4-$5}',$sid);
$sid = preg_replace('/^[\-\{]*([A-Za-z0-9]{2})[\{\-]{0,1}([A-Za-z0-9]{8})-{0,1}([A-Za-z0-9]{4})-{0,1}([A-Za-z0-9]{4})-{0,1}([A-Za-z0-9]{4})-{0,1}([A-Za-z0-9]{12})[\-\}]*$/iu','$1{$2-$3-$4-$5-$6}',$sid);
if(empty($sid)) { die("Param Miss."); }

require_once('inc/TmsPageManager.class.php');
try{
    $pm = new TmsPageManager();
} catch (Exception $err) {
    echo("<pre>");
    if(stripos($err->getMessage(),"SQL")!==false)
        echo("连接数据库出现错误，请稍后再试。\n对于给您带来的不便深表歉意，我们会尽快修复。\n");
    echo($err->getMessage()."\n");
    die("--------------\nERROR !</pre>");
}
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
$curr_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
if( $_info['hollow_anonymous'] == 0 && !$pm->signed() ) { header("location: redirect.php?r=".urlencode($curr_url)); exit; }

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
if(empty($_info['hollow_subtitle'])) $_info['hollow_subtitle']='放在树洞里的秘密';

$tpl = preg_replace('/[^\w\d]/','',$_GET['tpl']);
$tpl = $tpl?$tpl:'metro';
require_once('inc/TmsTemplate.inc.php');
$ttpl->assign('hollow_title',$_info['hollow_title']);
$ttpl->assign('hollow_subtitle',$_info['hollow_subtitle']);
$ttpl->assign('page_lnk_tip',$_info['page_lnk_tip']);
$ttpl->assign('page_lnk'	,$_info['page_lnk']);
$ttpl->assign('sid'			,$_info['hollow_sid']);
$ttpl->assign('analytics_code',$ttpl->fetch('analytics.html'));
$ttpl->display("index-$tpl.html");
?>