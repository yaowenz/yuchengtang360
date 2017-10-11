<?php
//分享站域名管理类 by ziv 2011/3/8
require_once dirname(__FILE__).'/../db/db.class.php'; 
class duap_domain{

	 static function get_list_db(){
		$ar = cm_db_config::get_ini('user_root');
		$db = cm_db::get_db($ar);

		$select = $db->select();
		$select	->from('domain_index', '*');  
		$select->order(array('REVERSE(domain)','id desc'));   
		$sql = $select->__toString();    
		$r1 = $db->fetchAll($sql); 	
		return $r1; 
	}

	static function update($a){ 
		if(!$a) return false;
		$a['last_time']=time();

		$ar = cm_db_config::get_ini('user_root','master');
		$db = cm_db::get_db($ar);

		$table = 'domain_index';
		$where = $db->quoteInto('id = ? ', $a['id']); 
		unset($a['id']);
		$rows_affected = $db->update($table, $a, $where);
		return $rows_affected;    
	}

	static function insert($row){ 
		if(!$row) return false;
		$row['createtime'] = time();
		$row['last_time'] = time();

		$ar = cm_db_config::get_ini('user_root','master');
		$db = cm_db::get_db($ar);

		$table = 'domain_index';
		$ret = $db->insert($table, $row);
		if($ret>0){
			return $db->lastInsertId();
		}else{
			return 0;
		}
	}

	static function get_domain_name_from_cach($domain){
		$key = md5('get_domain_name_'.$domain);
		$expire = 60*60;
		$cache = new temp_cache; 
		$ret = $cache->get($key); 
		if (empty($ret)){ 
			$ret = self::get_domain_name($domain);
			$cache->saveOrUpdate($key,$ret,$expire);
		}
		$cache->close();
		if($ret) return $ret; else return false;
	}

	static function get_domain_name($domain){
		$ar = cm_db_config::get_ini('user_root');
		$db = cm_db::get_db($ar);

		$select = $db->select();
		$select->from('domain_index', array('id','domain','type','name'));
		$select->where('domain = ?',$domain);
		$sql = $select->__toString(); //echo $sql;exit;
		$r1 = $db->fetchRow($sql);
		return $r1;
	}

	static function get_domain_by_id_from_cach($id){
		if(!is_numeric($id)){
			return false;
		}
		$key = md5('get_domain_by_id_'.$id);
		$expire = 60*60;
		$cache = new temp_cache; 
		$ret = $cache->get($key); 
		if (empty($ret)){ 
			$ret = self::get_domain_by_id($id);
			$cache->saveOrUpdate($key,$ret,$expire);
		}
		$cache->close();
		if($ret) return $ret; else return false;
	}

	static function get_domain_by_id($id){
		$ar = cm_db_config::get_ini('user_root');
		$db = cm_db::get_db($ar);

		$select = $db->select();
		$select->from('domain_index', '*');
		$select->where('id = ?',$id);
		$sql = $select->__toString(); //echo $sql;exit;
		$r1 = $db->fetchRow($sql);
		return $r1;
	}


}//end class