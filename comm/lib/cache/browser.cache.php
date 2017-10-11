<?php
//利用 etag 控制客户端，刷新缓存 
//by ziv 2011/4/2
class browser_cache{

	//$v 版本 $time 有效时间 秒 $other 其他关键字
	static function get_etag($v='',$time=300,$other=''){
		if(!is_numeric($time)) return false;
		$t1=time();
		$t=floor($t1/$time);
		$etag = md5($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].$v.$t.$other);
		return $etag;
	}

	static function match($v='',$time=300,$other=''){
		if(!is_numeric($time)) return false;
		$etag = self::get_etag($v,$time,$other);
		
		$inm = getenv("HTTP_IF_NONE_MATCH"); 
		if($inm==$etag && !$_GET['__DEBUG']) {header ("HTTP/1.0 304 Not Modified");exit;}
		
		header ("ETag: ".$etag);
		return true; 
	}

	static function set_no_cache(){
		header('Expires: -1');
		header('Cache-Control: max-age=0'); 
		header('Pragma: No-Cache'); 
		header ("ETag: ".md5(microtime()));
		return true; 
	}

	static function set_no_expires(){
		header('Expires: -1');
		header('Cache-Control: max-age=0'); 
		return true; 
	}
	
	static function set_expires($time=300){
		if(!is_numeric($time)) return false;
		header('Cache-Control: max-age='.$time); 
		header('Pragma: Cache'); 
		return true; 
	}

}//end class
