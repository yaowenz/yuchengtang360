<?
/**
 * 用户点击统计
 * created by jay 2011-8-15
 */
require_once dirname(__FILE__).'/../cache/cache.class.php';
require_once dirname(__FILE__).'/../logs/simple.mq.php';
require_once dirname(__FILE__).'/../db/db.class.php';
class statistics_log{
	static function save_click_log($data){
		if(!$data['key1']) return false;
		$url = self::get_url_by_key_cache($data['key1']);
		if(!$url) return false;
 
		$key = 'click_statistics_save_log'; //分类识别用的key
		$max = 1;  //阀值,达到阀值就会将所有数据以数组方式返回，否则返回空数组
		$smq = new simpleMQ($key,$max);
		$datalist = $smq->saveData($data);
		if(is_array($datalist) && $datalist){
			$db = cm_db::get_db(cm_db_config::get_ini('comm','master'));
			$smq->saveto_db($datalist,$db,'user_click_log');
		}

		return $url; 
	}
	
	static function get_url_by_key_cache($k){  
		if(!$k) return false; 
		$expire=3600;
		$key='statilgetlbyeyche3v13'.$k; 
		$cache = new temp_cache;
		$info = $cache->get($key); 
		if (!$info){ 
			$info = self::get_url_by_key($k);
			$cache->saveOrUpdate($key,$info,$expire);
		} 
		$cache->close();
		if($info) return $info; else return false; 
	}

	static function get_url_by_key($k){ 
		if(!$k) return false;
		$k=intval($k);
		$db = cm_db::get_db(cm_db_config::get_ini('comm','master'));
		$table = 'user_click_log_key1';
		
		$select = $db->select();
		$select->from($table, 'url');
		$select->where('id = ?',$k);
		$sql = $select->__toString();//echo $sql;exit;
		$r=$db->fetchOne($sql);
		if($r) return $r; 
	}
	
	static function get_key($url)
	{
		if(!$url) return false;
		$url=preg_replace("/(^http:\/\/)(.*)/i","\$2",$url);
		$url=preg_replace("/(^http%3A%2F%2F)(.*)/i","\$2",$url);
 
		$db = cm_db::get_db(cm_db_config::get_ini('comm','master'));
		$table = 'user_click_log_key1';
		
		/*
		$select = $db->select();
		$select->from($table, 'id');
		$select->where('url = ?',$url);
		$sql = $select->__toString();//echo $sql;exit;
		$r=$db->fetchOne($sql);
		if($r) return $r;
		*/
		 
		$row = array( 
			'url' => $url 
		); 
		$db->insert($table, $row);
		$last_insert_id = $db->lastInsertId();
		return $last_insert_id; 
	} 
}
?>