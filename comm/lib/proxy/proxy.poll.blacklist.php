<?php
/*
代理 轮询，以及黑名单基础类
by ziv 2011/11/2 
*/

require_once dirname(__FILE__).'/../db/db.class.php';
require_once dirname(__FILE__).'/proxy.adsl.class.php';
require_once dirname(__FILE__).'/proxy.adshell.class.php'; 
require_once dirname(__FILE__).'/../process.class.php';
require_once dirname(__FILE__).'/../poll.class.php'; 
require_once dirname(__FILE__).'/../spider/my_spider/myspider.class.php';

class proxy_poll_bl{ 

	static $k='xdsgherstkv9'; //default 
	static $m='MEMCACHE_COMM_BASHIDASHA'; //default memcache config use 222 server
	static $bk_time=21600;//6小时 
	static $proxy_count;
	static $error;
	  
	static function check_proxy($ip,$port,$name=null,$pwd=null)
	{
		$url='http://www.baidu.com'; 
		$a['url']=$url;
		$a['time_out']=10; 
		$a['proxy']['ip']=$ip; 
		$a['proxy']['port']=$port; 
		$a['proxy']['p']='http://'.$ip.':'.$port;
		my_spider::get_info_from_curl($a);
		if(my_spider::$error=='0:proxy error') 
		{
			proxy_adshell::delete_one($ip);  
			self::delete_proxy_from_poll($ip);  
			return false;
		}
		else return true; 
	}

	//将某代理加入黑名单    
	static function add_black_list($ip,$key0=null,$bool=false)  
	{
		if(!$ip) return false; 
		$time=self::$bk_time;
		if(!$key0) $key0=self::$k;
		$key=$key0.'blacklistv1'.$ip; 
		$cache = new temp_cache;
		$cache->mem_config_str = cm_cache_config::get(self::$m); 
		$cache->saveOrUpdate($key,1,$time);
		$cache->close();

		self::delete_proxy_from_poll($ip,$key0,$bool); 
 
		return true; 
	}
	
	//删除黑名单中某代理
	static function del_black_list($ip)
	{
		if(!$ip) return false;
		$key=self::$k.'blacklistv1'.$ip;
		$cache = new temp_cache;
		$cache->mem_config_str = cm_cache_config::get(self::$m); 
		$cache->delete($key);
		$cache->close();
		return true; 
	}
	
	//判断是否在黑名单里
	static function is_in_black_list($ip,$key0)
	{
		if(!$ip) return false;
		if(!$key0) $key0 = self::$k;
		$key=$key0.'blacklistv1'.$ip; 
		$cache = new temp_cache;
		$cache->mem_config_str = cm_cache_config::get(self::$m); 
		$r=$cache->get($key);
		$cache->close();
		if($r) return 1;
		else return 0;
	}

	//每隔20秒取一次 adsl ip代理，排除掉ip黑名单内的ip，进入到轮询ip池  
	static function refresh_poll_list() 
	{ 
		$key=self::$k;  
		if(!cm_process::runing($key.'run',20)) return false;    
		$a0=proxy_adsl::get_list();//从adsl库里取
		if(cm_process::runing($key.'run2',600)) 
		{ 
			$a1=proxy_adshell::get_list();//从肉鸡ip库里取
		}
		else
		{
			$a1=array();
		}
		$a=array_merge($a0, $a1);
 
		self::add_poll_list($a); 

		return true;  
	}
	
	//排除掉ip黑名单内的ip，进入到轮询ip池 
	static function add_poll_list($a=array(),$key=null) 
	{ 
		if(!$a || !is_array($a)) return false; 
		if(!$key) $key=self::$k;   
		
		foreach($a as $k0=>$i0) {
			if(!$a[$k0]['port']) $a[$k0]['port']=proxy_adsl::$port; 
			$a[$k0]['p']='http://' . $i0['ip'] . ':' . $a[$k0]['port'];  
		}
		
		comm_poll::$k=$key;  
		comm_poll::$time=86400*5;  
		//$t=time();
		foreach($a as $k=>$i) 
		{
			if(!self::is_in_black_list($i['ip'],$key)) 
			{
				comm_poll::add($i['ip'],$i);  
			}
		}
		//echo '<br/> -- add use time :';print_R(time()-$t);echo ' -->  ';    
		//$r=comm_poll::get_one();print_R($r);   
		return true;  
	}
	 
    //从代理轮询列表里删除一个代理
	static function delete_proxy_from_poll($ip,$key=null,$bool=false)
	{
		if(!$ip) return false;
		if(!$key) $key=self::$k; 
		comm_poll::$k=$key;   
		comm_poll::$time=86400*5;  
		return comm_poll::delete_one($ip,$bool);  
	}

	static function delete_poll()
	{ 
		$key=self::$k;
		if(!$key) return false;
		comm_poll::$k=$key;  
		comm_poll::$time=86400*5;  
		return comm_poll::delete_poll(); 
	}

