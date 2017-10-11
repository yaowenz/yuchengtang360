<?php
/*
DUAP用户相关处理类
by jay 2011-5-4
*/ 

require_once dirname(__FILE__).'/../db/db.class.php';
class comm_duap_user{
	static $duap_user = 'user';
	static $db_name = 'user';
//ziv 等待修改
	static function add_user($row){

		//先插入总索引表节点数据库 ncg_user_root 做唯一约束，利用 insert output 新特性取到保险的id,防止高并发时产生的错读
		$sql = "insert into user_root_index (db_id) output INSERTED.id values(".cm_db_config::$db_id.")"; 
		$db1 = cm_db::get_db_mssql_query(cm_db_config::get_ini('user_root','master')); 
		$rt1 = $db1->fetchRow($sql); 
		if(!$rt1) return false; //可能已存在这条
		 
		$row['id'] = $rt1['id']; 

		$table = self::$duap_user;
		$db = cm_db::get_db(cm_db_config::get_ini(self::$db_name,'master'));
		try{
			$ret = $db->insert($table,$row);
			if($ret>0){
				return $row['id']; 
			} else {
				return 0;
			}
		}catch(Exception $e){return 0;}
	}

	static function get_user($ary,$page=null,$pagesize=1,$order=null){
		$table = self::$duap_user;
		$db = cm_db::get_db(cm_db_config::get_ini(self::$db_name));
		$select = $db->select();
		
		$param = '*';
		if(isset($ary['param'])){
			if($ary['param'] == 'count'){
				$param = 'count(*)';
			}else{
				$param = $ary['param'];
			}
		}
		$select->from($table,$param);

		if(isset($ary['third_id'])){
			$select->where('third_id = ?',$ary['third_id']);
		}

		if(isset($ary['domain_id'])){
			$select->where('domain_id = ?',$ary['domain_id']);
		}

		if(!isset($ary['param']) || $ary['param'] != 'count'){
			if($page != null){
				$startMsg = ($page-1)*$pagesize;
				$select->limit($pagesize,$startMsg);
			}
			if($order != null){
				$select->order($order);
			}
		}

		$sql = $select->__toString();	//print_R($sql);exit;
		if(isset($ary['param']) && $ary['param'] == 'count'){
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
}
