<?php
/*
各种ip处理类
by zx 2010-5-26
*/
class ip_class{
	
	//获取客户端ip
	static function get_ip(){
	   if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
			   $ip = getenv("HTTP_CLIENT_IP");
		   else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			   $ip = getenv("HTTP_X_FORWARDED_FOR");
		   else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			   $ip = getenv("REMOTE_ADDR");
		   else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			   $ip = $_SERVER['REMOTE_ADDR'];
		   else
			   $ip = "unknown";
	   return($ip);
	}
	
	//判断是否是有效ip地址
	static function verify_ip($ipaddres){
		$preg="/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
		if(preg_match($preg,$ipaddres))return true;
		return false;
	}
	
	//通过ip取地区
	static function get_city_by_ip_from_cache($ip)
	{
		$time=3600;
		require_once dirname(__FILE__).'/../cache/cache.class.php'; 
		$key = md5('get_city_by_ip_from_cache'.$ip); 
		$cache = new temp_cache; 
		$info = $cache->get($key);
		if (empty($info)){
			$info=self::get_city_by_ip($ip);
			if($info) $cache->saveOrUpdate($key,$info,$time);
		} 
		$cache->close();
		if($info) return $info; else return null;

	}

	static function get_city_by_ip($ip){
		if(!self::verify_ip($ip)) return false;
		require_once dirname(__FILE__).'/../string/string.class.php'; 
		$r=file_get_contents('http://www.ip138.com/ips.asp?ip='.$ip);
		$t='本站主数据：';
		string::$code_to='gbk';
		$t=string::convert_encoding_deep($t);
		preg_match("/".$t."(.*) /iU",$r, $matches);
		$r = $matches[1]; 
		string::$code_to='utf-8';
		$r=string::convert_encoding_deep($r); 
		return $r;    
	}
	
	//按区号获取城市
	static function get_no_by_city($city){
		$city_list = array();
		$city_list['北京'] = '10';
		$city_list['上海'] = '21';
		$city_list['广东'] = '20';
		if($city){
			return $city_list[$city];
		} else {
			return false;
		}
	}
	
	//按城市获取区号
	static function get_city_by_no($no){
		$city_list = array();
		$city_list[10] = '北京';
		$city_list[21] = '上海';
		$city_list[20] = '广东';
		if($no){
			return $city_list[$no];
		} else {
			return 0;
		}
	}
}//end class
?>