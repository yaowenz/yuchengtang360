<?php
/*
全站memcache 配置
array(server,权重)
*/
class cm_cache_config
{
	static function get($s=null)
	{
		//队列缓存用cach服务器,无群集
		$r['MEMCACHE_SERVER_SINGLE'][] = array("127.0.0.1","11211",1); 
		$r['MEMCACHEDB_SERVER_SINGLE'][] = array("127.0.0.1","11211",1); 
		//$r['MEMCACHEDB_SERVER_SINGLE'][] = array("127.0.0.1","21201",1); 

		$r['MEMCACHE_COMM_BASHIDASHA'][] = array("192.168.1.222","11211",1); 
		$r['MEMCACHEDB_COMM_BASHIDASHA'][] = array("192.168.1.222","21211",1);  

		//$r['MEMCACHE_SERVER'][] = array("127.0.0.1","21201",2); //权重
		$r['MEMCACHE_SERVER'][] = array("127.0.0.1","11211",1);

		$r['MEMCACHEDB_SERVER'][] = array("127.0.0.1","11211",1); 
		//$r['MEMCACHEDB_SERVER'][] = array("127.0.0.1","11212",1); 

		$r['TT_SERVER'][] = array("127.0.0.1","11211",1);

		if($s) return $r[$s];
		else return $r;
	}
}
?>
