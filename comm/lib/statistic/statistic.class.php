<?php
//统计相关超类 by ziv 2011/8/24

require_once dirname(__FILE__).'/../cache/cache.class.php';
require_once dirname(__FILE__).'/../logs/simple.mq.php';
require_once dirname(__FILE__).'/../db/db.class.php';
class cm_statistic
{	
	static $statistics_table = 'comm';
	static $statistics_click_db = 'user_img_log';
	static function save_img_log($data){
		$key = 'cmstatisticsaveimglog'; //分类识别用的key
		$max = 1;  //阀值,达到阀值就会将所有数据以数组方式返回，否则返回空数组
		$smq = new simpleMQ($key,$max);
		$datalist = $smq->saveData($data);
		if(is_array($datalist) && $datalist){
			$db = cm_db::get_db(cm_db_config::get_ini(self::$statistics_table,'master'));
			$smq->saveto_db($datalist,$db,self::$statistics_click_db);
		}
	}
	
	static function save()
	{
		$k=$_GET['k'];
		if(!$k) return false;
	
		//统计
		$referer = $_SERVER['HTTP_REFERER'];
		$from = parse_url($referer);
		$host = $from['host'];
		$path = $from['path'];

		$data = array();
		$data['key'] = $k;
		$data['host'] = $host;
		$data['ref'] = $path;
		$data['browser'] = $_SERVER['HTTP_USER_AGENT'];
		if($_SERVER['HTTP_X_REAL_IP']) $data['ip'] = $_SERVER['HTTP_X_REAL_IP'];
		else if($_SERVER['HTTP_X_FORWARDED_FOR']) $data['ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if($_SERVER['REMOTE_ADDR']) $data['ip'] = $_SERVER['REMOTE_ADDR'];

		$data['create_time'] = time(); 
		self::save_img_log($data);
		return true;

	}
	
	static function get_img_cache(){
		$expire=3600*12;
		$key = 'cmstatisticgetimgcache2';
		$cache = new temp_cache;
		$ret = $cache->get($key);
		if (!$ret){
			$ret = self::get_img($x);
			$cache->saveOrUpdate($key,$ret,$expire);
		}
		$cache->close();
		return $ret; 
	}

	static function get_img()
	{
		$x=file_get_contents(dirname(__FILE__).'/1.png'); 
		return $x; 
	}

}//end class

?>