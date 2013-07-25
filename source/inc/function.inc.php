<?php
	function save_token($newrefreshtoken,$newaccesstoken) {
		$file = fopen('userconfig.inc.php','w');
		fwrite($file,"<?php\n\$refreshtoken = '$refreshtoken';\n\$accesstoken = '$accesstoken';\n?>");
		fclose($file);
		return "\$refreshtoken = $refreshtoken<br/>\$accesstoken = $accesstoken;";
	}
	function create_guid() {
		$charid = strtoupper(md5(uniqid(mt_rand(), true)));
		$hyphen = chr(45);// "-"
		$uuid = chr(123)// "{"
		.substr($charid, 0, 8).$hyphen
		.substr($charid, 8, 4).$hyphen
		.substr($charid,12, 4).$hyphen
		.substr($charid,16, 4).$hyphen
		.substr($charid,20,12)
		.chr(125);// "}"
		return $uuid;
	}
	function get_var_curl($url,$post_string='',$cookie='',$Ref=''){ // #发送给其他的SIMSIMI中转服务器请求数据
		if( stripos($url, '://')!==false ) {
			$urlprot = preg_replace('/:\\/\\/.*$/i','',$url);
			$url  = preg_replace('/^https*:\\/\\//i','',$url);
			$host = preg_replace('/\\/.*$/i','',$url);
			$url  = preg_replace('/^.*?\\//i','/',$url);
		} else if( stripos($url, '/')===0 ) {
			$urlprot = preg_replace('/\\/.*$/i','',$_SERVER['SERVER_PROTOCOL']);
			$host = $_SERVER['REMOTE_HOST'];
		} else {
			$urlprot = preg_replace('/\\/.*$/i','',$_SERVER['SERVER_PROTOCOL']);
			$host = $_SERVER['REMOTE_HOST'];
			$url  = preg_replace('/\\/[^\\/]*$/i','/',$_SERVER['REQUEST_URI']).$url;
		}
		if( strlen($Ref)===0 ) {$Ref = "$urlprot://$host/";}
		$header = array();
		$header[]= 'Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, text/html, * '. '/* ';  
		$header[]= 'Accept-Language: zh-cn ';  
		$header[]= 'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:13.0) Gecko/20100101 Firefox/13.0.1';  
		$header[]= 'Host: '.$host;  
		$header[]= 'Connection: Keep-Alive ';  
		$header[]= 'Cookie: '.$cookie;//JSESSIONID=2D96E7F39FBAB9B28314607D0328D35F
		$Ch = curl_init();
		$Options = array(
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_URL => "$urlprot://$host$url",       
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS => $post_string,
			CURLOPT_REFERER	=> $Ref,
		);
		curl_setopt_array($Ch, $Options);
		$responseHTML = curl_exec($Ch);
		curl_close($Ch);
		return $responseHTML ;
	}
?>