<?php
include_once('inc/config.inc.php');
$sid = $_REQUEST['sid'];
$type= $_REQUEST['type'];
$status_str = urldecode($_REQUEST['s']);
if(empty($sid)) { die("SID参数丢失。"); }
if(''==preg_replace("/\s+/ui",'',$status_str)) die('先写点什么吧。');

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
#echo '树洞正在搬家 请稍后再来';exit; 
if( !$_info = $pm->db_get_hollow_info( array($key=>$sid) ) ) { die("SID参数错误。"); }
if( $_info['hollow_state'] == 0 ) { echo '本树洞暂不开放！';exit; }
if( $_info['hollow_anonymous'] == 0 && !$pm->signed() ) { echo '本树洞发言要求先登录。'; exit; }
// if( $_info['expire_time'] < time() ) {
	// if( $_info['expire_num'] < 1 ) {
		// echo '本树洞已欠费！';exit; 
	// } else {
		// $pm->db_set_hollow_info( array( 'expire_num'=>($_info['expire_num']-1) ) );
	// }
// }
echo $pm->db_set_status($_info['hollow_id'],$status_str,$_info['data_prefix'],$_info['data_suffix']);
	
?>