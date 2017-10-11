<?php
require_once dirname(__FILE__).'/../../spider/my_spider/myspider.class.php';
require_once dirname(__FILE__).'/../../spider/config/config.class.php';
require_once dirname(__FILE__).'/../../../../config/comm.config.php';
require_once dirname(__FILE__).'/../../process.class.php';
require_once dirname(__FILE__).'/back_change/tsina.change.php';
class tsina_login{
	static $error;
	static $user;
	static $proxy; 
	static $process_id;
	static $browser;
	static $key_simulate_login='simulate_login_v2';
	static $key_login_ed='simulatekeylogin_ed_v2';

	//记录登录log
	static function log_simulate_login($a=array()){
		if(!$a['aid']) return false;
		require_once dirname(__FILE__).'/../../db/db.class.php';
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single','master'));
		$table = 'log_tsina_login';
		$row = $a; 
		$row['create_time'] = time();
		$rows_affected = $db->insert($table, $row); 
		$last_insert_id = $db->lastInsertId();
		return $last_insert_id; 
	}

	//模拟登录
	static function simulate_login($time_out = 0){
		my_spider::$time_out = $time_out;
		my_spider::$pt_name = 'tsina';

		//获取用户登录信息
		if(!self::$user){
			//self::$user = spider_config::get_user_tsina(self::$process_id);
			self::$error = 'no user';
			return false;
		} 
		  
		$username = self::$user['user'];
		$pwd = self::$user['pwd'];

		$user_key = self::$user['user'];
		$browser = spider_config::get_spider_browser($user_key);
		self::$browser = $browser;
		if(self::$proxy){
			$proxy = self::$proxy;
		} else {
			//$proxy = spider_config::get_spider_proxy($user_key);
			$proxy = null;
		}

		$cookie_jar = my_spider::get_cookie_jar_path($user_key); 
		$new_pwd = '';


		//是否60秒内已经登录过
		if(!cm_process::runing($username.self::$key_login_ed,60))  
		{
			self::$error = 'login_ed';
			return $cookie_jar;
		} 
		//是否正在登录中
		if(!cm_process::runing($username.self::$key_simulate_login,8)) 
		{ 
			self::$error = 'doing_login';
			return false;
		}   

		if($cookie_jar && (!file_exists($cookie_jar) || !self::$user['uid'])){
			if(file_exists($cookie_jar))	unlink($cookie_jar);
 			//获取 servertime nonce
			$su=base64_encode(urlencode($username)); 
			$ary_prelogin = array(); 
			$ary_prelogin['url'] = 'http://login.sina.com.cn/sso/prelogin.php?entry=weibo&callback=sinaSSOController.preloginCallBack&su='.$su.'&client=ssologin.js(v1.3.16)&_='.time().rand(100,999); 
			$ary_prelogin['proxy'] = $proxy;
			$ary_prelogin['browser'] = $browser;
			$ary_prelogin['ref'] = 'http://weibo.com';
			$prelogin_str = my_spider::get_content_for_curl($ary_prelogin);
			if(!$prelogin_str){
				self::$error = my_spider::$error;
				return false;
			} 

			$change_note = 'user:' . $username;
			//判断获取登录pwd识别码请求的反馈是否变化的地方
			if(!tsina_change::login_1($prelogin_str,$change_note)){
				self::$error = 'tsina login 1 change';
				return false;
			}
			//end
			

			$preg = '/sinaSSOController.preloginCallBack\((.*)\)/iUsu';
			preg_match($preg,$prelogin_str, $match_prelogin);
			$prelogin = json_decode($match_prelogin[1],true);
			$servertime = $prelogin['servertime']+1;
			sleep(1); 
			$nonce = $prelogin['nonce'];

			//计算pwd
			$new_pwd = tsina_login_encoder::hex_sha1("" . tsina_login_encoder::hex_sha1(tsina_login_encoder::hex_sha1($pwd)) . $servertime . $nonce);

			//模拟登录第一步
			$login_ary = spider_config::get_login_url('weibo',$username,$new_pwd,array('nonce'=>$nonce,'servertime'=>$servertime,'su'=>$su));
			$login_url = $login_ary['url'];
			$request = $login_ary['request'];
			$login_ref = $login_ary['login_ref'];
			
			$ary = array();
			$ary['url'] = $login_url; 
			$ary['post_request'] = $request;
			$ary['proxy'] = $proxy;
			$ary['browser'] = $browser;
			$ary['ref'] = $login_ref;
			//echo $login_url.'&'.$request;exit;

			$content = my_spider::get_content_for_curl($ary);
			if(!$content){
				self::$error = my_spider::$error; 
				return false;
			}
			
			
			//判断第二次登录请求的反馈是否变化的地方
			if(!tsina_change::login_2($content,$change_note)){
				self::$error = 'tsina login 2 change';
				return false;
			}
			//end
			

			$preg_url = "/location\.replace\('(.*)'/iU"; 
			preg_match($preg_url,$content, $match_pre_url);
			$get_cookie_url = $match_pre_url[1]; 

			//模拟登录
			$ary_login = array();
			$ary_login['url'] = $get_cookie_url;
			if($proxy){
				$ary_login['proxy'] = $proxy;
			}
			$ary_login['browser'] = $browser;
			$ary_login['ref'] = $login_url;
			$login_back = my_spider::get_info_from_curl($ary_login,$user_key);
			$cookie_jar = $login_back['cookie_jar'];
			$login_content = $login_back['body'];
			$login_header = $login_back['header'];
			
			//判断第三次登录请求的反馈是否变化的地方
			if(!tsina_change::login_3($login_content,$change_note)){
				self::$error = 'tsina login 3 change';
				return false;
			}
			//判断新浪微博的默认版本
			if(!tsina_change::login_wvr($login_header,$cookie_jar,$change_note)){
				self::$error = 'tsina wvr change';
				return false;
			}
			//end
			
			//如果是新浪微博登录，适用，可取到 uid
			$preg1 = "/uniqueid\":\"(.*)\"/iU";
			preg_match($preg1,$login_content, $match1);
			$uid2 = trim($match1[1]);
			
			//$cookie_jar = my_spider::simulate_login($get_cookie_url,null,$user_key,$proxy,$browser,$login_url);
			//$uid2 = trim(my_spider::$uid);
			//登录end
			//echo '<br/>'.$uid2;exit;
			if(!self::$user['uid'] && is_numeric($uid2)) //保存账户 uid
			{ 
				require_once dirname(__FILE__).'/../../account/tsina.php';
				$a=null; 
				$a['uid'] = $uid2;
				$where_ary['user_name']=self::$user['user'];
				duap_account_tsina::update_account($a,$where_ary,true);
				self::$user['uid'] = $uid2; 
			}
			echo '<br/>登录啦<br/>';  

			//记录登录日志 
			$a=null;
			$a['aid']=self::$user['uid'];
			$cookie_jar2 = $cookie_jar;
			if(!$cookie_jar2) $cookie_jar2 = self::$error;
			$a['cookie_jar']=$cookie_jar2;
			$a['proxy']=$proxy['ip'];
			self::log_simulate_login($a);
			///

			//针对每个代理ip做登录计数器，超过额定值,20，强制重启adsl
			if($proxy['ip'])
			{
				$time1=20;  
				$key_t1=md5('tsina_login_p_ip_hr3fv3'.$proxy['ip']);
				$ts1=cm_process::get_times($key_t1);
				$ts1++;
				if($ts1>$time1)
				{
					cm_process::del_times($key_t1);
					require_once dirname(__FILE__).'/../../proxy/proxy.adsl.class.php';
					proxy_adsl::$reason='too_logins_per_ip';
					proxy_adsl::restart($proxy['id']);
				}
				else cm_process::add_times($key_t1,86400); 
			}
			///

			/*
			$login_back_content = my_spider::get_info_from_curl($ary_login,$cookie_key);
			if(!$login_back_content && !$login_back_content['cookie_jar']){
				self::$error = my_spider::$error;
				return false;
			}
			return $login_back_content['cookie_jar'];
			*/
		}
		cm_process::done($username.self::$key_simulate_login);
		return $cookie_jar;
	}
}

