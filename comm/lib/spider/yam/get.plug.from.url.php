<?php
error_reporting(255);  
ini_set('display_errors','On');
@header("content-type:text/html;charset=utf-8");
//通过url得到plug目录名称 by ziv 2011/3/11
class get_plug_from_url{
	static $error;
	static function get($url){
		$d=self::get_domain($url);
		if(!$d){self::$error='没有所属域';return false;} 
		$d2=self::d_change($d);
		$handle=self::get_handle_by_domain($d2); if(!$handle) {self::$error='没有所属域';return false;} 
		$rt=$handle->set_url($url)->get();
		return $rt;
	}

	static function get_domain($url){
		preg_match("/^(http:\/\/)?([^\/]+)/i",$url, $matches);
		$host = $matches[2];
		return $host;
	}

	static function d_change($domain){
		$r = str_replace(".", "_", $domain);
		return $r;
	}

	static function get_handle_by_domain($d){
		$x = 'get_plug_'.$d;
		if(!class_exists($x)) {self::$error='没有所需类';return false;}
		$r = new $x;
		return $r;
	}

}//end class get_plug_from_url

class get_plug_t_sina_com_cn{
	
	function __construct(){
		
	}

	function set_url($url){
		$this->url=$url;
		return $this;
	}

	function get(){
		if(!$this->url) {get_plug_from_url::$error='没有所需url';return false;}
	 		 
		 if($this->url == 'http://t.sina.com.cn/atme') {return 'ignore';}//atme，什么都不做
			else if(preg_match("/^http:\/\/t\.sina\.com\.cn\/[^\/]+(\/.*|\/?)$/iU",$this->url)){ return 't_sina_com_cn_d';}//我的微博
		 //if(preg_match("/^http:\/\/t.sina.com.cn\/\d+\/profile\/{0,1}$/i",$this->url)) return 't_sina_com_cn_d_profile';//我的微博
		//else if(preg_match("/^http:\/\/t.sina.com.cn\/\d+\/{0,1}$/i",$this->url)) return 't_sina_com_cn_d';//我的首页
		//else if(preg_match("/^http:\/\/t.sina.com.cn\/\d+\/\w+\/{0,1}$/i",$this->url)) return 't_sina_com_cn_d_w';//分享原文
		else {get_plug_from_url::$error='没有匹配的插件';return false;}
	}
}//end class t_sina_com_cn

class get_plug_weibo_com{
	
	function __construct(){
		
	}

	function set_url($url){
		$this->url=$url;
		return $this;
	}

	function get(){
		if(!$this->url) {get_plug_from_url::$error='没有所需url';return false;}

		if($this->url == 'http://weibo.com/atme') { return 'ignore';}//atme，什么都不做
		else if(preg_match("/^http:\/\/weibo.com\/[^\/]+(\/.*|\/?)$/iU",$this->url)){ return 'weibo_com_d';}//我的微博
			 //if(preg_match("/^http:\/\/t.sina.com.cn\/\d+\/profile\/{0,1}$/i",$this->url)) return 't_sina_com_cn_d_profile';//我的微博
		//else if(preg_match("/^http:\/\/t.sina.com.cn\/\d+\/{0,1}$/i",$this->url)) return 't_sina_com_cn_d';//我的首页
		//else if(preg_match("/^http:\/\/t.sina.com.cn\/\d+\/\w+\/{0,1}$/i",$this->url)) return 't_sina_com_cn_d_w';//分享原文
		else {get_plug_from_url::$error='没有匹配的插件';return false;}
	}
}//end class weibo_com

class get_plug_yam_laawoo_com{
	
	function __construct(){
		
	}

	function set_url($url){
		$this->url=$url;
		return $this;
	}

	function get(){
		return 'ignore';
	}
}//end class yam_laawoo_com

class get_plug_www_baidu_com{
	
	function __construct(){
		
	}

	function set_url($url){
		$this->url=$url;
		return $this;
	}

	function get(){
		return 'ignore';
	}
}//end class yam_laawoo_com

class get_plug_www_google_com_hk{
	
	function __construct(){
		
	}

	function set_url($url){
		$this->url=$url;
		return $this;
	}

	function get(){
		return 'ignore';
	}
}//end class yam_laawoo_com

class get_plug_alexacn_cc{
	
	function __construct(){
		
	}

	function set_url($url){
		$this->url=$url;
		return $this;
	}

	function get(){
		return 'ignore';
	}
}//end class alexacn_cc

class get_plug_url_cn{
	
	function __construct(){
		
	}

	function set_url($url){
		$this->url=$url;
		return $this;
	}

	function get(){
		return 'ignore';
	}
}//end class url_cn

class get_plug_t_cn{
	
	function __construct(){
		
	}

	function set_url($url){
		$this->url=$url;
		return $this;
	}

	function get(){
		return 'ignore';
	}
}//end class t_cn


?>