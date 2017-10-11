<?php
//轮询相关
//2011/10/29 by ziv
require_once dirname(__FILE__).'/db/db.class.php';
require_once dirname(__FILE__).'/cache/cache.class.php';
require_once dirname(__FILE__).'/process.class.php';

class comm_poll 
{
	static $time=86400;//有效期
	static $keyindex = "mlyix46hv2";
	static $k;
	
	//统计个数
	static function get_count()
	{
		$k=self::$k;
		if(!$k) return false;
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_comm','master'));  
		$select = $db->select();
		$select	->from('poll', 'count(*)')
				->where('poll_key = ?', $k);
		$sql = $select->__toString();     
		$v=$db->fetchOne($sql); 
		return $v; 
	}

	static function get_list()
	{
		$k=self::$k;
		if(!$k) return false;
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_comm','master'));  
		$select = $db->select();
		$select	->from('poll', '*')
				->where('poll_key = ?', $k);
		$sql = $select->__toString();     
		$v=$db->fetchAll($sql); 
		if($v && is_array($v))
		{
			$a=null;
			foreach ($v as $k1=>$i)
			{
				$a[$i['value_key']]=unserialize($i['value']);
			}
		}   
		self::delete_expired();
		return $a;  
	}

	//$k poll_key  $ki item_key  $i item 
	static function add($ki,$i)   
	{
		$k=self::$k;
		if(!$k || !$ki || !$i) return false;
		 
		$pkey="pcommpolladdh4ye8v3".$k;
		if(!cm_process::runing($pkey,5)) return false;

		$db = cm_db::get_db(cm_db_config::get_ini('ncg_comm','master'));  
		$a['create_time'] = time();
		$a['expired_time'] = time()+self::$time; 
		$a['poll_key'] = $k;
		$a['value_key'] = $ki;
		$a['value'] = serialize($i);
 
		try{@$db->insert('poll', $a);}catch(Exception $e){} 
  
		cm_process::done($pkey);
		return true;
	} 

	static function get_one() 
	{
		$k=self::$k;
		if(!$k) return false; 
		
		$pkey="pcommpogetonedh4y6v1".$k;
		if(!cm_process::runing($pkey,5)) return false;
 
		$keyindex=self::$keyindex.$k;
	
		$cache = new temp_cache;
		$cache->mem_config_str = cm_cache_config::get('MEMCACHE_COMM_BASHIDASHA'); 
		$vkey=$cache->get($keyindex);

		if(!$vkey) 
		{
			$vkey=0;
		}
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_comm','master'));  
		$select = $db->select();
		$select	->from('poll', '*')
				->where('poll_key = ?', $k)
				->where('id > ?', intval($vkey))
				->order('id')
				->limit(1); 
		$sql = $select->__toString();     
		$v=$db->fetchRow($sql); 
		if(!$v) 
		{
			$vkey=0;
			$select = $db->select();
			$select	->from('poll', '*')
				->where('poll_key = ?', $k)
				->where('id > ?', intval($vkey))
				->order('id') 
				->limit(1); 
			$sql = $select->__toString();     //print_R($sql);exit; 
			$v=$db->fetchRow($sql); 
		}

		if($v && $v['id'])
		{
			$cache->saveOrUpdate($keyindex,$v['id'],self::$time); 
			$r=unserialize($v['value']);
		}
		 
		$cache->close(); 
		cm_process::done($pkey);  
		return $r;   
	}
	 
	//$k poll_key  $ki item_key  $i item  
	static function delete_one($ki,$if_poll_key=true)
	{
		$k=self::$k;
		if(!$k) return false; 

		$pkey="pcommpolldeloneg3rv1".$k;
		if(!cm_process::runing($pkey,5)) 
		{	
			return false;
		}

		$db = cm_db::get_db(cm_db_config::get_ini('ncg_comm','master'));  
		
		$where = $db->quoteInto('value_key = ?',$ki); 
		if($if_poll_key)
		{
			$where .= $db->quoteInto(' and poll_key = ? ',$k);
		}

		$db->delete('poll', $where);
 
		cm_process::done($pkey);
		return true;  
	}

	static function delete_poll()
	{
		$k=self::$k;
		if(!$k) return false; 

		$pkey="pcomdelepollnegwv1".$k;
		if(!cm_process::runing($pkey,5)) 
		{	
			return false;
		}

		$db = cm_db::get_db(cm_db_config::get_ini('ncg_comm','master'));  
		$where = $db->quoteInto('poll_key = ?', $k); 
		$db->delete('poll', $where);
		
		$keyindex=self::$keyindex.$k;
		$cache = new temp_cache;
		$cache->mem_config_str = cm_cache_config::get('MEMCACHE_COMM_BASHIDASHA'); 
		$cache->delete($keyindex);
		$cache->close(); 
		 
		cm_process::done($pkey);
		return true;  
	}
	
	static function delete_expired()//删除过期的 
	{
		$k=self::$k;
		if(!$k) return false; 

		$pkey="pcomdeletexpidgu1".$k;
		if(!cm_process::runing($pkey,5)) 
		{	
			return false;
		}

		$db = cm_db::get_db(cm_db_config::get_ini('ncg_comm','master'));  
		$where = $db->quoteInto('expired_time < ?', time());  
		$db->delete('poll', $where);
		 
		cm_process::done($pkey);
		return true;  
	}
  
}//end class

 