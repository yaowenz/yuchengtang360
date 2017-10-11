<?php
/*
adsl 代理相关
by ziv 2011/6/17
*/

require_once dirname(__FILE__).'/../db/db.class.php';
require_once dirname(__FILE__).'/../cache/cache.class.php';

class proxy_adsl{
	
	static $port='4139'; //默认端口配置
	static $u_p='newcogs:ncg';
	static $error='';
	static $reason; //重启理由

	static $key_p_adsl_list='key_p_adsl_list_v2'; 
	static $tag_p_adsl_list='tag_p_adsl_list_v2'; 
	static $key_wait='prox_yladdwaitqueue3';
 
	static function get_proxy_webservice($key='')
	{
		$woshou='hwngj(@#gas24';
		$time=time();
		$url='http://comm.laawoo.com/webserver/get_adsl_proxy.php?t='.$time.'&k='.$key.'&w='.md5($woshou.$time);
		$ch = curl_init();
		$timeout = 5;
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$file_contents = curl_exec($ch);
		curl_close($ch);
		return json_decode($file_contents,true);
	}
	
	//根据key，从db里取出一条 http代理ip，如果没有返回 false 
	static function get_proxy($key,$name=null){
		if(!$key) return false;
		$a['have_ip']=true;
		$a['state']='0';  
		/*
		if($name) $a['source_name']=$name;
		else
		{	
			//如果是工作站模式，就不需要用adsl代理发curl
			require_once dirname(__FILE__).'/../tsina.class.php'; 
			$computername=strtolower($_SERVER['COMPUTERNAME']);
			if(t_sina_comm::not_need_adsl_proxy()) $a['source_name']=$computername;
		}
		*/
		//echo '-------source_name-----'.$a['source_name'].'------------'; 
		$t1 = self::get_list($a);
		if(!$t1) return false;
		foreach($t1 as $k=>$i) {
			$t1[$k]['p']='http://' . $i['ip'] . ':' . self::$port;
			$t1[$k]['port']=self::$port;
			$t1[$k]['region']='上海市';
			$t1[$k]['region_key']='上海市';
		}
		$hash = self::_time33Hash($key);
		$count=count($t1); 
		$rt = abs(fmod($hash, $count)); 

		//echo '-------get_proxy-----';print_R($t1[$rt]);echo '------------';  
 
		return $t1[$rt];  
	}

	static function get_list_from_cache($a=array()){
		require_once dirname(__FILE__).'/../cache/cache.class.php';
		$expire = 1;
		$t = serialize($a);
		$key = md5(self::$key_p_adsl_list.$t);
		$tag = self::$tag_p_adsl_list; 
		$cache = new temp_cache; 
		$info = $cache->get($key); 
		if (empty($info)){  
			$info = self::get_list($a);
			$cache->saveByTag($key,$info,$tag,$expire);
		} 
		$cache->close();
		if($info) return $info; else return false; 
	} 

	static function get_list($a=array()){   
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_proxy_adsl'));  
		$select = $db->select();
		$select	->from('proxy_adsl_using', '*'); 
		if(is_numeric($a['state'])){
			$select->where('state = ?',$a['state']);
		} 
		if($a['source_name']){ 
			$select->where('source_name = ?',$a['source_name']);
		} 
		//$select->where('source_name = ?','ziv');//临时添加的条件
		if($a['have_ip']){
			$select->where('ip != ? and ip is not null','');
		}  
		$sql = $select->__toString();     
		$r=$db->fetchAll($sql); 
		foreach($r as $k=>$i) 
		{
			$r[$k]['u']=self::$u_p;   
			$r[$k]['region']='上海市';
			$r[$k]['region_key']='上海市';
		}
		return $r; 
	}

	//获取数据库指定ID的IP字段
	static function get_adsl_ip($a){
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_proxy_adsl'));
		if(isset($a['id'])){
			$select=$db->select();
			$select->from('proxy_adsl_using','*');
			$select->where('id = ?',$a['id']);
			$sql=$select->__toString();
			//echo $sql;exit;
			$r= $db->fetchRow($sql);
		}else{
			$select=$db->select();
			$select->from('proxy_adsl_using','*');
			$select->where('source_name = ?',$a['source_name']);
			$sql=$select->__toString();
			$r= $db->fetchRow($sql);
		}
		return $r;
	}

