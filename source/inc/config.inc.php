<?php
/*
 * 总体配置文件，包括API Key, Secret Key，以及所有允许调用的API列表
 * This file for configure all necessary things for invoke, including API Key, Secret Key, and all APIs list
 *
 * @Modified by mike on 17:54 2011/12/21.
 * @Modified by Edison tsai on 16:34 2011/01/13 for remove call_id & session_key in all parameters.
 * @Created: 17:21:04 2010/11/23
 * @Author:	Edison tsai<dnsing@gmail.com>
 * @Blog:	http://www.timescode.com
 * @Link:	http://www.dianboom.com
 */

$config					= new stdClass;

$config->APIURL			= 'http://api.renren.com/restserver.do'; //RenRen网的API调用地址，不需要修改
$config->APIKey			= '00000000000000000000000000000000';	//你的API Key，请自行申请
$config->SecretKey		= '00000000000000000000000000000000';	//你的API 密钥
$config->RedirectURI	= 'http://sd.fanfor.cn/redirect.php';	//你的API Redirect URI
$config->APIVersion		= '1.0';	//当前API的版本号，不需要修改
$config->decodeFormat	= 'json';	//默认的返回格式，根据实际情况修改，支持：json,xml
/*
 * 个人项目配置文件
 */
define("TM_DB_HOST",SAE_MYSQL_HOST_M);
define("TM_DB_NAME",SAE_MYSQL_DB);
define("TM_DB_PORT",SAE_MYSQL_PORT);
define("TM_DB_USER",SAE_MYSQL_USER);
define("TM_DB_PW",SAE_MYSQL_PASS);
define("TMS_SESSION_DIR",'RenRenHollow');

header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
?>