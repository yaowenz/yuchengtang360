<?php
/*
	by sapphirel 2011-7-11
*/ 
require_once LIB_PATH . '/db.class.php';
class comm_platform
{
	static $error;
	static function get_list_platform($a=array()){   
		$table = 'platform';
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm_single'));  
		$select = $db->select();
		$select->from($table, '*');
		$sql = $select->__toString();//echo $sql;exit;
		$r=$db->fetchAll($sql);
		return $r;
	}

	static function insert_platform($a){
		if(!is_array($a)) return false;
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm_single','master')); 
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
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm_single','master')); 
		$table = 'platform';
		$set = array('name'=>$name);
		$where = $db->quoteInto('id = ?', $id); 
		return $db->update($table,$set,$where);
	}

	static function count_platform($pt){   
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm_single'));
		$select = $db->select();
		$select->from('reach_accounts_tsina', 'count(*)');
		$select->where('pt = ?',$pt);
		$sql = $select->__toString();
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
		$table = 'platform';
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm_single'));  
		$select = $db->select();
		$select->from($table, 'id');
		$select->where('name = ?',$name);
		$sql = $select->__toString();
		$r=$db->fetchOne($sql);
		return $r;
	}
/*
	static function count_all(){   
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm_single'));
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
