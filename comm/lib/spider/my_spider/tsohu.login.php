<?php

require_once COMM_PATH . '/lib/spider/my_spider/myspider.class.php';
require_once COMM_PATH . '/lib/spider/my_spider/spider.model.php';
require_once COMM_PATH . '/lib/spider/config/config.class.php';
require_once COMM_PATH . '/../config/comm.config.php';

class tsohu{
	public $user_name;
	public $password;
	public $error;
	public $domain = 't.sohu.com';
	public $request_url = "https://passport.sohu.com/sso/login.jsp";
	public $proxy;
	public $browser;


	function __construct(){
		
	}
	/** ģ���¼ ����forciblyָ���Ƿ�ǿ��ˢ��cookie **/
	public function simulate_login($forcibly = null){

	
		if(!$this->user_name){
			$this->error = "no user";
			return false;
		}
		if(!$this->password){
			$this->error = "no password";
			return false;
		}

		$user_name = $this->user_name;
		$password = $this->password;

		$cookie_key = $user_name;
		$cookie_jar = my_spider::get_cookie_jar_path($cookie_key, 'by_pt');
		
		$ary_test_login['url'] ='http://t.sohu.com/home';
		$ary_test_login['cookie_jar'] = $cookie_jar;
		$ary_test_login['ref'] = 'http://t.sohu.com/new_index';
		
		/**���Cookie�ļ��Ƿ���ڣ��ҺϷ����ж�Ϊ�ѵ�¼״̬ **/
		/** ���cookie���Ϸ����Զ���ȡ��cookie **/
		/*var_dump($forcibly);*/
		if(file_exists($cookie_jar)){
			//$co = $this->check_cookie($ary_test_login, $cookie_key, 'by_pt');
			//if($co){
				
			//}
			return $cookie_jar;
		}
		/** ��¼�Ѻ���passport **/
		$user_name = urlencode($user_name);
		$password = md5($password);
		$url = $this->request_url;
		$url .=	"?userid=".$user_name;
		$url .=	"&password=".$password;
		$url .=	"&appid=9999&persistentcookie=1";
		$url .=	"&s=".time();
		$url .=	"&b=2&w=1600&pwdtype=1&v=26";
		$ary_login['url'] =$url;
		$ary_login['ref'] = 'http://t.sohu.com/new_index';
		$ary_login['https'] = true;
		$passport_content = my_spider::get_info_from_curl($ary_login, $cookie_key, 'by_pt');

		if(!isset($passport_content['body'])){
			$this->error = "communication error";
			return false;
		}
		if($passport_content['body'] != "login_status='success';"){
			$this->error = $login_back_content['body'];
			return false;
		}

		
		/** �ж��»�õ�cookie�Ƿ�Ϸ� **/
		$co = $this->check_cookie($ary_test_login, $cookie_key, 'by_pt');
	
		if(!$co){
			$this->error = 'cookie error';
			return false;
		}
		return $passport_content['cookie_jar'];
	}

	private function check_cookie($ary_test_login, $cookie_key, $cookie_dir){
		$test_login_back_content = my_spider::get_info_from_curl($ary_test_login,$cookie_key,$cookie_dir);
	
		if(!empty($test_login_back_content['location'])){
			$this->error = 'account block';
			@unlink($test_login_back_content['cookie_jar']);
			return false;
		}else{
			return $test_login_back_content['cookie_jar'];
		}
	}

	public function reset_cookie($user_name ){
		$cookie_key = $user_name;
		$cookie_jar = my_spider::get_cookie_jar_path($cookie_key, 'by_pt');
		unlink($cookie_jar);
		return;
	}
	
	public function set_user_name($uname){
		$this->user_name = $uname;
	}

	public function set_password($pwd){
		$this->password = $pwd;
	}

	public function set_browser($browser){
		$this->browser = $browser;
	}

	public function set_proxy($proxy){
		$this->proxy = $proxy;
	}

	public function error_msg(){
		return $this->error;
	}

}