<?php
@session_start();
include_once('config.inc.php');
include_once('function.inc.php');
include_once('db.class.php');
class TmsRenRenPageManager {
	var $db;																#用户数据库
	/**
	 * 构造函数 连接数据库。
	 */
	function __construct() {												#构造函数，连接数据库
		$this->db = new DB("mysql:dbname=".TM_DB_NAME.";host=".TM_DB_HOST.";port=".TM_DB_PORT.";charset=utf-8", TM_DB_USER, TM_DB_PW);//odbc:driver={microsoft access driver (*.mdb)};dbq=".getcwd()."\\TopUsers.php");
	}
	/**
	* TO-DO: 刷新token
	* Ret  : false 刷新失败
	* 		 refresh_token => 
	* 		 access_token => 
	*		 expires_time =>
	*		 access_scope =>
	*/
	function rr_get_new_token( $refresh_token ) {							# 公共函数类
		global $config;
		$jsonAuthCode = json_decode(get_var_curl("http://graph.renren.com/oauth/token?grant_type=refresh_token&refresh_token=".$refresh_token."&client_id=".$config->APIKey."&client_secret=".$config->SecretKey),true);
		if ( is_array($jsonAuthCode) && empty($jsonAuthCode['error']) ){
			$token = array(
				'refresh_token'	=>$jsonAuthCode['refresh_token'],
				'access_token'	=>$jsonAuthCode['access_token'],
				'expires_time'	=>( ((int)$jsonAuthCode['expires_in']) + time() ),
				'access_scope'	=>$jsonAuthCode['scope']
			);
			$this->db_update_renren_oauth( $jsonAuthCode['user']['id'], $token );
			return $token;
		} else {
			return false;
		}
	}
	/**
	* FNAME: rr_check_page_admin
	* TO-DO: 检查access_token是否是PID主页的管理员
	* RET  : false 不是
	*        true  是
	*/
	function rr_check_page_admin( $page_id, $access_token ) {				# 后台管理类
		require_once 'RenrenRestApiService.class.php';
		$rrObj = new RenrenRestApiService;
		$params = array('access_token'	=> $access_token,
						'format'		=> 'json',
						'call_id'		=> '1313735980455',
						'page_id'		=> $page_id ,
						'v'				=> '1.0');	//使用access_token调api的情况
		$res = $rrObj->rr_post_curl('pages.isAdmin', $params);//curl函数发送请求
		return $res['result']==1;
	}
	/**
	* FNAME: rr_check_page_exist
	* TO-DO: 检查PID是否是一个合法的主页ID
	* RET  : false 不是
	*        true  是
	*/
	function rr_check_page_exist( $page_id, $access_token ) {				# 后台管理类
		require_once 'RenrenRestApiService.class.php';
		$rrObj = new RenrenRestApiService;
		$params = array('access_token'	=> $access_token,
						'format'		=> 'json',
						'call_id'		=> '1313735980455',
						'page_id'		=> $page_id ,
						'v'				=> '1.0');	//使用access_token调api的情况
		$res = $rrObj->rr_post_curl('pages.isPage', $params);//curl函数发送请求
		return $res['result']==1;
	}
	/**
	* FNAME: db_update_renren_oauth
	* TO-DO: 设置授权信息
	* RET  : false 设置失败
	*        true  设置成功
	*/
	function db_update_renren_oauth( $user_id, $data ) {
		$_bool = $this->db->get_col("SELECT COUNT(*) FROM `renren_oauth` WHERE `user_id` = ?",array($user_id));
		if (!$_bool) {	// OAuth记录不存在
			$data['user_id'] = $user_id;
			return $this->db->insert( 'renren_oauth', $data );
		} else {	// OAuth记录存在
			return $this->db->update( 'renren_oauth', $data, array( 'user_id' => $user_id ) );
		}
	}
	/**
	* TO-DO: 获取管理的主页列表
	* Ret  : array((page_id,page_name,sid,expire_num,expire_time,data_prefix,data_suffix,hollow_title,hollow_state),()....())
	* 		 false 无效token
	*/
	function rr_get_managed_list( $access_token ) {										# 后台管理类
		# 向人人网发起请求
		require_once 'RenrenRestApiService.class.php';
		$rrObj = new RenrenRestApiService;
		
		# 根据access_token或session_key得到用户的ID，返回的ID值应该在access_token或session_key有效期内被存储，从而避免重复调用。
		// $params = array('access_token'	=> $access_token,
						// 'format'		=> 'json',
						// 'call_id'		=> '1313735980455',
						// 'method'		=> 'users.getLoggedInUser',
						// 'v'				=> '1.0');	//使用access_token调api的情况
		// $res = $rrObj->rr_post_curl('users.getLoggedInUser', $params);//curl函数发送请求
		// if( array_key_exists('error_code',$res) ) { echo $res['error_code'].$res['error_msg']; die(); }
		
		$params = array('access_token'	=> $access_token,
						'format'		=> 'json',
						'call_id'		=> '1313735980455',
						'method'		=> 'pages.getManagedList',
						'page'			=> 1,	//	int	页码，默认值为1
						'count'			=> 50,	//	int	每页的容量，默认值为10
						'v'				=> '1.0');	//使用access_token调api的情况
		$res = $rrObj->rr_post_curl('pages.getManagedList', $params);//curl函数发送请求
		if( array_key_exists('error_code',$res) ) { $res = array();/* echo $res['error_code'].$res['error_msg']; die(); */ }
		
		return $res;	//返回结果
	}
	/**
	* FNAME: rr_post_data
	* TO-DO: 向人人网发起请求
	* RET  : 人人网返回值
	*/
	function rr_post_data( $method, $params ) {								# 公共函数类
		require_once 'inc/RenrenRestApiService.class.php';
		$rrObj = new RenrenRestApiService;
		return $rrObj->rr_post_curl( $method, $params );//curl函数发送请求
	}
	/**
	* FNAME: rr_page_set_status
	* TO-DO: 向人人网公共主页发送新状态
	* RET  : 人人网返回值
	*/
	function rr_page_set_status( $page_id, $token, $status_str ) {	# 用户使用类
		if( !is_array($token) ) { return array('result'=>'0','error_code'=>'0','error_msg'=>'TMS:token参数格式错误。'); }
		if( array_key_exists('expires_time',$token) && array_key_exists('refresh_token',$token) && $token['expires_time']-2000 < time() ) {
			if( false === ( $new_token = $this->rr_get_new_token($token['refresh_token']) ) ) { return array('result'=>'0','error_code'=>'0','error_msg'=>'TMS:获取refresh_token失败。'); }
			$this->db_update_renren_oauth($token['user_id'],$new_token);
			$token = $new_token;
		}
		
		$params = array('access_token'	=> $token['access_token'],
						'format'		=> 'json',
						'call_id'		=> '1313735980455',
						'method'		=> 'status.set',
						'page_id'		=> $page_id,
						'place_id'		=> '',
						'status'		=> $status_str,
						'v'				=> '1.0');	//使用access_token调api的情况
		return $this->rr_post_data('status.set', $params);//curl函数发送请求
	}
}
?>