	static function get_one_proxy() 
	{
		self::$error=null; 
		$key=self::$k;
		comm_poll::$k=$key;  
 
		//echo '          ----- 11111111 ----------->           '; 
		$count1=self::get_poll_count(); 
		//echo '<br/>          ----- count1 ----------->           '.$count1;echo '<br/>'; 


		//10分钟一次,轮询代理少于 100 个，补充100个	 有3个地方会调用，一个发edm 一个注册edm  , gather
		$num_limit=150; 
		if(cm_process::runing(md5('prsheetfmtao1ioolg4fv5'.$key),600))  
		{
			if($count1<$num_limit)   
			{
				$ra=proxy_adshell::get_from_taobao1_into_poll($num_limit);   
				$count1=self::get_poll_count(); 
			}
		}
		
		//$t=time();
		self::refresh_poll_list(); //从adsl ip池读入到轮询池  
		//echo '<br/> -- refresh_poll_list use time :';print_R(time()-$t);echo ' -->  ';   
		
		//由于每天3点到10点半期间ip很少，所以要做一个ip保护机制，当ip池里ip少于15的时候，不注册，不发edm 
		if($count1<15)   
		{
			self::$error='proxy_not_enough'; 
			return false;
		}

		//$t=time();
		$r=comm_poll::get_one();
		//echo '<br/> -- comm_poll::get_one use time :';print_R(time()-$t);echo ' -->  ';  
		return $r; 
	}
	
	//取特定地区代理
	static function get_one_dq_proxy($dq) 
	{ 
		if(!$dq) return false;

		$key=md5(self::$k.'dq:'.$dq);
		comm_poll::$k=$key;  

		//echo '<br/>key:<br/>';print_R($key);echo '<br/><br/>';  
 
		$num_limit=50;    
		$k_runing='gefmto1iopbsgv3';
		if(cm_process::runing($k_runing,600))  
		{ 
			$a=null;$a0=null;
			$a0['region_key']=$dq; 
			$a=proxy_adshell::get_list($a0);//从肉鸡ip库里取 

			//echo '<br/>a:<br/>';print_R($a);echo '<br/><br/>'; 

			$p_count=comm_poll::get_count();  
			//echo '<br/>p_count:<br/>';print_R($p_count);echo '<br/><br/>'; 

			if(!$p_count || $p_count<5)    
			{ 
				$r1=proxy_adshell::get_from_taobao1_into_poll($num_limit,$dq);  
				if($r1 && is_array($r1)) $a=array_merge($a, $r1);
			}

			if($a && is_array($a))
			{
				self::add_poll_list($a,$key);   
			} 
			else cm_process::done($k_runing); 
		}

		$r=comm_poll::get_one(); 

		self::add_black_list($r['ip'],$key,true);  
		//echo '<br/> -- comm_poll::get_one use time :';print_R(time()-$t);echo ' -->  ';  
		return $r; 
	}


	//取北京上海广州 北上广地区ip
	static function get_one_bsg_proxy() 
	{ 
		$key=md5(self::$k.'dq:bsg'); 
		comm_poll::$k=$key;  

		//echo '<br/>key:<br/>';print_R($key);echo '<br/><br/>';  
 
		$num_limit=50;    
		$k_runing='gefmto1iopbsgv3';
		if(cm_process::runing($k_runing,600))  
		{ 
			$a0['region_key']='北京市';  
			$a=proxy_adshell::get_list($a0);
			$a0['region_key']='上海市'; 
			$a2=proxy_adshell::get_list($a0);
			$a=array_merge($a, $a2);
			//$a0['region_key']='广东省'; 
			//$a3=proxy_adshell::get_list($a0);
			//$a=array_merge($a, $a3);
			
			//echo '<br/>a:<br/>';print_R($a);echo '<br/><br/>'; 

			$p_count=comm_poll::get_count();  
			//echo '<br/>p_count:<br/>';print_R($p_count);echo '<br/><br/>'; 

			if(!$p_count || $p_count<10)    
			{ 
				$r1=proxy_adshell::get_from_taobao1_into_poll($num_limit,'北京市');  
				if($r1 && is_array($r1)) $a=array_merge($a, $r1);
				$r1=proxy_adshell::get_from_taobao1_into_poll($num_limit,'上海市');  
				if($r1 && is_array($r1)) $a=array_merge($a, $r1);
				//$r1=proxy_adshell::get_from_taobao1_into_poll($num_limit,'广东省');  
				//if($r1 && is_array($r1)) $a=array_merge($a, $r1);
			}

			if($a && is_array($a))
			{
				self::add_poll_list($a,$key);   
			} 
			else cm_process::done($k_runing); 
		}

		$r=comm_poll::get_one(); 

		self::add_black_list($r['ip'],$key,true);  
		//echo '<br/> -- comm_poll::get_one use time :';print_R(time()-$t);echo ' -->  ';  
		return $r; 
	}

	static function get_one_proxy_once($kk='') 
	{
		$key=md5(self::$k.'once:'.$kk);
		comm_poll::$k=$key;  

		//echo '<br/>key:<br/>';print_R($key);echo '<br/><br/>';  
 
		$num_limit=50;    
		$k_runing='gefmtonceopbsgv3';
		if(cm_process::runing($k_runing,600))  
		{ 
			$a=null;
			$a=proxy_adshell::get_list();//从肉鸡ip库里取 

			$p_count=comm_poll::get_count();  
		
			if(!$p_count || $p_count<5)    
			{ 
				$r1=proxy_adshell::get_from_taobao1_into_poll($num_limit);  
				if($r1 && is_array($r1)) $a=array_merge($a, $r1);
			}

			if($a && is_array($a))
			{
				self::add_poll_list($a,$key);   
			} 
			else cm_process::done($k_runing); 
		}

		$r=comm_poll::get_one(); 

		self::add_black_list($r['ip'],$key,true);  
		//echo '<br/> -- comm_poll::get_one use time :';print_R(time()-$t);echo ' -->  ';  
		return $r; 
	}



	static function get_poll_list()
	{
		$key=self::$k;
		comm_poll::$k=$key;  
		return comm_poll::get_list(); 
	}
	 
	static function get_poll_count()
	{
		$key=self::$k;
		comm_poll::$k=$key;  
		return comm_poll::get_count();  
	}
	
}//end class 