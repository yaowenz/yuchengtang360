<?php
/*
	2014/8/21
*/ 
require_once LIB_PATH . '/db.class.php';   

class yc_team 
{
	static function add($a){ 
		if(!$a) return false;
		
		$db = db_o::get_single('master');
		
		// 插入数据的数据表
		$table = 'yc_team_index';

		// i插入数据行并返回行数
		$rows_affected = $db->insert($table, $a);
		$last_insert_id = $db->lastInsertId();
	
		return $last_insert_id; 
	}

	static function update($a){
		if(!$a) return false;
		$db = db_o::get_single('master');
		$table = 'yc_team_index';
		$where = $db->quoteInto('id = ? ', $a['id']); 
		$rows_affected = $db->update($table, $a, $where);
		return $rows_affected;
	}

	static function get_list(){ 
		$db = db_o::get_single(); 
		$select = $db->select();
		$select	->from('yc_team_index', '*');  
		$select->order('e_name');   
		$sql = $select->__toString();    
		$r1 = $db->fetchAll($sql); 	
		return $r1; 
	}

	static function get_team($id){ 
		if(!$id) return false;
		$db = db_o::get_single(); 
		$select = $db->select();
		$select	->from('yc_team_index', '*')
				->where('id = ?', $id);
		$sql = $select->__toString();      
		$r1 = $db->fetchRow($sql); 	 
		return $r1; 
	}

	static function del($uid){ 
		if(!$uid) return false;
		$db = db_o::get_single('master');
		$table = 'yc_team_index';
		$where = $db->quoteInto('id = ?', $uid); 
		$rows_affected = $db->delete($table, $where);
		return $rows_affected;
	}

}//end class