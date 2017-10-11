<?php
//进程控制相关 by ziv 2011-6-25
require_once dirname(__FILE__).'/cache/cache.class.php';
class cm_process{

	
	
	//判断是否有key进程在运行，实现小颗粒单进程控制
	static function is_runing($key,$mem_config_str=null)
	{
		$key = $key.'cm_process';
		$cache = new temp_cache;
		if($mem_config_str) $cache->mem_config_str = $mem_config_str;
		$ret = $cache->get($key); 
		$cache->close();
		//if($ret){echo'<br/><br/> -- is_runing -- key: -- '.$key.' -- time: -- '.time().' --><br/><br/>';}
		if($ret) return true;
		else return false; 
	}
	
	//占位 锁 成功返回 true 失败返回 false  
	static function runing($key,$expire=3,$mem_config_str=null)  
	{
		$key = $key.'cm_process';
		$cache = new temp_cache; 
		if($mem_config_str) $cache->mem_config_str = $mem_config_str;
		//$o=$cache->get_server($key);
		$r=$cache->add($key,1,$expire);  
		//if($r){echo'<br/><br/> -- runing -- key: -- '.$key.' -- start_time: -- '.time().' --><br/><br/>';}
		$cache->close();
		return $r;
	}
  
	static function done($key,$mem_config_str=null)  
	{
		$key = $key.'cm_process';
		$cache = new temp_cache; 
		if($mem_config_str) $cache->mem_config_str = $mem_config_str;
		$cache->delete($key);
		$cache->close();
		//echo'<br/><br/> -- done -- key: -- '.$key.' -- end_time: -- '.time().' --><br/><br/>';
		return true;
	}
	
	//基数器，用来控制次数逻辑，如同一ip运行 10 触发
	static function add_times($key,$time=86400,$mem_config_str=null)  
	{	
		if(!$key) return false;
		$cache = new temp_cache;
		if($mem_config_str) $cache->mem_config_str = $mem_config_str;
		$info = $cache->get($key);
		if($info) $info=intval($info)+1;
		else $info = 1;
		$cache->saveOrUpdate($key,$info,$time);
		$cache->close();
		return true;
	} 

	static function get_times($key,$mem_config_str=null)  
	{	
		if(!$key) return false;
		$cache = new temp_cache;
		if($mem_config_str) $cache->mem_config_str = $mem_config_str;
		$info = $cache->get($key);
		if(!$info) $info=0;
		$cache->close();
		return $info; 
	}
	
	static function del_times($key,$mem_config_str=null)  
	{	
		if(!$key) return false;
		$cache = new temp_cache;
		if($mem_config_str) $cache->mem_config_str = $mem_config_str;
		$cache->delete($key);
		$cache->close(); 
		return true; 
	}

}//end class