class tsina_login_encoder{
	static $n=0;
	static $o=8;
	
	static function shr32($x, $bits){  
		if($bits <= 0){  
			return $x;  
		}  
		if($bits >= 32){  
			return 0;  
		}  
		$bin = decbin($x);  
		$l = strlen($bin);  
		if($l > 32){  
			$bin = substr($bin, $l - 32, 32);  
		}elseif($l < 32){  
			$bin = str_pad($bin, 32, '0', STR_PAD_LEFT);  
		}  
		return bindec(str_pad(substr($bin, 0, 32 - $bits), 32, '0', STR_PAD_LEFT));  
	}

	static function utf8_2_unicode($word) {
	  if (is_array( $word)) $arr = $word;
	  else  $arr = str_split($word);
	  $bin_str = '';
	  foreach ($arr as $value) $bin_str .= decbin(ord($value));
	  $bin_str = preg_replace('/^.{4}(.{4}).{2}(.{6}).{2}(.{6})$/','$1$2$3', $bin_str);
	  return bindec($bin_str); //返回类似20320，汉字"你"
	  //return dechex(bindec($bin_str)); //如想返回十六进制4f60，用这句
	}

	static function char_code_at($a='',$i=1){
		$x=substr($a,$i,1);
		return self::utf8_2_unicode($x);   
	}

