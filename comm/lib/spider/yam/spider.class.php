<?php
//yam spider 相关基类 by 2011/3/18
require_once dirname(__FILE__).'/../../db/db.class.php'; 
class spider_class{
	
	static function &instance() {
		static $instance;
		if(!$instance) {
				$instance = new spider_class();
		}
		return $instance;
	}

	function __construct(){
		$this->db=cm_db::get_db(cm_db_config::get_ini('ncg_yam','master'));
		$this->db_single=cm_db::get_db(cm_db_config::get_ini('ncg_yam_single','master')); 
	}

	function get_logs($page=0,$limits=100){
		$startMsg = ($page-1)*$limits;
		$select = $this->db_single->select()->from('spider_log', '*')->order('id desc')->limit($limits,$startMsg);
		$sql = $select->__toString(); //echo $sql;exit;
		$r = $this->db_single->fetchAll($sql); 
		return $r; 
	}

	function get_logs_count(){
		$select = $this->db_single->select()->from('spider_log', 'count(*)');
		$sql = $select->__toString(); //echo $sql;exit;
		$r = $this->db_single->fetchOne($sql); 
		return $r; 
	}


	/*function get_url_list($a){
		$limits=1000;
		$select = $this->db->select(); 
		$select->from('log_user_ref', '*');
		if(is_numeric($a['state'])) $select->where('state = ?', $a['state']);  
		if(is_numeric($a['no_state'])) $select->where('state != ?', $a['no_state']);  
		if(isset($a['no_spider_error'])) $select->where('spider_error != ?', $a['no_spider_error']);  
		if(isset($a['spider_error'])) $select->where('spider_error = ?', $a['spider_error']);   
		$select->order('id desc');
		$select->limit($limits); 
 
		$sql = $select->__toString();
		$r = $this->db->fetchAll($sql); 
		return $r; 
	}*/

	function get_url_list($a,$page=1,$limits=10){
		$startMsg = ($page-1)*$limits;
		$select = $this->db->select(); 
		$select->from('log_user_ref', '*');
		if(is_numeric($a['state'])) $select->where('state = ?', $a['state']);  
		if(is_numeric($a['no_state'])) $select->where('state != ?', $a['no_state']);  
		if(isset($a['no_spider_error'])) $select->where('spider_error != ?', $a['no_spider_error']);  
		if(isset($a['spider_error'])) $select->where('spider_error = ?', $a['spider_error']);   
		if(isset($a['ignore_reason']))$select->where('ignore_reason = ?', $a['ignore_reason']);
		$select->order('id desc');
		$select->limit($limits,$startMsg); 
		//echo $limits;exit;
		$sql = $select->__toString();
		//echo $sql;exit;
		$r = $this->db->fetchAll($sql);
		//print_r($r);exit;
		return $r; 
	}

	function get_url_list_count($a){
		$select = $this->db->select(); 
		$select->from('log_user_ref', 'count(*)');
		if(is_numeric($a['state'])) $select->where('state = ?', $a['state']);  
		if(is_numeric($a['no_state'])) $select->where('state != ?', $a['no_state']);  
		if(isset($a['no_spider_error'])) $select->where('spider_error != ?', $a['no_spider_error']);  
		if(isset($a['spider_error'])) $select->where('spider_error = ?', $a['spider_error']);   
		$select->order('id desc');
		$sql = $select->__toString();
		$r = $this->db->fetchOne($sql);
		return $r; 
	}

	function get_url_by_id($id){
		if(!is_numeric($id)) return false;
		$select = $this->db->select();
		$select->from('log_user_ref', '*');
		$select->where('id = ?', $id);  
 
		$sql = $select->__toString();
		$r = $this->db->fetchRow($sql); 
		return $r;  
	}


	function update_url_list($a){  
		if(!$a) return false;
		if(!is_numeric($a['id'])) return false;
		if(is_numeric($a['state']))
		{
			$t1=$this->get_url_by_id($a['id']);
			if($t1['state']!=$a['state'])
			{
				$a['from_state']=$t1['state'];
			}
		}

		$a['last_time'] = time(); 
		$table = 'log_user_ref';
		$where = $this->db->quoteInto('id = ? ', $a['id']); 
		$rows_affected = $this->db->update($table, $a, $where);
		return $rows_affected;    
	}

 
}//end class
?>