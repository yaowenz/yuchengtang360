<?php
/*
各种字符串处理类
updated by zx 2010-5-18
*/
class string {

	static $code_to = 'utf-8';
	
	//截断字符串 $s ,$n个字，自动加...
	function str_cut($s,$n)
	{
		mb_internal_encoding(self::$code_to); 
		if(mb_strlen($s)<=$n) return $s;
		return mb_substr($s,0,$n-3) . '...';
	}

	function string() {
		die("Class string can not instantiated!");
	}
	 
	//随机字符串 
	function random_str($nc, $a='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {   
	   $l=strlen($a)-1; $r='';   
	   while($nc-->0) $r.=$a{mt_rand(0,$l)};   
	   return $r;   
	}   


	//字符串切割
	function substring($str, $start=0, $limit=12) {
		$encoding = mb_detect_encoding($str, array('ASCII','UTF-8','GB2312','GBK','BIG5'));
		if('gbk'==strtolower($encoding)){
			$strlen=strlen($str);
			if ($start>=$strlen){
				return $str;
			}
			$clen=0;
			for($i=0;$i<$strlen;$i++,$clen++){
				if(ord(substr($str,$i,1))>0xa0){
					if ($clen>=$start){
						$tmpstr.=substr($str,$i,2);
					}
					$i++;
				}else{
					if ($clen>=$start){
						$tmpstr.=substr($str,$i,1);
					}
				}
				if ($clen>=$start+$limit){
					break;
				}
			}
			$str=$tmpstr;
		}else{
			$patten = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
			preg_match_all($patten, $str, $regs);
			$v = 0; $s = '';
			for($i=0; $i<count($regs[0]); $i++){
				(ord($regs[0][$i]) > 129) ? $v += 2 : $v++;
				$s .= $regs[0][$i];
				if($v >= $limit * 2){
					break;
				}
			}
			$str=$s;
		}
		return $str;
	}
	
	//字符串长度
	function hstrlen($str) {
		$encoding = mb_detect_encoding($str, array('ASCII','UTF-8','GB2312','GBK','BIG5'));
		if('gbk'==strtolower($encoding)){
			$length=strlen($str);
		}else{
			$length=floor(2/3*strlen($str));
		}
		return $length;
	}
	
	//遍历数组变大写
	function hstrtoupper($str){
		if (is_array($str)){
			foreach ($str as $key => $val){
				$str[$key] = string::hstrtoupper($val);
			}
		}else{
			$i=0;
			$total = strlen($str);
			$restr = '';
			for ($i=0; $i<$total; $i++){
				$str_acsii_num = ord($str[$i]);
				if($str_acsii_num>=97 and $str_acsii_num<=122){
					$restr.=chr($str_acsii_num-32);
				}else{
					$restr.=chr($str_acsii_num);
				}
			}
		}
		return $restr;
	}
	
	//遍历数组变小写
	function hstrtolower($string){
		if (is_array($string)){
			foreach ($string as $key => $val){
				$string[$key] = string::hstrtolower($val);
			}
		}else{
			$string = strtolower($string);
		}
		return $string;
	}
	
	//遍历数组 addslashes
	function haddslashes($string, $force = 0) {
		if(!MAGIC_QUOTES_GPC || $force) {
			if(is_array($string)) {
				foreach($string as $key => $val) {
					$string[$key] = string::haddslashes($val, $force);
				}
			}else {
				$string = addslashes($string);
			}
		}
		return $string;
	}
	
	//遍历数组 stripslashes
	function hstripslashes($string) {
		while(@list($key,$var) = @each($string)) {
			if ($key != 'argc' && $key != 'argv' && (strtoupper($key) != $key || ''.intval($key) == "$key")) {
				if (is_string($var)) {
					$string[$key] = stripslashes($var);
				}
				if (is_array($var))  {
					$string[$key] = string::hstripslashes($var);
				}
			}
		}
		return $string;
	}

	//去除空行空格
	function convercharacter($str){
		$str=preg_replace('/\n/',"",$str);
		$str=preg_replace('/\r/',"",$str);
		$str=preg_replace('/\t/',"",$str);
		/*
		$str=str_replace('\\\r',"",$str);
		$str=str_replace('\\\n',"",$str);
		$str=str_replace('\n',"",$str);
		$str=str_replace('\r',"",$str);
		*/
		return $str;
	}

	//去除空行,2个空格转换成1个空格
	function change_space($str){
		$str=preg_replace('/\n/',"",$str);
		$str=preg_replace('/\r/',"",$str);
		$str=preg_replace('/\t/',"",$str);
		$str=str_replace('  ',"",$str);
		return $str;
	}
	
