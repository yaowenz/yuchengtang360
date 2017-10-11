<?php
//tsina weibo 相关实现类
require_once dirname(__FILE__).'/../../files/dir.class.php';
require_once dirname(__FILE__).'/../../../../config/comm.config.php';
require_once dirname(__FILE__).'/../../process.class.php';
require_once dirname(__FILE__).'/../../db/db.class.php';
require_once dirname(__FILE__).'/../../proxy/proxy.poll.blacklist.php'; 
 
class my_spider{
	static $error;
	static $tmp_dir = '/tmpcookie'; 
	static $time_out=0;  
	static $pt_name = 'default';
	static $uid;
	static $echo_out=false; // true 调试打印信息  
 
	
	 
	//生成cookie_jar路径
	static function get_cookie_jar_path($key,$dir_style = null){
		if($dir_style == 'by_pt'){
			$dir_path = self::mkdir_by_pt(comm_config::$tmp_path);
		} else {
			$dir_path = self::mkdir_by_date(time(),comm_config::$tmp_path);
		}
		$cookie_name = 'cookie_'.md5($key);
		$cookie_jar = $dir_path.'/'.$cookie_name;
		return $cookie_jar;
	}

	//模拟登录
	static function simulate_login($login_url,$login_request,$key,$proxy=null,$browser=null,$ref=null){
		
		$cookie_jar = self::get_cookie_jar_path($key);
		if(!file_exists($cookie_jar)){
			$fp = fopen($cookie_jar,'w');
			if(!$fp){
				self::$error = '创建cookie临时文件错误';
				echo self::$error;
				return false;
			}

			$curl = curl_init();
			if($proxy['p'] && $_GET['__PROXY'] != 1){
				curl_setopt($curl, CURLOPT_PROXY, $proxy['p']);
				if($proxy['u']){
					curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxy['u']);
				}
			}
			curl_setopt($curl, CURLOPT_URL,$login_url);//这里写上处理登录的界面
			if(!empty($login_request)){
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $login_request);//传 递数据
			}
			curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_jar);// 把返回来的cookie信息保存在$cookie_jar文件中
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//设定返回 的数据是否自动显示
			curl_setopt($curl, CURLOPT_HEADER, false);//设定是否显示头信 息
			if($browser){
				curl_setopt($curl, CURLOPT_USERAGENT, $browser);//浏览器信息
			}
			if($ref){
				curl_setopt($curl, CURLOPT_REFERER, $ref);//模拟Referer
			}
			if(self::$time_out && is_numeric(self::$time_out)){
				curl_setopt($curl, CURLOPT_TIMEOUT, self::$time_out);//超时
			}
			curl_setopt($curl, CURLOPT_NOBODY, false);//设定是否输出页面 内容
			$content = curl_exec($curl);//返回结果
			$r1=curl_getinfo($curl);
			curl_close($curl); //关闭

			//显示细节，调试用
			if(self::$echo_out) 
			{
				
				echo '<br/>-- get_info_from_curl start ----------------> <br/>';
				if($_GET['__PROXY']) echo '<br/>__PROXY:<br/>';	print_R($_GET['__PROXY']);echo '<br/>';
				if($ary['proxy']) echo '<br/>proxy:<br/>';	print_R($ary['proxy']);echo '<br/>';  
				if($ary['url']) echo '<br/>url:<br/>';	print_R($ary['url']);echo '<br/>';  
				if($ary['post_request']) echo '<br/>post_request:<br/>';	print_R($ary['post_request']);echo '<br/>';
				if($ary['header']) echo '<br/>header:<br/>';	print_R($ary['header']);echo '<br/>';
				if($ary['browser']) echo '<br/>browser/ua:<br/>';	print_R($ary['browser']);echo '<br/>';
				if($ary['ref']) echo '<br/>ref:<br/>';	print_R($ary['ref']);echo '<br/>';
				if($ary['cookie_jar']) echo '<br/>cookie_jar:<br/>';	print_R($ary['cookie_jar']);echo '<br/>';
				if($ary['set_cookie']) echo '<br/>set_cookie:<br/>';	print_R($ary['set_cookie']);echo '<br/>';
				if($ary['timeout']) echo '<br/>timeout:<br/>';	print_R($ary['timeout']);echo '<br/>';
				//if($content) echo '<br/>body:<br/>';	print_R($content);echo '<br/>';
				echo '<br/>-- get_info_from_curl stop ---------------> <br/><br/><br/><br/>';
			} 
			/// 

			if($r1['http_code'] == 0){
				self::$error = '0:proxy error';
				/*
				if($proxy && $_GET['__PROXY'] != 1)
				{
					//说明代理无效
					require_once COMM_PATH.'/lib/proxy/proxy.poll.blacklist.php';  
					proxy_adshell::delete_one($proxy['ip']);  
					proxy_poll_bl::delete_proxy_from_poll($proxy['ip']);  
				}
				*/
				return false;
			}
			if(!$content && $r1['total_time']>=self::$time_out && intval(self::$time_out)>0){
				self::$error = 'curl_time_out';
				return false;
			}
			
			//如果是新浪微博登录，适用，可取到 uid
			$preg1 = "/uniqueid\":\"(.*)\"/iU";  
			preg_match($preg1,$content, $match1);
			self::$uid = $match1[1]; 

		} 
 
		//var_dump($content);
		return $cookie_jar;
	}

	//抓取页面
	static function get_content_for_curl($ary,$get_cookie = false,$cookie_dir_style = null){
		$r=self::get_info_from_curl($ary,$get_cookie,$cookie_dir_style);
		if(!$r) return false;
		$content=$r['body'];
		return $content;
	}
 
	//抓取页面
	static function get_info_from_curl($ary,$cookie_key = null,$cookie_dir_style = null){
		if(!isset($ary['url'])){ 
			self::$error = '需要抓取页面的URL不存在';
			return false; 
		}

		if($ary['time_out']) $timeout=$ary['time_out'];
		else $timeout=self::$time_out;

		//echo '<br/>url:<br/>';	print_R($ary['url']);echo '<br/>';  
		$curl = curl_init();
		if($ary['proxy'] && $_GET['__PROXY'] != 1){
			/*
			//验证代理是否有效
				$fp = @fsockopen($ary['proxy']['ip'],$ary['proxy']['port'], $errno, $errstr, 3);  
				if (!$fp) {
					//当某ip代理不可用时，从ip代理池和轮询池内删除此ip
					require_once COMM_PATH.'/lib/proxy/proxy.poll.blacklist.php';  
					proxy_adshell::delete_one($ary['proxy']['ip']);  
					proxy_poll_bl::delete_proxy_from_poll($ary['proxy']['ip']);  

					self::$error = 'none_proxy_curl:'.$ary['proxy']['ip'];
					return false; 
				}
			///
			*/
			curl_setopt($curl, CURLOPT_PROXY, $ary['proxy']['p']);
			if(!empty($ary['proxy']['u'])){
				curl_setopt($curl, CURLOPT_PROXYUSERPWD, $ary['proxy']['u']);
			}  
		}

		curl_setopt($curl, CURLOPT_URL, $ary['url']);//登陆后要从哪个页面获取信息
		if(isset($ary['post_request'])){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $ary['post_request']);//传 递数据 
		} 
		
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if(isset($ary['browser'])){ 
			curl_setopt($curl, CURLOPT_USERAGENT, $ary['browser']);//浏览器信息
		}
		
		if($ary['content_type'])
		{
			curl_setopt($curl, CURLINFO_CONTENT_TYPE, $ary['content_type']); 
		}  

		if($ary['header'] && is_array($ary['header']))
		{
			curl_setopt($curl, CURLOPT_HTTPHEADER, $ary['header']);
		}  

		if(isset($ary['ref'])){
			curl_setopt($curl, CURLOPT_REFERER, $ary['ref']);//模拟Referer
		}
		if(isset($ary['cookie_jar'])){
			curl_setopt($curl, CURLOPT_COOKIEFILE, $ary['cookie_jar']);
		}
		
		if(isset($ary['set_cookie'])){
			curl_setopt($curl,CURLOPT_COOKIE,$ary['set_cookie']);    
		}
 

		if($cookie_key){
			$cookie_jar = self::get_cookie_jar_path($cookie_key,$cookie_dir_style);
			curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_jar);// 把返回来的cookie信息保存在$cookie_jar文件中
		} else if($ary['cookie_jar']) {
			curl_setopt($curl, CURLOPT_COOKIEJAR, $ary['cookie_jar']);
		}

		if($timeout && is_numeric($timeout)){
			curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);//超时 
		}
		//echo '<br/>time_out:<br/>';	print_R($timeout);echo '<br/>';   
		$response = curl_exec($curl); 
		//echo '<br/>response:<br/>';	print_R($response);echo '<br/>'; 

		/*
		//记入日志 
		$dbconfig=cm_db_config::get_ini('ncg_duap_single','master');
		if($dbconfig) 
		{
			$db = cm_db::get_db($dbconfig); 
			$a=null;
			$a['create_time']=time();
			if($ary['post_request']) 
			{
				if(is_array($ary['post_request'])) $a['post']=json_encode($ary['post_request']);
				else $a['post']=$ary['post_request'];
			}
			if($ary['browser']) $a['ua']=$ary['browser'];
			if($ary['ref']) $a['ref']=$ary['ref'];
			if($ary['cookie_jar']) $a['cookie_jar']=$ary['cookie_jar'];
			if($ary['url']) $a['curl_url']=$ary['url'];
			if($ary['proxy']) $a['proxy']=$ary['proxy']['ip'];   
			$db->insert('log_curl', $a);
		}
		///
		*/ 
		
		
		$r1=curl_getinfo($curl); 
		//echo '<br/>r1:<br/>';	print_R($r1);echo '<br/>';  
		$header_size = $r1['header_size'];  
        $rt1['header'] = trim(substr( $response, 0, $header_size ));  
        $rt1['body'] = trim(substr( $response, $header_size ));  
		curl_close($curl); //关闭
		if($r1['http_code'] == 0){
			self::$error = '0:proxy error'; 
			echo '<br/>proxyerrorurl:<br/>';	print_R($ary['url']);echo '<br/>';    
			//self::$error = $ary['url'];  
			/*
			if($ary['proxy'] && $_GET['__PROXY'] != 1)
			{
				//说明代理无效
				require_once COMM_PATH.'/lib/proxy/proxy.poll.blacklist.php';  
				proxy_adshell::delete_one($ary['proxy']['ip']);  
				proxy_poll_bl::delete_proxy_from_poll($ary['proxy']['ip']);  
			}
			*/
			return false;
		}
		preg_match('/Location:[^\"].*\r/i', $rt1['header'], $location);
		$location=explode(': ',$location[0],2);  
		$rt1['location']=$location[1];  

		if($cookie_key){
			$rt1['cookie_jar'] = $cookie_jar;
		}
		$content=$rt1['body'];

		//显示细节，调试用
		if(self::$echo_out) 
		{ 
			
			echo '<br/>-- get_info_from_curl start ----------------> <br/>';
			if($_GET['__PROXY']) echo '<br/>__PROXY:<br/>';	print_R($_GET['__PROXY']);echo '<br/>';
			if($ary['proxy']) echo '<br/>proxy:<br/>';	print_R($ary['proxy']);echo '<br/>';  
			if($ary['url']) echo '<br/>url:<br/>';	print_R($ary['url']);echo '<br/>';  
			if($ary['post_request']) echo '<br/>post_request:<br/>';	print_R($ary['post_request']);echo '<br/>';
			if($ary['header']) echo '<br/>header:<br/>';	print_R($ary['header']);echo '<br/>';
			if($ary['browser']) echo '<br/>browser/ua:<br/>';	print_R($ary['browser']);echo '<br/>';
			if($ary['ref']) echo '<br/>ref:<br/>';	print_R($ary['ref']);echo '<br/>'; 
			if($ary['cookie_jar']) echo '<br/>cookie_jar:<br/>';	print_R($ary['cookie_jar']);echo '<br/>';
			if($ary['set_cookie']) echo '<br/>set_cookie:<br/>';	print_R($ary['set_cookie']);echo '<br/>';
			if($ary['timeout']) echo '<br/>timeout:<br/>';	print_R($ary['timeout']);echo '<br/>';
			//if($content) echo '<br/>body:<br/>';	print_R($content);echo '<br/>';
			echo '<br/>-- get_info_from_curl stop ---------------> <br/><br/><br/><br/>';
		}
		///

		if(!$content && $r1['total_time']>=$timeout && intval($timeout)>0){
			self::$error = 'curl_time_out';
			return false;
		}
		if(empty($rt1)){
			self::$error = 'get_no_content'; 
		}
		return $rt1;
	}

	static function get_token_cache($x){
		//echo'------------x[cookie_jar]----------';print_R($x['cookie_jar']);echo '<br/>';  
		if(!$x['cookie_jar']) {self::$error='token_no_cookie_jar';return false; }
		$expire=3600; 
		require_once COMM_PATH.'/lib/cache/cache.class.php';
		$key = md5('myspidergettokencachev3'.$x['cookie_jar']);
		$cache = new temp_cache;
		$ret = $cache->get($key);
		if (!$ret){ 
			$ret = self::get_token($x);
			$cache->saveOrUpdate($key,$ret,$expire);
		}
		$cache->close();
		return $ret; 
	}

	static function get_token($x){
		//如果有先从数据库取 
		$dbconfig=cm_db_config::get_ini('ncg_duap_single','master');
		if($dbconfig) 
		{
			$db = cm_db::get_db($dbconfig); 
			if($x['user_name'])
			{
				$select = $db->select();
				$select	->from('reach_accounts_tsina',array('token','str_uid'))
						->where('user_name = ?', $x['user_name'])
						->limit(1);
				$sql = $select->__toString();    //die($sql);  
				$rs = $db->fetchRow($sql); 
				if($rs['token'] && $rs['str_uid']) return $rs;
			}
			//如果取不到再从weibo取，取到后录入到数据库
			if(!$x['cookie_jar']) return false; 
			require_once COMM_PATH . '/../config/url.config.php';
			$a['url']=cm_url::get('tsina_weibo').'/systemnotice'; 
			$a['browser']=$x['browser'];
			$a['cookie_jar']=$x['cookie_jar'];
			$a['ref'] = $a['url'];
			$a['proxy']=$x['proxy'];
			 
			$rt=self::get_info_from_curl($a);
			
			if(!$rt['body']) return false; 
			
			/*
			//判断获取token后的反馈是否变化
			require_once dirname(__FILE__).'/back_change/tsina.change.php';
			if(!tsina_change::login_token($rt['body'])){
				self::$error = 'tsina login_token change';
				return false;
			}
			//end
			*/

			$str1=substr($rt['body'],0,1500); 
			$t1 = '/\$token.*: "(.*)",/iUsu'; 
			$t2 = '/\$str_uid.*: "(.*)",/iUsu'; 
			preg_match($t1,$str1,$match);
			preg_match($t2,$str1,$match2);
			if($match[1] && $match2[1])
			{	
				$token = $match[1];
				$str_uid = $match2[1];
				if($x['user_name'])
				{
					$table = 'reach_accounts_tsina';
					$where = $db->quoteInto('user_name = ? ', $x['user_name']); 
					$a=null;
					$a['token'] = $token; 
					$a['str_uid'] = $str_uid; 
					$db->update($table, $a, $where);
					return $a;
				} 
			}
		}//if dbconfig
	}

	//按照日期建立目录
	static function mkdir_by_date($date,$dir = '.',$tag=null) {
		$pt = self::$pt_name;
		if($tag) $dir .= $tag; 
		else $dir .= self::$tmp_dir;  
		list($y, $m, $d) = explode('-', date('Y-m-d', $date));
		!is_dir("$dir") && mkdir("$dir", 0755);
		!is_dir("$dir/$pt") && mkdir("$dir/$pt", 0755);
		!is_dir("$dir/$pt/$y$m$d") && mkdir("$dir/$pt/$y$m$d", 0755);
		return $dir."/$pt/$y$m$d";
	}

	//按照平台建立目录
	static function mkdir_by_pt($dir = '.',$tag=null) {
		$pt = self::$pt_name;
		if($tag) {
			$dir .= $tag; 
		} else {
			$dir .= self::$tmp_dir;
		}
		!is_dir("$dir") && mkdir("$dir", 0755);
		!is_dir("$dir/$pt") && mkdir("$dir/$pt", 0755);
		return $dir."/$pt";
	}
 
	/*
	static function del_tmp_dir(){
		
		if(!empty(comm_config::$tmp_path)){
			if(!empty(self::$tmp_dir) && comm_config::$tmp_path != '/'){
				$dir = comm_config::$tmp_path.self::$tmp_dir;
				//echo $dir;
				dirs::del($dir);
			}
		}
	}
	*/ 
	
}//end class 