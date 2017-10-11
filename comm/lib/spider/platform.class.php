<?php
/*
	by sapphirel 2011-7-11
*/ 
set_time_limit(0); 
if(!defined('COMM_PATH')) { 
	define('COMM_PATH', dirname(__FILE__).'/../../');//共用库目录
}

require_once COMM_PATH . '/lib/db/db.class.php';
class duap_platform 
{
	static $error;
	static function get_list_platform($a=array()){   
		$table = 'platform';
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm'));  
		$select = $db->select();
		$select->from($table, '*');
		$sql = $select->__toString();//echo $sql;exit;
		$r=$db->fetchAll($sql);
		return $r;
	}

	static function insert_platform($a){
		if(!is_array($a)) return false;
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm','master')); 
		$table = 'platform';
		try{
			$r=$db->insert($table, $a);
			$last_insert_id = $db->lastInsertId();
			return $last_insert_id;
		}catch(Exception $e){} 
	}

	static function update_platform($id,$name){
		if(!$name){self::$error='参数错误';return false;}; 
		if(!is_numeric($id)) return false;
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm','master')); 
		$table = 'platform';
		$set = array('name'=>$name);
		$where = $db->quoteInto('id = ?', $id); 
		return $db->update($table,$set,$where);
	}

	static function count_platform($pt){   
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_duap_single'));
		$select = $db->select();
		$select->from('reach_accounts_tsina', 'count(*)');
		$select->where('pt = ?',$pt);
		$sql = $select->__toString();
		$r=$db->fetchOne($sql);
		return $r;
	}

	static function count_platform_state($pt){   
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_duap_single'));
		$select = $db->select();
		$select->from('reach_accounts_tsina', 'count(*)');
		$select->where('pt = ?',$pt);
		$select->where('state = ?','1');
		$sql = $select->__toString();//print_r($sql);exit;
		$r=$db->fetchOne($sql);
		return $r;
	}

	static function count_platform_state_f($pt){   
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_duap_single'));
		$select = $db->select();
		$select->from('reach_accounts_tsina', 'count(*)');
		$select->where('pt = ?',$pt);
		$select->where('state = ?','9');
		$sql = $select->__toString();//print_r($sql);exit;
		$r=$db->fetchOne($sql);
		return $r;
	}

	

	static function count_user($pt){   
		$db =cm_db::get_db(cm_db_config::get_ini('user'));
		$select = $db->select();
		$select->from('user', 'count(*)');
		$select->where('pt = ?',$pt);
		$sql = $select->__toString();
		$r=$db->fetchOne($sql);
		return $r;
	}

	
	static function name_platform($name){   
		$key = md5('duapplatformnameplatform'.$name);
		$expire = 60*60;
		$cache = new temp_cache; 
		$info = $cache->get($key); 
		if (!$info){ 
			$info = self::name_platform_no_cache($name);
			$cache->saveOrUpdate($key,$info,$expire);
		} 
		$cache->close();
		if($info) return $info; else return false;
	} 

	static function name_platform_no_cache($name){    
		$table = 'platform';
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm'));  
		$select = $db->select();
		$select->from($table, 'id');
		$select->where('name = ?',$name);
		$sql = $select->__toString();
		$r=$db->fetchOne($sql);
		return $r;
	}

	static function get_platform_name($id){   
		$key = md5('get_platform_name'.$id);
		$expire = 60*60;
		$cache = new temp_cache; 
		$info = $cache->get($key); 
		if (!$info){ 
			$info = self::get_platform_name_no_cache($id);
			$cache->saveOrUpdate($key,$info,$expire);
		} 
		$cache->close();
		if($info) return $info; else return false;
	} 

	static function get_platform_name_no_cache($id){    
		$table = 'platform';
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm'));  
		$select = $db->select();
		$select->from($table, 'name');
		$select->where('id = ?',$id);
		$sql = $select->__toString();
		$r=$db->fetchOne($sql);
		return $r;
	}
/*
	static function count_all(){   
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm'));
		$select = $db->select();
		$select->from('reach_accounts_tsina', 'count(*)'); 
		$sql = $select->__toString();
		$r=$db->fetchOne($sql);
		return $r;
	}

	static function count_user_all(){   
		$db =cm_db::get_db(cm_db_config::get_ini('user'));
		$select = $db->select();
		$select->from('user', 'count(*)');
		$sql = $select->__toString();
		$r=$db->fetchOne($sql);
		return $r;
	}

	*/
} 