	//获取拼音首字母
	function getfirstletter($str) {

		$str=mb_ereg_replace("窦","d",$str); 
		$str=mb_ereg_replace("鼬","y",$str);
		$str=mb_ereg_replace("驯","x",$str);
		$str=mb_ereg_replace("侏","z",$str);

		$encoding = mb_detect_encoding($str, array('ASCII','UTF-8','GB2312','GBK','BIG5'));
		if($encoding=='UTF-8'){
			$str=mb_convert_encoding($str, "gbk", $encoding);
		}
		
		$dict=array(
			'a'=>0xB0C4,'b'=>0xB2C0,'c'=>0xB4ED,'d'=>0xB6E9,
			'e'=>0xB7A1,'f'=>0xB8C0,'g'=>0xB9FD,'h'=>0xBBF6,
			'j'=>0xBFA5,'k'=>0xC0AB,'l'=>0xC2E7,'m'=>0xC4C2,
			'n'=>0xC5B5,'o'=>0xC5BD,'p'=>0xC6D9,'q'=>0xC8BA,
			'r'=>0xC8F5,'s'=>0xCBF9,'t'=>0xCDD9,'w'=>0xCEF3,
			'x'=>0xD1B8,'y'=>0xD4D0,'z'=>0xD7F9,
			);
		$letter = substr($str, 0, 1);
		$letter_ord = ord($letter);
		if($letter_ord >= 176 && $letter_ord <= 215){
			$num = '0x'.bin2hex(substr($str, 0, 2));
			foreach ($dict as $k=>$v){
				if($v>=$num) break;
			}
			return $k;
		}elseif(($letter_ord>64 && $letter_ord<91) || ($letter_ord>96 && $letter_ord<123)){
			return $letter;
		}elseif($letter>='0' && $letter<='9'){
			return $letter;
		}else{
			return '-';
		}
	}
	
	//过滤特殊字符
	function stripspecialcharacter($string) {
		$string=trim($string);
		$string=str_replace("&","",$string);
		$string=str_replace("\'","",$string);
		$string=str_replace("'","",$string);
		$string=str_replace("&amp;amp;","",$string);
		$string=str_replace("&amp;quot;","",$string);
		$string=str_replace("\"","",$string);
		$string=str_replace("&amp;lt;","",$string);
		$string=str_replace("<","",$string);
		$string=str_replace("&amp;gt;","",$string);
		$string=str_replace(">","",$string);
		$string=str_replace("&amp;nbsp;","",$string);
		$string=str_replace("\\\r","",$string);
		$string=str_replace("\\\n","",$string);
		$string=str_replace("\n","",$string);
		$string=str_replace("\r","",$string);
		$string=str_replace("\r","",$string);
		$string=str_replace("\n","",$string);
		$string=str_replace("'","&#39;",$string);
		$string=nl2br($string);
		return $string;
	}
	
	
	//去除js
	function stripscript($string){
		$pregfind=array("/<script.*>.*<\/script>/siU",'/on(mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i');
		$pregreplace=array('','',);
		$string=preg_replace($pregfind,$pregreplace,$string);
		return $string;
	}

	//遍历去 html php 字符
	function strip_tags_deep($value){  
		$value = is_array($value)?array_map(array('string', 'strip_tags_deep'), $value):strip_tags($value);
		return $value; 
	}
	
	//遍历 urldecode
	function urldecode_deep($value){  
		$value = is_array($value)?array_map(array('string', 'urldecode_deep'), $value):urldecode($value);   
		return $value; 
	}

	//遍历 urlencode
	function urlencode_deep($value){  
		$value = is_array($value)?array_map(array('string', 'urlencode_deep'), $value):urlencode($value);   
		return $value;
	} 
	
	/*
		遍历 转换编码 如 urf-8 GBK 等 
		用法如：string::$code_to = 'utf-8';$a = string::convert_encoding_deep($a);
	*/
	function convert_encoding_deep($value){
		if(is_array($value))
		{
			$value = array_map(array('string', 'convert_encoding_deep'),$value);
		}
		else
		{
			$codeing = mb_detect_encoding($value, array('ASCII','UTF-8','GB2312','GBK','BIG5')); 
			$value = mb_convert_encoding($value,self::$code_to,$codeing);
		}
		return $value; 
	} 
	
	/*
		遍历过滤html
	*/
	function dhtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = string::dhtmlspecialchars($val);
		}
	} else {
		$string = htmlspecialchars($string, ENT_QUOTES);
	}
	return $string; 
	}
	
	/*
		遍历过滤html 老版本
	*/
	function dhtmlspecialchars_old($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = string::dhtmlspecialchars_old($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1',
		str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
	}

	
	//字符串转换成js能赋值的字符串格式 
	function str_to_js($string) {
		$string=trim($string);
		$string=htmlspecialchars($string,ENT_QUOTES);
		$string=str_replace("\\\r","<br/>",$string);
		$string=str_replace("\\\n","",$string);
		$string=str_replace("\n","",$string); 
		$string=str_replace("\r","<br/>",$string);
		return $string; 
	}

}
?>