<?php
require_once dirname(__FILE__).'/../../spider/my_spider/myspider.class.php';
require_once dirname(__FILE__).'/../../spider/config/config.class.php';
require_once dirname(__FILE__).'/../../../../config/comm.config.php';
require_once dirname(__FILE__).'/../../process.class.php';
class t163_login{
	public $error;
	public $proxy; 
	public $process_id;
	public $browser;
	public $key_simulate_login='simulate_login_t163_v2';
	public $key_login_ed='simulatekeylogin_ed_t163_v2';

	//模拟登录
	function simulate_login($user,$time_out = 0){
		my_spider::$time_out = $time_out;
		my_spider::$pt_name = 't163';

		//获取用户登录信息
		if(!$user){
			//self::$user = spider_config::get_user_tsina(self::$process_id);
			$this->error = 'no user';
			return false;
		}
		
		$username = trim($user['user']);
		$pwd = trim($user['pwd']);

		$browser = $this->browser;
		if($this->proxy){
			$proxy = $this->proxy;
		} else {
			//$proxy = spider_config::get_spider_proxy($user_key);
			$proxy = null;
		}

		$cookie_jar = my_spider::get_cookie_jar_path($username); 

		
		//是否60秒内已经登录过
		if(!cm_process::runing($username.$this->key_login_ed,60))  
		{
			$this->error = 'login_ed';
			return $cookie_jar;
		} 
		//是否正在登录中
		if(!cm_process::runing($username.$this->key_simulate_login,8)) 
		{ 
			$this->$error = 'doing_login';
			return false;
		}
		

		if($cookie_jar && !file_exists($cookie_jar)){
			//if(file_exists($cookie_jar))	unlink($cookie_jar);
 			
			//模拟登录第一步(login跳转请求)
			$ary_prelogin = null; 
			$ary_prelogin['url'] = 'https://reg.163.com/logins.jsp';
			$ary_prelogin['proxy'] = $proxy;
			$ary_prelogin['browser'] = $browser;
			$ary_prelogin['ref'] = 'http://t.163.com/session';
			$ary_post = 'password='.$pwd.'&product=t&type=1&url=http%3A%2F%2Ft.163.com%2Fsignup%2Fpassport&username='.$username;
			
			$ary_prelogin['https'] = true;
			$ary_prelogin['cookie_jar']=$cookie_jar;
			$ary_prelogin['post_request'] = $ary_post;
			//my_spider::$echo_out=true;//调试用
			
			$request_1 = my_spider::get_info_from_curl($ary_prelogin);
			//print_r($request_1['body']);//exit;
			if(!$request_1){
				$this->error = my_spider::$error; 
				return false;
			}
			//用正则取有道链接&发第2步请求
			preg_match('/<script\s*language=\"JavaScript\">\s*window.location.replace\(\"(.*)\"\)/iUsu',$request_1['body'], $youdao_url_ary);
			$youdao_url = $youdao_url_ary[1];
			//echo $youdao_url;
			//print_r($youdao_url_ary);
			//echo htmlspecialchars($request_1['body']);
			$ary_login = null;
			$ary_login['url'] = $youdao_url;
			$ary_login['proxy'] = $proxy;
			$ary_login['browser'] = $browser;
			$ary_login['ref'] = 'https://reg.163.com/logins.jsp';
			$ary_login['cookie_jar']=$cookie_jar; 
			$r2 = my_spider::get_info_from_curl($ary_login);
			

			$u= explode('@',$username);
			//模拟登录第3步passport
			$ary_login = null;
			$ary_login['url'] = 'http://t.163.com/signup/passport?username='.$u[0];
			$ary_login['proxy'] = $proxy;
			$ary_login['browser'] = $browser;
			$ary_login['ref'] = $youdao_url; 
			$ary_login['cookie_jar']=$cookie_jar; 
			$login_back = my_spider::get_info_from_curl($ary_login);
			//echo $login_back['location'];exit;
			//print_r($ary_login);
			//print_r($login_back);exit;

			/*//测试时候登录成功
			$ary_login = null;
			$ary_login['url'] = trim($login_back['location']);
			$ary_login['proxy'] = $proxy;
			$ary_login['browser'] = $browser;
			$ary_login['ref'] = $youdao_url; 
			$ary_login['cookie_jar']=$cookie_jar; 
			$rs = my_spider::get_info_from_curl($ary_login);
			//echo $login_back['location'];
			//print_r($ary_login);
			print_r($rs);*/
			
			echo '<br/>登录啦<br/>';  

			
		}
		cm_process::done($username.$this->key_simulate_login);
		return $cookie_jar;
	}


	
 
}//end class
