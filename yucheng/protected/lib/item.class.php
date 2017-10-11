<?php
/*
	2016/2/24
*/ 
require_once LIB_PATH . '/db.class.php';   

class yc_item 
{
	function add($a){ 
		if(!$a) return false;
		
		$db = db_o::get_single('master');
		
		// 插入数据的数据表
		$table = 'yc_item';
		$a['create_time']=time();
		// i插入数据行并返回行数
		$rows_affected = $db->insert($table, $a);
		$last_insert_id = $db->lastInsertId();
	
		return $last_insert_id; 
	}

	function update($a){
		if(!$a) return false;
		$db = db_o::get_single('master');
		$table = 'yc_item';
		$where = $db->quoteInto('id = ? ', $a['id']); 
		$rows_affected = $db->update($table, $a, $where);
		return $rows_affected;
	}

	function get_list($a){
		$db = db_o::get_single(); 
		$select = $db->select();
		$select->from('yc_item', '*');
		if($a['id_in'])
		{
			$select->where('id IN (?)',$a['id_in']);
		}
		if($a['niandai_in'])
		{
			$select->where('niandai IN (?)',$a['niandai_in']);
		}
		
		if($a['state']==">0")
		{
			$select->where('state > ?', 0);
		}
		else if($a['state']==">=0")
		{
			$select->where('state >= ?', 0);
		}
		else if($a['state'] || $a['state']===0)
		{
			$select->where('state = ?', $a['state']);
		}

		$select->order('id desc');
		$select->limit($limit[1],$limit[0]);
		$sql = $select->__toString(); //die($sql);
		$r1 = $db->fetchAll($sql); 	
		return $r1; 
	}

	function get_item($id){ 
		if(!$id) return false;
		$db = db_o::get_single(); 
		$select = $db->select();
		$select	->from('yc_item', '*')
				->where('id = ?', $id);

		if($a['state']==">0")
		{
			$select->where('state > ?', 0);
		}
		else if($a['state']==">=0")
		{
			$select->where('state >= ?', 0);
		}
		else if($a['state'] || $a['state']===0)
		{
			$select->where('state = ?', $a['state']);
		}

		$sql = $select->__toString(); //die($sql);   
		$r1 = $db->fetchRow($sql);
		return $r1; 
	}

	function del($uid){ 
		if(!$uid) return false;
		$db = db_o::get_single('master');
		$table = 'yc_item';
		$where = $db->quoteInto('id = ?', $uid); 
		$rows_affected = $db->delete($table, $where);
		return $rows_affected;
	}

}//end class