<?php
@header("content-type:text/html;charset=utf-8"); 
//用memcache模拟队列功能 基类  by ziv 2011/8/13
require_once dirname(__FILE__).'/cache/cache.class.php';
class cm_queue
{
	static function set($a=array(),$key=null,$time=3600){ 
		if(!$key) return false; 
		if(!is_array($a)) return false; 
		
		$cache = new temp_cache;
		$info = $cache->get($key);
		if($info) return false;
		$cache->saveOrUpdate($key,$a,$time);
		$cache->close();
		return true;
	}

	static function get($key){ 
		if(!$key) return false;
		$cache = new temp_cache;
		$info = $cache->get($key);
		$cache->close();
		if($info){
			return $info;
		}else {
			return false;
		}
	}

	static function del($key){ 
		if(!$key) return false; 
		$cache = new temp_cache;
		$cache->delete($key);
	}

	static function get_one($key=null,$time=3600){ 
		if(!$key) return false; 
		
		$cache = new temp_cache;
		$info = $cache->get($key);
		if(!$info) return false;
		$rt = array_shift($info);
		if ($info){ 
			$cache->saveOrUpdate($key,$info,$time);
		}
		else{
			$cache->delete($key);
		}
		$cache->close();
		if($rt) return $rt; else return false;  
	}

	//轮询取项目,$ts 次数
	static function get_poll($key=null,$time=86400){  
		if(!$key) return false; 
		
		$cache = new temp_cache;
		$info = $cache->get($key);
		if(!$info) return false;
		$rt = array_slice($info,0,1,true);
		$cache->close(); 
									//echo '--------get_poll: ';print_R($rt);echo ' --------<br/>';   
		if($rt) return $rt; else return false;  
	} 
	
	//轮询 $ts 次数
	static function set_poll($a=array(),$key=null,$ts=1,$time=86400){ 
									//echo '--------set_poll: ';print_R($a);echo ' --------<br/>';    
		if(!$key) return false; 
		if(!is_array($a)) return false; 
		if($a[0]['id'])
		{
			foreach($a as $k2=>$i2)
			{
				$x[$i2['id']]=$i2;
			}
		}
		else $x=$a;
		
		$key_t=$key.'_t_i_m_e_s1';

		$cache = new temp_cache;
		$cache->saveOrUpdate($key,$x,$time);
		foreach($x as $k=>$i)
		{
			$cache->saveOrUpdate($key_t.$k,$ts,$time); 
		}
		$cache->close();
		return true; 
	}

	//完成一次轮询
	static function done_poll($key=null,$time=86400){ //$k 具体哪条的 key
		if(!$key) return false;  
		
		$key_t=$key.'_t_i_m_e_s1';

		$cache = new temp_cache; 
		$info2 = $cache->get($key);
		$rt = array_slice($info2,0,1,true);
		$info2 = array_slice($info2,1,null,true);
		$k=key($rt);
		$now_ts=$cache->get($key_t.$k);
		//echo '--------done_poll times: ';print_R($now_ts);echo ' --------<br/>';     
		if($now_ts>1)
		{
			$cache->saveOrUpdate($key_t.$k,$now_ts-1,$time); 
			$info2 = $info2+$rt; 
		}
		else
		{
			$cache->delete($key_t.$k);
			if(!$info2) $cache->delete($key); 
		}
									
		if($info2) $cache->saveOrUpdate($key,$info2,$time);
		$cache->close();
		return true;  
	}
	
	//轮询队列删除一条  
	static function delete_poll($key=null,$k=null)	//$k 具体哪条的 key
	{ 
		if(!$key) return false;  
		$key_t=$key.'_t_i_m_e_s1';
		$cache = new temp_cache; 
		$cache->delete($key_t.$k);
		$info = $cache->get($key);
		if($info) 
		{
			if($k) unset($info[$k]); 
			else array_shift($info);
		}
		if(!$info) $cache->delete($key);
		$cache->saveOrUpdate($key,$info,$time);
		$cache->close();
		return true;  
	}
	

	//清空轮询
	static function clear_poll($key=null){  
		if(!$key) return false; 
		$cache = new temp_cache; 
		$cache->delete($key); 
		$cache->close();
		return true;  
	}

}//end class