<?php
/*
用户相关处理类
by zx 2010-6-10
*/ 
require_once dirname(__FILE__).'/../container.class.php';
require_once dirname(__FILE__).'/../db/db.class.php'; 
require_once dirname(__FILE__).'/../cache/cache.class.php'; 

define('COMM_CACHE_TIME',3600);	
define('cm_MEMCACHE_LOGIN_STATE','cm_MEMCACHE_LOGIN_STATE'); 
define('cm_COOKIE_LOGINED_UID','userid');

require_once dirname(__FILE__).'/../cache/cache.class.php';
class cm_user{
 	
	//用户名字符串长度的边界值
	static $uname_min = 4;
	static $uname_max = 20;
	///
	
	static $MAIL_ACTIVE_WORD="microblog.#dzhwb#";//给用户发email激活验证的钥匙 

	static $webapi_regist_key = '!(*$)*@(!(*^)^@#'; //新注册用户的webapi钥匙 
	static $webapi_updatepoint_key = '!(*$)*@(!(*^)^@#'; //奖励积分的webapi钥匙 
	
	static $key_base = 'cm_USER_INFO_'; //用户信息 cache key 跟 uid 
	static $key_base_email = 'cm_USER_INFO_BY_EMAIL_'; //用户信息 cache key 跟 email 
	static $key_get_uid = 'cm_USERID_FROM_UNAME_'; //通过用户名取用户uid cache key跟 user_name 
	
	static $slave_or_master = "slave"; //决定读主或从服务器的开关
	
	static $uid = null; //全局变量
	static $login_user_info = null; //全局变量
	
	static function uid(){return $_COOKIE[cm_COOKIE_LOGINED_UID];}//取登录用户uid

	//取用户cookie的值与memcache里比较 判断用户是否登录
	static function is_login() 
	{
		if(self::uid() && isset($_COOKIE['hash'])){
			$userid = self::uid();
			$hash = $_COOKIE['hash'];
			$cache = new temp_cache;
			if($hash==$cache->get('dzh.usercenter'.$userid)){
				return $userid;
			}
			$cache->close();
		}
		return 0;
	} 

	//取用户cache信息 通过 email  
	static function get_by_email($email = null){ 
		if(!$email) return false; 
		$key = self::$key_base_email . $email;

		$time = COMM_CACHE_TIME; 

		$cache = new temp_cache;
		$u_info = $cache->get($key);
		
		if (empty($u_info) || $_GET['__DEBUG'] == 1){
			$u_info = self::get_from_db_by_email($email);
			$cache->saveOrUpdate($key,$u_info,$time);
		}
		$cache->close();
		if($u_info) return $u_info; else return false;
	}
	//从数据库取用户数据 通过 email 
	static function get_from_db_by_email($email){ 
		$user_db = cm_db::get_db(cm_db_config::get_ini('microblog',self::$slave_or_master));    
		$select = $user_db->select();
		$select	->from('tb_user', '*')
				->where('email = ?', $email)
				->limit(1);
		$sql = $select->__toString();    //die($sql);  
		$u_info = $user_db->fetchRow($sql); 
		if($u_info) return $u_info; else return false; 
	}

	//删除用户信息缓存
	static function clear_cache_user($uid){  
		if(!is_numeric($uid)) return false;
		if($uid){
			$cache = new temp_cache; 
			$cache->delete(self::$key_base . $uid);
			$cache->close();
		}
	} 
	//取用户信息  
	static function get($uid = null){ 
		if(!is_numeric($uid)) $uid = self::uid();
		if(!is_numeric($uid)) return false;
		$key = self::$key_base . $uid;

		$time = COMM_CACHE_TIME; 

		$cache = new temp_cache;
		$u_info = $cache->get($key);
		
		if (empty($u_info) || $_GET['__DEBUG'] == 1){
			$u_info = self::get_from_db($uid);
			$cache->saveOrUpdate($key,$u_info,$time);
		}
		$cache->close();
		if($u_info) return $u_info; else return false;
	}

	//取用户数据
	static function get_from_db($uid){  
		$user_db = cm_db::get_db(cm_db_config::get_ini('microblog',self::$slave_or_master));  
		$select = $user_db->select();
		$select	->from('tb_user', '*') 
				->where('id = ?', $uid)
				->limit(1);
		$sql = $select->__toString();    
		$user_info = $user_db->fetchRow($sql);
		if($user_info) return $user_info; else return false;
	}


