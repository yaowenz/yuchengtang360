<?php
//验证码识别基类 by ziv 2011/7/20
@header("content-type:text/html;charset=utf-8");  
 
if(!defined('COMM_PATH')) {
	define('COMM_PATH', dirname(__FILE__).'/../../');//共用库目录
}
require_once COMM_PATH . '/lib/spider/my_spider/myspider.class.php';
require_once COMM_PATH.'/lib/files/files.class.php';
require_once COMM_PATH . '/../config/url.config.php';

class cm_captcha_identify{
	public $plug_name='tsina_p1'; //默认插件
	public $plug;
	public $uid;//账户id   
	public $user_name;
	public $pwd;
	public $proxy; 
	public $cookie_jar;  
	public $token;
	public $process_id=''; 
	public $proxy_browser; 
	public $path_identify='cm_captcha_identify'; 
	public $result; 
	static $error;
	public function __construct(){
		$this->get_plug();
		if(!$this->plug) return false;
	} 
	public function __destruct(){ 
	}

	public function get_plug(){
		$a = explode("_", $this->plug_name);
		if(!is_array($a)) return false;
		require_once dirname(__FILE__).'/'.$a[0].'/'.$a[1].'.php';
		$this->plug=new $this->plug_name;
		$this->plug->parent=$this;  
	}
	
	//获取浏览器信息
	public function get_proxy_browser(){
		if($this->proxy_browser) return $this->proxy_browser; 
		if(!$this->uid) return false; 
		require_once COMM_PATH . '/lib/spider/config/config.class.php';
		$user_key = $this->user_name;   
		$this->proxy_browser = spider_config::get_spider_browser($user_key); 
		return $this->proxy_browser; 
	}

	public function get_identify(){
		$r=$this->plug->get_identify();   
		if(!$r) return false;
		return true;  
	} 

}//end class