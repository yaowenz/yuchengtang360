<?php
require_once COMM_PATH . '/lib/spider/my_spider/myspider.class.php';
require_once COMM_PATH . '/lib/spider/config/config.class.php';
require_once COMM_PATH . '/../config/comm.config.php';
class renren_login{
	public $error;
	private $obj;
	static $cookie_dir_style = 'by_pt';

	//得到一个单例
	static private $instance = NULL;
	public static function getInstance($obj = null) {
		if (self::$instance === NULL) {
			self::$instance = new self($obj);
		}
		return self::$instance;
	}

	function __construct($obj){
		$this->obj = $obj;
	}

	//模拟登录
	function renren_simulate_login($time_out = 0){
		$process_id = $this->obj->get_process_id();
		$domain = $this->obj->get_domain();
		$user = $this->obj->get_user();
		$proxy = $this->obj->get_proxy();
		$user_browser = $this->obj->get_user_browser();
		my_spider::$time_out = $time_out;
		my_spider::$pt_name = 'renren';

		if(!$domain){
			$domain = 'www.renren.com';
		}

		//如果没有外部传入的user则获取一个用户
		$out_in_user = true;
		if(!$user){
			$out_in_user = false;
			$this->error = '没有获取到可用的账号';
			return false;
		}
		
		//如果没有外部传入的浏览器信息则获取一个浏览器信息
		$user_key = $user['user'] . $process_id;
		if(!$user_browser){
			$user_browser = spider_config::get_spider_browser($user_key);
		}
		
		$u = $user['user'];
		$pwd = $user['pwd'];
		
		//是否已登陆判断
		$cookie_key = $domain . $u;
		$cookie_jar = my_spider::get_cookie_jar_path($cookie_key,self::$cookie_dir_style);
		if(file_exists($cookie_jar)){
			return $cookie_jar;
		}
		
		$login_url = 'http://'.$domain.'/PLogin.do';
		$login_request = array();
		$login_request['domain'] = 'renren.com';
		$login_request['email'] = $u;
		$login_request['origURL'] = 'http://'.$domain.'/home';
		$login_request['password'] = $pwd;
		$ref = 'http://'.$domain.'/';

		$ary_login = array();
		$ary_login['url'] = $login_url;
		$ary_login['post_request'] = $login_request;
		$ary_login['proxy'] = $proxy;
		$ary_login['browser'] = $user_browser;
		$ary_login['ref'] = $ref;
		$login_back_content = my_spider::get_info_from_curl($ary_login);
		if(!$login_back_content && !$login_back_content['cookie_jar']){
			$this->error = my_spider::$error;
			return false;
		}
		
		if($login_back_content['location']){
			$ary_login_2 = array();
			$ary_login_2['url'] = urldecode(trim($login_back_content['location']));
			$ary_login_2['proxy'] = $proxy;
			$ary_login_2['browser'] = $user_browser;
			$ary_login_2['ref'] = $ref;
			$login_content = my_spider::get_info_from_curl($ary_login_2,$cookie_key,self::$cookie_dir_style);
		}

		if(!$login_content && !$login_content['cookie_jar']){
			$this->error = my_spider::$error;
			return false;
		}
		return $login_content['cookie_jar'];
	}

	//返回全局的obj
	function get_obj(){
		return $this->obj;
	}
}