	//通过用户名取用户uid
	static function get_uid_by_name($uname){  
		if(!$uname) return false;
		$key = self::$key_get_uid . $uname;

		$time = 3600*2;

		$cache = new temp_cache;
		$u_id = $cache->get($key);
		
		if (empty($u_id) || $_GET['__DEBUG'] == 1){
			$u_id = self::get_uid_from_db($uname);
			$cache->saveOrUpdate($key,$u_id,$time);
		}
		$cache->close();
		if($u_id) return $u_id; else return false;
	}
	//通过用户名取用户uid db操作
	static function get_uid_from_db($uname){ 
 		$user_db = cm_db::get_db(cm_db_config::get_ini('microblog','slave'));    
		
		$select = $user_db->select();
		$select	->from('tb_user', 'id')
				->where('username = ?', $uname)
				->limit(1);
		$sql = $select->__toString();    
		$uid = $user_db->fetchOne($sql);
		if($uid) return $uid; else return false;
	}

	//判断用户的email 是否存在 
	static function is_email_exist($email){ 
		$user_db = cm_db::get_db(cm_db_config::get_ini('microblog','slave'));    
		$select = $user_db->select();
		$select	->from('tb_user', 'id')
				->where('email = ?', $email)
				->limit(1);
		$sql = $select->__toString();    //die($sql);
		$uid = $user_db->fetchOne($sql);
		if($uid) return $uid; else return false; 
	}
	
	//奖励(改变)用户积分
	static function update_point($point,$uid = null,$reasonid = 1){
		/* 说明
			$reasonid	奖励积分	原因
			1			10			股民词典添加新词，审核通过 
		*/

		if(!is_numeric($uid)) $uid = self::uid();
		if(!is_numeric($uid)) return false;
		if(!is_numeric($point)) return false;

		$scretkey = self::$webapi_updatepoint_key;
		$key = md5(md5($uid.$point.$reasonid.$scretkey));

		require_once dirname(__FILE__).'/../../config/url.config.php';
		$url = cm_url::get('microblog').'/webApi/updatepoint/?uid=' . $uid . '&point=' . $point . '&reasonid=' . $reasonid . '&key=' . $key;
		
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10);
		$out = curl_exec($ch);
		$rt = json_decode($out); 
		return $rt; 
	}

	//新注册用户
	static function regist_user($uname,$upass,$email){
		if(!$uname) return false;
		if(!$upass) return false;
		if(!$email) return false;
		
		$scretkey = self::$webapi_regist_key;
		$key = md5(md5($uname.$upass.$email.$scretkey));

		require_once dirname(__FILE__).'/../../config/url.config.php';
		$url = cm_url::get('microblog').'/webApi/adduser/?uname=' . $uname . '&upass=' . $upass . '&email=' . $email . '&key=' . $key;
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10);
		$out = curl_exec($ch);
		$rt = json_decode($out); 
		return $rt;
	}
 
	//取不重复的用户名 当用户名重复的时候自动加1 ,如果用户名超过边界 补全 _ 或者截断
	static function get_user_name($uname){ 
		if(!$uname) return false;
		$n = 0;

		$n_len=strlen($uname);
		if($n_len < self::$uname_min){
			for($i=0;$i<(self::$uname_min-$n_len);$i++){$uname .= 'x';}
		}

		if($n_len > self::$uname_max){
				$uname = substr($uname,0,self::$uname_max-$n_len);
		}

		$uname_t = $uname;  
		$can = self::can_regist($uname_t);if(!$can) return false;
		while($can=='can_not')
		{
			$n++;
			$uname_t = $uname . $n;
			$n_len=strlen($uname_t);
			if($n_len < self::$uname_min){
				for($i=0;$i<(self::$uname_min-$n_len);$i++){$uname .= 'x';}
				$uname_t = $uname . $n;
			} 
			
			if($n_len > self::$uname_max){
				$uname = substr($uname,0,self::$uname_max-$n_len);
				$uname_t = $uname . $n;
			}
			$can = self::can_regist($uname_t);if(!$can) return false;
		}
		return $uname_t;
	} 
	
	//通过用户名，或者email，到账户中心判断用户是否能注册   返回TRUE说明可以注册
	static function can_regist($uname,$email = null){
		if(!$uname && !$email) return false;
		require_once dirname(__FILE__).'/../../config/url.config.php';
		$url = cm_url::get('microblog').'/userExistCheck';
		$ch = curl_init();

		if($uname) $data['username'] = $uname;
		if($email) $data['email'] = $email;

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,5);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$r = curl_exec($ch);
		curl_close($ch);
		if($r==null) return false; 
		else if($r == '1') return 'can';
		else if($r === '0') return 'can_not';  
		else {echo "<!--wabapi er-->";return false;}
	}
}
?>
