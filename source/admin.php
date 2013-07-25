<?php
	include_once('inc/TmsPageManager.class.php');
	$pm = new TmsPageManager();
	if( isset($_GET["code"]) ) {				# 初次授权登录 转换code为refresh_token
		$pm->sign_in($_GET["code"],'rr_authorize_code'); header('location: admin.php'); exit();
	} else if( !$pm->signed() ) {				# 未登录 跳转登录
		header('location: join.php'); exit();
	} else if( $_REQUEST["s"] == 'signout' ) {	# 更换用户
		$pm->sign_out(); header('location: join.php?s='.$_REQUEST['r']);
	} else if( $_REQUEST["s"] == 'setpage' ) {	# 设置主页信息
		$_response = array();
		$_response['status']  = "200";
		$_response['message'] = "";
		$_response['result']  = array();
		if( $jsonPages = json_decode( $_REQUEST["data"] ,true) ) {
			foreach( $jsonPages as $jsonPage ) {
				$_response['result'][$jsonPage['hollow_sid']] = $pm->db_set_hollow_info( $jsonPage['hollow_sid'], array( 
					"hollow_title" => $jsonPage['hollow_title'], 
					"data_prefix"  => $jsonPage['data_prefix'], 
					"data_suffix"  => $jsonPage['data_suffix'], 
					"hollow_state" => ($jsonPage['hollow_state']==0?0:1) 
				) ); # 1.成功 2.失败 -1.失败（没有该主页管理员权限）
				if( $_response['result'][$jsonPage['hollow_sid']] != 1 ) $_response['status']  = "300";
			}
		} else {
			$_response['status']  = "500";
			$_response['message'] = "数据传输出错！";
		}
		echo json_encode($_response);
		exit();
	} else {	# 普通访问 列出主页信息
		$accounts = $pm->get_signed_account_list();
		$hollows = array();
		foreach( $accounts['rr'] as $user_id=>$data ) {
			$hollows = array_merge( $hollows, $pm->get_managed_list($data['access_token'], 'rr') );
		}
		for( $i=0;$i<count($hollows);$i++ ) {
			$hollows[$i]['hollow_title'] = htmlspecialchars($hollows[$i]['hollow_title']);
			$hollows[$i]['hollow_sid'] = str_replace(array('{','-','}'),'',$hollows[$i]['hollow_sid']);
			$hollows[$i]['data_prefix'] = htmlspecialchars($hollows[$i]['data_prefix']);
			$hollows[$i]['data_suffix'] = htmlspecialchars($hollows[$i]['data_suffix']);
			$hollows[$i]['expire_time_formated'] = date('Y年m月d日',$hollows[$i]['expire_time']);
		}
		require_once('inc/TmsTemplate.inc.php');
		$tpl = preg_replace('/[^\w\d]/','',$_GET['tpl']);
		$tpl = $tpl?$tpl:'default';
		$ttpl->assign('hollows',$hollows);
		$ttpl->assign('accounts',$accounts);
		$ttpl->display("admin-$tpl.html");
		exit();
	}
?>