	static function update_adsl_ip($id,$ip){
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_proxy_adsl','master'));
		$set = array (
			'ip' => $ip,
			'state' => 0
		);
		$table= 'proxy_adsl_using';
		$where= $db->quoteInto('id = ?',$id);
		$db->update($table,$set,$where);
	}
	
	static function update_proxy_adsl_using($a){
		if(!is_numeric($a['id'])) return false;
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_proxy_adsl','master'));
		$table= 'proxy_adsl_using';
		$where= $db->quoteInto('id = ?',$a['id']);
		unset($a['id']);
		$db->update($table,$a,$where); 
	}
	
	static function restart($id){
		if(!is_numeric($id)) return false;
		$a['id']=$id;
		$a['state']=1;
		self::update_proxy_adsl_using($a); 
		self::log_restart(); 
	}
	
	static function log_restart(){
		$a['create_time']=time();
		$a['reason']=self::$reason;
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_duap_single','master'));
		$table = 'log_adsl_restart';
		$row = $a;
		$rows_affected = $db->insert($table, $row);
		return true; 
	}
	
	static function insert_adsl_log($id,$o_ip,$n_ip,$time){
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_proxy_adsl','master'));
		$table = 'proxy_adsl_log';
		$row = array(
			'new_ip' => $n_ip,
			'old_ip' => $o_ip,
			'time' => $time,
			'adsl_id' => $id
		);
		$rows_affected = $db->insert($table, $row);
		$last_insert_id = $db->lastInsertId();
		return $last_insert_id; 
	}
	//第一次进入没有IP的时候
	static function insert_adsl_ip($ip,$uname){
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_proxy_adsl','master'));
		$table = 'proxy_adsl_using';
		$row = array(
			'ip' => $ip,
			'source_name' => $uname,
		);
		$rows_affected = $db->insert($table, $row);
		$last_insert_id = $db->lastInsertId();
		return $last_insert_id;
	}

	static function insert_adsl($a){
		if(!is_array($a)) return false;
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_proxy_adsl','master')); 
		$table = 'proxy_adsl_using';
		try{
			$r=$db->insert($table, $a);
			$last_insert_id = $db->lastInsertId();
			return $last_insert_id;
		}catch(Exception $e){} 
	}

	static function del_adsl($id){
		if(!is_numeric($id)) return false;
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_proxy_adsl','master')); 
		$table = 'proxy_adsl_using';
		$where = $db->quoteInto('id = ?', $id); 
		$rows_affected = $db->delete($table, $where);
		return $rows_affected; 
	}
	
	//进入等待重启的代理队列
	static function add_wait_queue($id){
		if(!is_numeric($id)) return false;
		$a['id']=$id;
		$a['reason']=self::$reason;
		$key=self::$key_wait;
		$time=86400;

		$cache = new temp_cache; 
		$info = $cache->get($key);

		$info[]=$a;
		$info=array_unique($info);
		if($info) $cache->saveOrUpdate($key,$info,$time);
	
		$cache->close();
		return true;
	}
	
	//重启队列
	static function restart_wait_queue(){
		require_once dirname(__FILE__).'/../process.class.php';
		if(!cm_process::runing('proxy_adslrestart_wait_queue2',60)) return false;
		$key=self::$key_wait;
		$time=86400;  

		$cache = new temp_cache; 
		$info = $cache->get($key);
		if($info && is_array($info)) 
		{	
			$info=array_unique($info); 
			foreach($info as $k=>$i)
			{
				self::$reason=$i['reason'];
				self::restart($i['id']);
			}
			$cache->delete($key);
		}
		$cache->close();
		return true;
	}
	
	static function get_wait_queue(){
		$key=self::$key_wait;
		
		$cache = new temp_cache; 
		$info = $cache->get($key);
		$cache->close();
		return $info; 
	}
	

	static function _time33Hash($str) {
        $hash = 0;
        $n = strlen($str);
        for ($i = 0; $i <$n; $i++) {
            $hash += ($hash <<5 ) + ord($str[$i]);
        }
        return $hash;
    }
	

}//end class