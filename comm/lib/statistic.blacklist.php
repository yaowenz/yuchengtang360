<?php
/*
edm 针对对方反爬我们链接内容的行为，实现反反爬方案,如果对方相同ip + ua 反复抓取我们内容，根据阀值，将这些ip进入黑名单，以后返回混淆内容给他们。
对没项目定时每10分钟分析一次数据，记入ip黑名单
2011/10/28 by ziv
*/
require_once dirname(__FILE__).'/db/db.class.php';
require_once dirname(__FILE__).'/process.class.php';
require_once dirname(__FILE__).'/ip/ip.class.php'; 
require_once dirname(__FILE__).'/string/rand.string.php'; 

class statistic_blacklist
{	
	//对没项目定时每1分钟分析一次数据，记入ip黑名单
	static function in($k)
	{
		if(!$k) return false;

		$times = 5; //相同ua，相同ip > 3 次进
		if(!cm_process::runing('stasticcklisting3rv5'.$k,60)) return false;  

		$db = cm_db::get_db(cm_db_config::get_ini('comm','master'));

		$select = $db->select();
		$select->from('user_click_log as a','DISTINCT(a.ip)'); 
		$select->joinLeft('user_click_black_list as b','a.ip = b.ip',''); 
		$select->where('a.key1=? and b.ip is null',intval($k)); 
		$select->group(array('a.ip','a.browser')); 
		$select->having('count(*) > ?', $times);
		$sql = $select->__toString(); //echo $sql;exit;
		$r=$db->fetchAll($sql);
		//print_R($r);exit; 
		foreach($r as $k1=>$i)
		{
			$a=null;
			$a['create_time']=time();
			$a['ip']=$i['ip'];
			$db->insert('user_click_black_list',$a);
		} 

		return true;
	} 

	//如果ip在黑名单内，则返回随机混淆内容
	static function out($k)
	{
		if(!$k) return false;
		$ip=ip_class::get_ip();
		$a=self::get_list_cache();
		if(!is_array($a)) return false;
		foreach($a as $k=>$i)
		{
			if($i['ip']==$ip) 
			{
				$c=rand_string_s::rand_get_con(500,100);
				return $c;
			}
		}
		return false; 
	} 
	
	static function get_list_cache()
	{
		$expire=600;
		$key = md5('ststicblligetlstchev2');
		$cache = new temp_cache;
		$ret = $cache->get($key);
		if (empty($ret)){
			$ret=self::get_list();
			$cache->saveOrUpdate($key,$ret,$expire);
		}
		$cache->close();
		return $ret;
	}

	static function get_list()
	{
		$db = cm_db::get_db(cm_db_config::get_ini('comm','master'));
		$select = $db->select();
		$select->from('user_click_black_list', 'ip');
		$sql = $select->__toString();//echo $sql;exit;
		$r=$db->fetchAll($sql);
		return $r;
	}

}//end class 