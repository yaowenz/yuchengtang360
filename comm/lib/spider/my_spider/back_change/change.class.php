<?php
//duap 请求反馈变化情况log类
class duap_change_log{
	public $table_name = 'change_monitor';
	public $db_name = 'ncg_duap_single';

	//得到一个单例
	static private $instance = NULL;
	public static function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function add_change_log($row){
		$table = $this->table_name;
		$db = cm_db::get_db(cm_db_config::get_ini($this->db_name,'master'));
		$ret = $db->insert($table,$row);
		if($ret>0){
			return $db->lastInsertId();
		} else {
			return 0;
		}
	}

	static function back_list($a = array(),$page=null,$pagesize=1,$order=null){
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single'));
		$table = 'change_monitor';
		$select = $db->select();
		if($page==1 && $pagesize==1) $select->select_with='nolock';
		
		$param = '*';
		if(isset($a['param'])){
			if($a['param'] == 'count'){
				$param = 'count(*)';
			}else{
				$param = $a['param'];
			}
		}

		$select->from($table,$param);
		
		if(!isset($a['param']) || $a['param'] != 'count'){
			if($page != null){
				$startMsg = ($page-1)*$pagesize;
				$select->limit($pagesize,$startMsg);
			}
			if($order != null){
				$select->order($order);
			}
		}
		
		$sql = $select->__toString();	//print_R($sql);
		if(isset($a['param']) && $a['param'] == 'count'){
			$ret = $db->fetchOne($sql);
		}else{
			$ret = $db->fetchAll($sql);
		}

		if($ret > 0){
			return $ret;
		} else {
			return 0;
		}
	}

	static function back_xq($id){
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single'));
		$table = 'change_monitor';
		$select = $db->select();
		$select->from($table,'*');
		$select->where('id = ?',$id);
		$sql = $select->__toString();//print_r($sql);exit;
		$ret = $db->fetchAll($sql);
		return $ret;
	}

	static function back_update($a){
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single'));
		$table = 'change_monitor';
		$set = array();
		$set['state'] = $a['state']; 
		$where = $db->quoteInto('id = ?',$a['id']);
		$rows_affected = $db->update($table, $set, $where);
		if($rows_affected>0){
			return $rows_affected;
		} else {
			return 0;
		} 
	}
}
//end class
