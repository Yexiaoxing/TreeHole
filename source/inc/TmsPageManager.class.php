<?php
@session_start();
include_once('config.inc.php');
include_once('function.inc.php');
include_once('db.class.php');
include_once('TmsRenRenPageManager.class.php');
class TmsPageManager {
	var $db;																#用户数据库
	/**
	* 构造函数 连接数据库。
	*/
	function __construct() {												#构造函数，连接数据库
		$this->db = new DB("mysql:dbname=".TM_DB_NAME.";host=".TM_DB_HOST.";port=".TM_DB_PORT.";charset=utf-8", TM_DB_USER, TM_DB_PW);//odbc:driver={microsoft access driver (*.mdb)};dbq=".getcwd()."\\TopUsers.php");
	}
	/**
	* TO-DO: 检查是否登录（人人授权）
	* Ret  : true  已登录
	*        false 未登录
	*/
	function signed( $type = 'rr', $uid = '' ) {														# 后台管理类
		# 清理错误的登录信息。
		$accounts = $this->get_signed_account_list();
		if( empty($uid) ) {
			return !(empty($accounts['rr']) && empty($accounts['qz']) && empty($accounts['wb']));
		} else {
			$type = strtolower($type);
			return isset($accounts[$type][$uid]) && !empty($accounts[$type][$uid]);
		}
	}
	/**
	* TO-DO: 登录（人人授权）
	* Ret  : void
	*/
	function sign_in( $data, $type ) {	# 后台管理类
		switch( strtolower($type) ) {
		case 'rr_refresh_token':
			if( empty($data['access_token']) ) {
				$rpm = new TmsRenRenPageManager();
				$data = $rpm->rr_get_new_token($data['refresh_token']);
			}
			$_SESSION[TMS_SESSION_DIR]['admin_oauth']['rr'][substr($data['access_token'],-9)] = $data;
			break;
		case 'rr_authorize_code':
			global $config;
			# 通过code获取到refresh_token和access_token。
			$jsonAuthCode = json_decode(get_var_curl('http://graph.renren.com/oauth/token?grant_type=authorization_code&client_id='.$config->APIKey.'&redirect_uri='.urlencode($config->RedirectURI.(isset($_REQUEST['r'])?'?r='.urlencode($_REQUEST['r']):'')).'&client_secret='.$config->SecretKey.'&code='.$data),true);
			$error 			= $jsonAuthCode['error'];
			// $refresh_token 	= $jsonAuthCode['refresh_token'];
			// $access_token 	= $jsonAuthCode['access_token'];
			if(empty($error)) {
				$this->sign_in( $jsonAuthCode, 'rr_refresh_token' );
				$rpm = new TmsRenRenPageManager();
				$rpm->db_update_renren_oauth( $jsonAuthCode['user']['id'], array(
					'access_token'=>$jsonAuthCode['access_token'],
					'refresh_token'=>$jsonAuthCode['refresh_token'],
					'expires_time'=>( ((int)$jsonAuthCode['expires_in']) + time() ),
					'access_scope'=>$jsonAuthCode['scope']
				) );
				return true;
			} else {
				print_r($jsonAuthCode);
				return false;
			}
			break;
		default:
			break;
		}
	}
	/**
	* TO-DO: 登出（人人授权）
	* Ret  : void
	*/
	function sign_out( $mixed_uid='' ) {													# 后台管理类
		if( empty($mixed_uid) ) {
			unset($_SESSION[TMS_SESSION_DIR]['admin_oauth']);
		} else if( is_string($mixed_uid) ) {
			unset($_SESSION[TMS_SESSION_DIR]['admin_oauth'][$mixed_uid]);
		} else if( is_array($mixed_uid) ) {
			foreach( $mixed_uid as $k=>$v ) {
				unset($_SESSION[TMS_SESSION_DIR]['admin_oauth'][$v]);
			}
		}
	}
	/**
	* TO-DO: 获取已登录用户列表
	* Ret  : array[type][uid]
	* 		$ret_arr位false时返回一个用户id
	*/
	function get_signed_account_list( $ret_arr = true ){
		# 清理错误的登录信息。
		if(!is_array($_SESSION[TMS_SESSION_DIR])) $_SESSION[TMS_SESSION_DIR] = array();
		if(!is_array($_SESSION[TMS_SESSION_DIR]['admin_oauth'])) $_SESSION[TMS_SESSION_DIR]['admin_oauth'] = array();
		if(!is_array($_SESSION[TMS_SESSION_DIR]['admin_oauth']['rr'])) $_SESSION[TMS_SESSION_DIR]['admin_oauth']['rr'] = array();
		if(!is_array($_SESSION[TMS_SESSION_DIR]['admin_oauth']['wb'])) $_SESSION[TMS_SESSION_DIR]['admin_oauth']['wb'] = array();
		if(!is_array($_SESSION[TMS_SESSION_DIR]['admin_oauth']['qz'])) $_SESSION[TMS_SESSION_DIR]['admin_oauth']['qz'] = array();
		foreach( $_SESSION[TMS_SESSION_DIR]['admin_oauth']['rr'] as $k=>$v ) if(empty($v)||empty($v['refresh_token'])) unset($_SESSION[TMS_SESSION_DIR]['admin_oauth']['rr'][$k]);
		foreach( $_SESSION[TMS_SESSION_DIR]['admin_oauth']['wb'] as $k=>$v ) if(empty($v)) unset($_SESSION[TMS_SESSION_DIR]['admin_oauth']['wb'][$k]);
		foreach( $_SESSION[TMS_SESSION_DIR]['admin_oauth']['qz'] as $k=>$v ) if(empty($v)) unset($_SESSION[TMS_SESSION_DIR]['admin_oauth']['qz'][$k]);
		# 读取登录信息并返回
		$accounts = $_SESSION[TMS_SESSION_DIR]['admin_oauth'];
		if(!array_key_exists('rr',$accounts)) $accounts['rr'] = array();
		if(!array_key_exists('wb',$accounts)) $accounts['wb'] = array();
		if(!array_key_exists('qz',$accounts)) $accounts['qz'] = array();
		if($ret_arr){
			return $accounts;
		}else {
			foreach( $accounts as $account ){
				foreach( $account as $account_id => $account_data ){
					return  $account_id;
				}
			}
			return '';
		}
	}
	/**
	* TO-DO: 获取管理的主页列表
	* Ret  : array((page_id,page_name,sid,expire_num,expire_time,data_prefix,data_suffix,hollow_title,hollow_state),()....())
	* 		 false 无效token
	*/
	function get_managed_list( $access_token, $type='RR' ) {										# 后台管理类
		switch( strtoupper( substr( $type, 0, 2 ) ) ) {
		case 'WB':
			break;
		case 'QZ':
			break;
		case 'RR':	# 整理人人网管理的公共主页
		default:
			$rpm = new TmsRenRenPageManager();
			$rr_response = $rpm->rr_get_managed_list( $access_token );
			foreach( $rr_response as $i=>$arr ) {
				$renren_page_manager_data = array('page_id'=>$rr_response[$i]['page_id'], 'manager_id'=>substr($access_token,-9));
				if( !$_info = $this->db_get_hollow_info(array('page_id'=>$rr_response[$i]['page_id']),'','rr') ) {	# 数据库中没有这个主页，则添加。
					$this->db_create_hollow( array('hollow_title'=>$rr_response[$i]['name']), $renren_page_manager_data, 'rr' );
				} else {	# 数据库中有这个主页，查找当前管理员信息。
					$_page_manager = $this->db->get_one('SELECT `manager_valid` FROM `renren_page_manager` WHERE `page_id` = ? AND `manager_id` = ?',array($renren_page_manager_data['page_id'],$renren_page_manager_data['manager_id']));
					if( $_page_manager ) {	# 数据库中有这个主页，有这个管理员，则更新管理员状态为有效。
						if( $_page_manager['manager_valid']!=1 ) $this->db->update( 'renren_page_manager', array('manager_valid'=>1), $renren_page_manager_data );
					} else {	# 数据库中有这个主页，但没有这个管理员，则添加。
						$this->db->insert( 'renren_page_manager', $renren_page_manager_data );
					}
				}
				if( $_info = $this->db_get_hollow_info(array('page_id'=>$rr_response[$i]['page_id']),'','rr') ) {	# 数据库中有这个主页，读取信息。
					$rr_response[$i]['hollow_sid'] = $_info['hollow_sid'];
					$rr_response[$i]['expire_num'] = $_info['expire_num'];
					$rr_response[$i]['expire_time'] = $_info['expire_time'];
					$rr_response[$i]['data_prefix'] = $_info['data_prefix'];
					$rr_response[$i]['data_suffix'] = $_info['data_suffix'];
					$rr_response[$i]['hollow_title'] = $_info['hollow_title'];
					$rr_response[$i]['hollow_state'] = $_info['hollow_state'];
					$rr_response[$i]['hollow_count'] = $_info['hollow_count'];
				}
			}
		}
		return $rr_response;	//返回结果
	}
	/**
	* TO-DO: 新建树洞
	* RET  : false 新建失败
	*        array 新建成功
	*/
	function db_create_hollow( $data, $ex_data, $type='rr' ) {
		switch( strtoupper( substr( $type, 0, 2 ) ) ) {
		case 'WB':
			break;
		case 'QZ':
			break;
		case 'RR':	# 新建人人树洞
		default:
			$data['hollow_sid'] = $this->create_hollow_sid('rr');
			$data['expire_num'] = 500;
			$data['expire_time'] = (time()+30*24*60*60);
			$data['hollow_state'] = 0;
			$data['data_suffix'] = '--匿名发布自http://hollow.sinaapp.com/?sid='.str_replace(array("{","-","}"),"",$data['hollow_sid']);
			if( $this->db->insert( 'hollow', $data ) ) {
				if( $_hollow = $this->db->get_one('SELECT `hollow_id` FROM `hollow` WHERE `hollow_sid` = ?', array($data['hollow_sid'])) ) {
					$this->db->insert( 'renren_page', array('hollow_id'=>$_hollow['hollow_id'],'page_id'=>$ex_data['page_id']) );
					$ex_data['manager_valid'] = 1;
					$this->db->insert( 'renren_page_manager', $ex_data );
				}
			}
		}
	}
	/**
	* TO-DO: 检查主页是否存在在数据库中（是否已添加）
	* RET  : false 不存在
	*        true  存在
	*/
	function db_check_hollow_exist( $hollow_sid ) {								# 公共函数类
		$hollow_sid = $this->check_hollow_sid($hollow_sid);
		$_bool = $this->db->get_col( "SELECT COUNT(1) FROM `hollow` WHERE `hollow_sid` = ?", array($hollow_sid) );
		if (!$_bool) {
			return false; // 树洞不存在
		} else {
			return $_bool; // 树洞存在
		}
	}
	/**
	* TO-DO: 设置主页信息
	* RET  : 0 设置失败
	*        1 设置成功
	* 		-1 没有权限管理该树洞
	* 		-2 树洞页不存在
	*/
	function db_set_hollow_info( $hollow_sid, $data ) {							# 公共函数类
		$hollow_sid = $this->check_hollow_sid($hollow_sid);
		$hollow_info = $this->db_check_hollow_exist( $hollow_sid );
		if ( !$hollow_info ) { return -2; }
		$is_admin = false;
		$account_list = $this->get_signed_account_list();
		switch( strtolower( substr( $hollow_sid, 0, 2) ) ) {
			case 'wb':
			break;
			case 'qz':
			break;
			case 'rr':
			default:
				foreach( $account_list['rr'] as $rr_id => $rr_data ){
					if($this->db->get_col( "SELECT COUNT(1) FROM `hollow` LEFT JOIN `renren_page` ON `hollow`.`hollow_id`=`renren_page`.`hollow_id` AND `hollow`.`hollow_sid`=? INNER JOIN (SELECT `page_id` FROM `renren_page_manager` WHERE `manager_id`=? AND `manager_valid`=1) AS `renren_page_manager` ON `renren_page_manager`.`page_id`=`renren_page`.`page_id`", array($hollow_sid,$rr_id) )) {
						$is_admin = true;
						break;
					}
				}
			break;
		}
		if (!$is_admin) {return -1;}
		
		if( $_result = $this->db->update( 'hollow', $data, array('hollow_sid'=>$hollow_sid) ) ) {
			return 1; // 设置值成功
		} else {
			return 0; // 设置值失败
		}
	}
	/**
	* TO-DO: 获取主页信息
	* RET  : false  获取失败
	*        数组集 获取成功并返回
	*/
	function db_get_hollow_info( $where, $fields = '', $type = 'rr' ) {		# 公共函数类
		$values = $wheres = array();						//建立数据
		$fields = ($fields == '') ? '*' : '`'.implode('`,`',$fields).'`';
		
		if ( is_array( $where ) )
			foreach ( $where as $c => $v ) {						//循环构建需要的条件参数
				$wheres[] = "`$c` = ?";
				$values[] = $v;
			}
		else
			return false;
		switch( strtolower( substr( $type, 0, 2) ) ) {
			case 'wb':
			break;
			case 'qz':
			break;
			case 'rr':
				$_result = $this->db->get_one('SELECT ' . $fields .' FROM `hollow` LEFT JOIN `renren_page` ON `hollow`.`hollow_id`=`renren_page`.`hollow_id` WHERE ' . implode(' AND ',$wheres), $values);
			break;
			default:
				$_result = $this->db->get_one('SELECT ' . $fields .' FROM `hollow` WHERE ' . implode(' AND ',$wheres), $values);
			break;
		}
		if($_result) {
			return $_result; // 获取值成功
		} else {
			return false; // 获取值失败
		}
	}
	/**
	* TO-DO: 发送新状态
	* RET  : 1 发送完成
	*        其他提示文字 出错信息
	*/
	function db_set_status( $hollow_id, $status_str, $status_prefix='', $status_suffix='' ) {
		# 验证用户发送时间间隔
		$post_time = time();
		$post_ip   = ip2long($_SERVER['REMOTE_ADDR']);
		if( $post_time - $_SESSION['last_post_time'] < 5 ) return '您在5秒之内只能提交一次哦～'; else $_SESSION['last_post_time'] = $post_time;
		# 验证用户发送长度
		$status_length += 2 * str_word_count( preg_replace(
			'/[\x{4e00}-\x{9fa5}]/iu', 
			'', 
			str_replace(
				array('，','。','？','！','…','；','、','、'),
				' ',
				$status_str
			),
			-1,
			$status_length
		) );
		if( $status_length<5 ) return "喵～\n至少5个字(3个单词)～\n\n因为近期树洞刷屏严重\n我们不得已采取某些限制\n\n树洞内容反映着大家的素质\nSo,请同学们三思而后行\n多发些有意义的内容吧"; // 15,8
		# 验证蛋疼的用户
		if(preg_match('/(测试|试试|真的).*匿名/iu',$status_str)) { return "不要无聊。。\n这个真的是匿名的…\n行行好不要刷屏了…"; }
		if(preg_match('/知道.*谁发的/iu',$status_str)) { return "不要疑问。。\n真的是不知道谁发的…\n行行好不要刷屏了…"; }
		# 验证用户发送敏感字
		$_offensive_words = $this->db->get_all('SELECT * FROM `offensive_word` WHERE `word_type`=0 AND `word_valid`=1 AND (`hollow_id`=-1 OR `hollow_id`=?)', array($hollow_id));
		foreach( $_offensive_words as $_offensive_word ) {
			if( $_offensive_word['word_type']==0 ) {
				foreach( explode(',',$_offensive_word['word_data']) as $offensive_word ) {
					if( false !== stripos( $status_str.'|'.preg_replace('/[^\x{4e00}-\x{9fa5}]/iu','',$status_str).'|'.preg_replace('/[^a-zA-Z]/iu','',$status_str), $offensive_word ) )
						return '树洞不喜欢“'.$offensive_word.'”这样奇怪的话。';
				}
			}
		}
		# 读数据库最近发送记录。
		$_result   = $this->db->get_one('SELECT `record_id`,`record_time`,`record_data`,`record_state`,`record_error_code`,`record_error_msg` FROM `record` WHERE `hollow_id` = ? AND `record_ip` = ? ORDER BY `record_time` DESC LIMIT 1', array($hollow_id,$post_ip));
		# 测试功能：树洞频率限制。
		if($hollow_id==5) {
			@$last_post_success_time_data = explode('.',$_COOKIE['lt']);
			if( count($last_post_success_time_data)<2 ) $last_post_success_time_data = array('','');
			if( md5('tms_hollow_md5_salt'.$last_post_success_time_data[1])==$last_post_success_time_data[0] ) {
				if( (int)$last_post_success_time_data[1] + 60*60*12 > $post_time ) return '12小时';
			} else if( $_result ) {
				setcookie("lt", md5('tms_hollow_md5_salt'.$_result['record_time']).'.'.$_result['record_time'], $post_time+31536000);
			} else {
				setcookie("lt", md5('tms_hollow_md5_salt'.$post_time).'.'.$post_time, $post_time+31536000);
			}
		}
		# 验证通过，5号洞为我的测试洞，就此中断
		//if($hollow_id==5)return '看到这句话说明可以发送成功了，测试通过～';
		# 根据最近发送记录，判断是否是重复提交。
		if($_result) { # 该IP地址($post_ip)在该树洞($hollow_id)曾经发表过内容。
			if( $_result['record_state']==0 ) {	# 该IP曾因人人服务器出错而存在未发表出去的状态
				if( $_result['record_data']==$status_str ) {	# 相同内容的状态再次发表
					# 更新数据库
					$this->db->update('record',array( 'record_time'=>$post_time ), array( 'record_id'=>$_result['record_id'] ));
				} else if( $post_time - $_result['record_time'] < 60*60 ) {	# 不同内容的状态短时间内再次发表
					# 更新数据库
					$this->db->update('record',array( 'record_time'=>$post_time, 'record_data'=>$status_str ), array( 'record_id'=>$_result['record_id'] ));
				} else {	# 很久之前（可能是不同用户IP重复）发送失败的状态，直接忽略。
					# 写入数据库
					$this->db->insert('record',array( 'hollow_id'=>$hollow_id, 'record_time'=>$post_time, 'record_ip'=>$post_ip, 'record_data'=>$status_str, 'record_user'=>$this->get_signed_account_list(false) ));
				}
			} else if( $_result['record_data']==$status_str ) {
				return '请不要重复发送已经成功发送的信息～'; 	# 重复提交
			} else if( $post_time - $_result['record_time'] < 5*60 ) {
				return '您在5分钟之内只能发送一次哦～'; 		# 发送超频
			} else {
				# 写入数据库
				$this->db->insert('record',array( 'hollow_id'=>$hollow_id, 'record_time'=>$post_time, 'record_ip'=>$post_ip, 'record_data'=>$status_str, 'record_user'=>$this->get_signed_account_list(false) ));
			}
		} else {	# 该IP地址($post_ip)在该树洞($hollow_id)没有发表过内容。
			# 写入数据库
			$this->db->insert('record',array( 'hollow_id'=>$hollow_id, 'record_time'=>$post_time, 'record_ip'=>$post_ip, 'record_data'=>$status_str, 'record_user'=>$this->get_signed_account_list(false) ));
		}
		
		# 确认写入数据库成功.
		$_result = $this->db->get_one('SELECT * FROM `record` LEFT JOIN `hollow` ON `record`.`hollow_id` =`hollow`.`hollow_id` WHERE `record`.`hollow_id` = ? AND `record`.`record_time` = ? AND `record`.`record_ip` = ? LIMIT 1', array($hollow_id, $post_time, $post_ip));
		
		if ( $_result ) {
			switch( strtoupper( substr( $_result['hollow_id'], 0, 2 ) ) ) {
			case 'WB':
				break;
			case 'QZ':
				break;
			case 'RR':	# 向人人网发起请求
			default:
				$tokens = $this->db->get_all('SELECT `renren_page`.`page_id`,`user_id`,`access_token`,`refresh_token`,`expires_time`,`access_scope` FROM `renren_page` RIGHT JOIN `renren_page_manager` ON `renren_page`.`page_id`=`renren_page_manager`.`page_id` LEFT JOIN `renren_oauth` ON `renren_page_manager`.`manager_id`=`renren_oauth`.`user_id` WHERE `renren_page_manager`.`manager_valid`=1 AND `renren_page`.`hollow_id`= ? ORDER BY `renren_oauth`.`last_error_time` ASC',array($hollow_id));
				$rpm = new TmsRenRenPageManager();
				$rr_response = array('error_code'=>'0','error_msg'=>'该树洞主页授权已被取消，请联系相应人人主页管理员。');
				foreach( $tokens as $i=>$token ) {
					$rr_response = $rpm->rr_page_set_status($token['page_id'],$token,$status_prefix.$status_str.$status_suffix);
					if($rr_response['result']==1) {	# 发送成功记录到数据库
						
						$this->db->update('hollow',array('hollow_count'=>$_result['hollow_count']+1),array('hollow_id'=>$_result['hollow_id']));
						$this->db->update('record',array('record_state'=>1),array('record_id'=>$_result['record_id']));
						return $rr_response['result'];	#返回结果
					} else {	# 发送失败记录到数据库
						$this->db->update('renren_oauth',array('last_error_time'=>time(),'last_error_code'=>$rr_response['error_code'],'last_error_msg'=>$rr_response['error_msg']),array('user_id'=>$token['user_id']));
						if( $rr_response['error_code']==20301 ) $this->db->update('renren_page_manager',array('manager_valid'=>0),array('page_id'=>$token['page_id'], 'manager_id'=>$token['user_id']));	# 该账号没有公共主页管理权限。
					}
				}
				# 发送失败记录到数据库
				$this->db->update('record',array('record_error_code'=>$rr_response['error_code'],'record_error_msg'=>$rr_response['error_msg']),array('record_id'=>$_result['record_id']));
				# 返回结果给用户。
				if( $rr_response['error_code']==7 )
					return '发送频率超过人人网限制，5分钟后再试试吧。';	# 程序走到这里，只能是所有账户都已出错，返回最后一个错误给用户。
				else
					return '['.$rr_response['error_code'].']'.$rr_response['error_msg'];	#返回结果
				break;
			}
			return '错误：未知的树洞类型。';
		} else {
			return '系统错误：写入数据库失败。';	# 写入数据库不成功
		}
		
	}
	/**
	* TO-DO: 获取状态记录
	* RET  : 数据集
	*/
	function db_get_status( $hollow_id, $start_index = 0, $limit_index = 20 ) {
		return $this->db->get_all("SELECT * FROM `record` WHERE `record_state`=1 AND `hollow_id` = ? ORDER BY `record_id` LIMIT $start_index, $limit_index", array($hollow_id));
	}
	/**
	* TO-DO: 生成新的树洞SID
	* RET  : SID
	*/	
	function create_hollow_sid( $type='rr' ) {
		$charid = strtoupper(md5(uniqid(mt_rand(), true)));
		$hyphen = chr(45);// "-"
		$uuid = chr(123)// "{"
		.substr($charid, 0, 8).$hyphen
		.substr($charid, 8, 4).$hyphen
		.substr($charid,12, 4).$hyphen
		.substr($charid,16, 4).$hyphen
		.substr($charid,20,12)
		.chr(125);// "}"
		switch( strtolower( substr( $type, 0, 2) ) ) {
		case 'wb':
			$uuid = 'WB'.$uuid ;
			break;
		case 'qz':
			$uuid = 'QZ'.$uuid ;
			break;
		case 'rr':
			$uuid = 'RR'.$uuid ;
			break;
		default:
			break;
		}
		return $uuid;
	}
	function check_hollow_sid($sid){
		switch(strlen($sid)){
		case 32:
			$sid = 'RR{'.substr($sid,0,8).'-'.substr($sid,8,4).'-'.substr($sid,12,4).'-'.substr($sid,16,4).'-'.substr($sid,20,12).'}';
			break;
		case 34:
			$sid = substr($sid,0,2).'{'.substr($sid,2,8).'-'.substr($sid,10,4).'-'.substr($sid,14,4).'-'.substr($sid,18,4).'-'.substr($sid,22,12).'}';
			break;
		}
		return $sid;
	}
	/**
	* TO-DO: 初始化系统数据库（安装使用）
	* RET  : 1 完成
	*        0 失败
	*/
	function install_db() {													# 初始化数据库
		$_page = $this->db->get_one("SELECT count(1) FROM page",array());
		if($_page) { return 0;}
		if($this->db->execute("CREATE TABLE page (
			uid INT UNSIGNED NOT NULL AUTO_INCREMENT, 
			sid VARCHAR(255),
			page_id INT UNSIGNED NOT NULL,
			expire_num INT UNSIGNED DEFAULT 10,
			expire_time INT UNSIGNED DEFAULT 0,
			access_token VARCHAR(255),
			refresh_token VARCHAR(255),
			hollow_state INT DEFAULT 0,
			hollow_title VARCHAR(255) NOT NULL DEFAULT '树洞秘密' ,
			hollow_count INT DEFAULT 0 ,
			data_prefix	varchar(255) DEFAULT '',
			data_suffix	varchar(255) DEFAULT '',
			PRIMARY KEY(uid),
			UNIQUE u_sid (sid),
			INDEX i_pid (pid)
			)",array())
		) {
			$this->db->execute("CREATE TABLE `record` (
				`record_id` INT UNSIGNED NOT NULL AUTO_INCREMENT, 
				`page_id` INT UNSIGNED NOT NULL,
				`record_time` INT UNSIGNED DEFAULT 0,
				`record_ip` INT UNSIGNED DEFAULT 0,
				`record_data` TEXT NOT NULL ,
				`record_state` INT NOT NULL DEFAULT 0,
				`record_error_code` INT NOT NULL DEFAULT '0' COMMENT '发送失败代码',
				`record_error_msg` TEXT COMMENT '发送错误信息',
				PRIMARY KEY(rid),
				INDEX i_pid (pid),
				INDEX i_time (rtime)
				)",array());
			return 1;
		} else {
			return 0;
		}
	}
}
?>