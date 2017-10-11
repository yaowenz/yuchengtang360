<?php
//队列cache体系
require_once COMM_PATH.'/comm.functions.php';
require_once COMM_PATH.'/lib/cache/cache.class.php';
require_once COMM_PATH.'/lib/cache/cache.db.class.php';

class q_cache
{
	public $a;
	public $k;
	public $clear_cache;
	public $time1;
	public $key1;
	public $info;
	public $cache;
	public $cache_tt;
	public $then;

	public function step1()
	{
		if($this->a['cache_time']) $this->time1=$this->a['cache_time'];
		$this->key1 = md5(serialize(array_deep_to_char($this->a)).$this->time1.$this->k);
		$this->cache = new temp_cache;
		$this->cache_tt=new cache_db_big_class;
		//$this->cache_tt=new seria_cache;
		$this->info = $this->cache_tt->get($this->key1);
		if($this->info===false || $_GET['__DEBUG'] == 1 || $this->clear_cache || ($_SERVER['REMOTE_ADDR']==$_SERVER['SERVER_ADDR'] && !$this->cache->get($this->key1)))
			$this->then=1;
		else $this->then=2;
		if($this->info) $this->info=unserialize(gzuncompress($this->info));
	}

	public function step2()
	{
		if($this->then==1)
		{
			$this->cache_tt->saveOrUpdate($this->key1,gzcompress(serialize($this->info),9));
			$this->cache->saveOrUpdate($this->key1,1,$this->time1);
		}
		else if(!$this->cache->get($this->key1) && $_SERVER['REMOTE_ADDR']!=$_SERVER['SERVER_ADDR'])
		{
			//进入异步调用队列
			require_once DOCUMENT_ROOT.'/../crontab/http_request_queue.lib.php';
			$x['url']='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$r_o=new http_request_queue;$r_o->insert_queue($x);
			///
		}
		$this->cache_tt->close();
		$this->cache->close();
	}

}//end class