	static function char_at($a='',$i=1){
		$x=substr($a,$i,1);
		return $x;  
	}
	
	static function hex_sha1($s) {
        return self::big_a(self::p(self::z($s), strlen($s) * self::$o));
    }

	static function p($x, $f) {
		$x[$f >> 5] |= hexdec('80') << (24 - $f % 32);
        $x[(($f + 64 >> 9) << 4) + 15] = $f;
		$i=1;$t_count=count($x);while (current($x)) {if ($i == $t_count) {$t_key = key($x); }$i++;next($x); } //取最后的键值
		for ($i = 0; $i < $t_key; $i ++) if(!isset($x[$i])) $x[$i]='';
		$w = array(80);
        $a = 1732584193;
        $b = -271733879;
        $c = -1732584194;
        $d = 271733878;
        $e = -1009589776;
        for ($i = 0; $i < count($x); $i += 16) {
            $g = $a;
            $h = $b;
            $k = $c;
            $l = $d;
            $m = $e;
            for ($j = 0; $j < 80; $j++) {
                if ($j < 16) $w[$j] = $x[$i + $j];
                else $w[$j] = self::v($w[$j - 3] ^ $w[$j - 8] ^ $w[$j - 14] ^ $w[$j - 16], 1);
				$t = self::u(self::u(self::v($a, 5), self::q($j, $b, $c, $d)), self::u(self::u($e, $w[$j]), self::r($j)));
				$e = $d;
                $d = $c;
                $c = self::v($b, 30);
				$b = $a;
                $a = $t;
            }
            $a = self::u($a, $g);		
			
            $b = self::u($b, $h);
            $c = self::u($c, $k);
            $d = self::u($d, $l);
            $e = self::u($e, $m);
        }
        $r1=array($a, $b, $c, $d, $e);
		return $r1;
    }
	static function q($t, $b, $c, $d) {
        if ($t < 20) return ($b & $c) | ((~$b) & $d);
        if ($t < 40) return $b ^ $c ^ $d;
        if ($t < 60) return ($b & $c) | ($b & $d) | ($c & $d);
        return $b ^ $c ^ $d;
    }
	static function r($t) {
		if($t < 20){
			$r=1518500249;
		}else{
			if($t < 40){
				$r=1859775393;
			}else{
				if($t < 60){
					$r=-1894007588;
				}else{
					$r=-899497514;
				}
			}
		}

        return $r;
    }
	static function u($x, $y) {
        $a = ($x & hexdec('FFFF')) + ($y & hexdec('FFFF'));
		$b = ($x >> 16) + ($y >> 16) + ($a >> 16);
        return ($b << 16) | ($a & hexdec('FFFF'));
    }
	static function v($a,$b){
		$r = ($a << $b) | self::shr32($a,(32 - $b));
		return $r;
    }
	static function z($a){
		$b = array();
		$c = (1 << self::$o) - 1;
		for ($i = 0; $i < strlen($a) * self::$o; $i += self::$o) $b[$i >> 5] |= (self::char_code_at($a,$i/self::$o) & $c) << (24 - $i % 32);
		return $b;
	}
	
	static function big_a($a) {
        $b = self::$n ? "0123456789ABCDEF" : "0123456789abcdef";
        $c = "";
        for ($i = 0; $i < count($a) * 4; $i++) {
            $c .= self::char_at($b,($a[$i >> 2] >> ((3 - $i % 4) * 8 + 4)) & hexdec('F')) . self::char_at($b,($a[$i >> 2] >> ((3 - $i % 4) * 8)) & hexdec('F'));
        }
        return $c; 
    }
	
 
}//end class
