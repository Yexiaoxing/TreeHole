<?php
/** 功能：实现PHP的MVC分离。
  * 作者：翟一鸣tinymins
  * 版本：2013-4-13 v1.1
 */
class TmsTemplate {
	var $variants;
	var $template_dir;
	var $left_delimiter;
	var $right_delimiter;
	function __construct( ) {
		$this->variants = array();
		$this->template_dir = "";
		$this->left_delimiter = "<?";
		$this->right_delimiter = "?>";
	}
	/**
	* TO-DO: 注册变量值
	* Ret  : void
	*/
	function assign( $key, $value ) {
		$this->variants[$key] = $value;
	}
	/**
	* TO-DO: 获取模板替换变量的结果
	* Ret  : html code
	*/
	function fetch( $template_name ) {
		$template_code = file_get_contents($this->template_dir.$template_name);
		$regex_left_delimiter = $this->regex_escape($this->left_delimiter);
		$regex_right_delimiter = $this->regex_escape($this->right_delimiter);
		$regex_loop_exp = '/'.$regex_left_delimiter.'\s*section\s+name=(\w+)\s+loop=\$(\w+)\s*'.$regex_right_delimiter.'(.*?)'.$regex_left_delimiter.'\/section'.$regex_right_delimiter.'/uis';
		preg_match_all( $regex_loop_exp, $template_code, $matches );
		for( $i=0; $i<count($matches[0]); $i++ ) {	# section name=arrIndex loop=$arrName (...) /foreach
			$tpl_loop_index = $matches[1][$i];		# name=(\w+)
			$tpl_arr_name 	= $matches[2][$i];		# loop=\$(\w+)
			$tpl_loop_html 	= $matches[3][$i];		# (...)
			$out_loop_html 	= '';
			foreach( $this->variants[$tpl_arr_name] as $arr_k=>$arr_v ) {	# <?=$arr_v.store_sending_time? >
				$tmp_loop_html = $tpl_loop_html;
				$regex_loop_exp = '/'.$regex_left_delimiter.'\$'.$tpl_arr_name.'\['.$tpl_loop_index.'\]'.'([0-9a-zA-Z\._]*)'.$regex_right_delimiter.'/uis';
				preg_match_all($regex_loop_exp, $tmp_loop_html, $loop_matches);
				// print_r($loop_matches);
				for( $j=0; $j<count($loop_matches[0]); $j++ ) {
					$value = $this->get_array_value($arr_v,$loop_matches[1][$j]);
					$tmp_loop_html = str_replace($loop_matches[0][$j], $value, $tmp_loop_html);
				}
				$tmp_loop_html = str_replace($this->left_delimiter.'=$'.$tpl_loop_index.$this->right_delimiter, $arr_k, $tmp_loop_html);
				$out_loop_html 	.= $tmp_loop_html;
			}
			$template_code = str_replace($matches[0][$i], $out_loop_html, $template_code);
		}
		foreach( $this->variants as $k => $v ) {
			if(!is_array($v)) {
				$template_code = str_replace($this->left_delimiter.'=$'.$k.$this->right_delimiter, $v, $template_code);
			}
		}
		return $template_code;
	}
	/**
	* TO-DO: 输出模板替换变量的结果
	* Ret  : void
	*/
	function display( $template_name ) {
		echo $this->fetch( $template_name );
	}
	/**
	* TO-DO: 转义正则表达式特殊字符
	* Ret  : String 转义后的字符串
	*/
	function regex_escape( $str ) {
		$str = str_replace( '\\', '\\\\', $str );
		$str = str_replace( '/', '//', $str );
		$str = str_replace( '^', '\\^', $str );
		$str = str_replace( '$', '\\$', $str );
		$str = str_replace( '*', '\\*', $str );
		$str = str_replace( '+', '\\+', $str );
		$str = str_replace( '?', '\\?', $str );
		$str = str_replace( '(', '\\(', $str );
		$str = str_replace( ')', '\\)', $str );
		$str = str_replace( '[', '\\[', $str );
		$str = str_replace( ']', '\\]', $str );
		$str = str_replace( '{', '\\{', $str );
		$str = str_replace( '}', '\\}', $str );
		$str = str_replace( '|', '\\|', $str );
		$str = str_replace( '.', '\\.', $str );
		return $str;
	}
	/**
	* TO-DO: 获取数组指定键的值
	* Ret  : value
	* Sampl: get_array_value( $arr, 'i.name' ) = $arr['i']['name']
	*/
	function get_array_value( $arr, $key ){
		$key = preg_replace('/\.+$/usi','',$key);
		$key = preg_replace('/^\.+/usi','',$key);
		$keys= explode('.',$key);
		$key = substr( $key, strlen($keys[0]) );
		if( empty($keys[0]) ) {
			return $arr;
		} else if( !is_array($arr) ) {
			throw new Exception('[TMS] Unknow String to Array Conv. ');
		} else if( array_key_exists( $keys[0], $arr ) ) {
			$arr = $arr[$keys[0]];
		} else {
			throw new Exception('[TMS] Undefined Array Key: '.$keys[0]);
		}
		if( count( $keys ) <= 1 ) {
			return $arr;
		} else {
			return $this->get_array_value( $arr, $key );
		}
	}
}
/**
 * 日志：
 * 2013-3-23 v1.0 版本创建
 * 2013-4-13 v1.1 增加assign数组/在模板中使用section语句
 * 
 **/
?>