<?php
/*
DUAP用户相关处理类
by jay 2011-5-4
*/ 

require_once dirname(__FILE__).'/../db/db.class.php';
class duap_app_index{
	static $app_index = 'app_index';
	static $db_name = 'user_root'; 

	static function add_app_index($row){ 
		if(empty($row) || !isset($row['app_index'])){
			return false;
		}

		$table = self::$app_index;
		$db = cm_db::get_db(cm_db_config::get_ini(self::$db_name,'master'));
		try{
			$ret = $db->insert($table,$row);
			if($ret>0){
				return $db->lastInsertId();
			} else {
				return 0;
			}
		}catch(Exception $e){return 0;}
	}

	static function update_app_index($ary){
		$table = self::$app_index;
		$db = cm_db::get_db(cm_db_config::get_ini(self::$db_name,'master'));

		$set = array();
		if(isset($ary['note'])){
			$set['note'] = $ary['note'];
		}

		if(empty($set)){
			return false;
		}
		// where语句
		$where = $db->quoteInto('id = ?',$ary['id']);

		// 更新表数据,返回更新的行数
		$rt = $db->update($table, $set, $where);
	}

	static function get_app_index($param){
		$table = self::$app_index;
		$db = cm_db::get_db(cm_db_config::get_ini(self::$db_name));
		$select = $db->select();
		$select->from($table,'id');

		if(is_array($param)){
			if(isset($param['app_index'])){
				$select->where('app_index = ?',$param['app_index']);
			}
			if(isset($param['note'])){
				$select->where('note = ?',$param['note']);
			}
		} else {
			$select->where('app_index = ?',$param);
		}

		$sql = $select->__toString();	//print_R($sql);exit;
		$ret = $db->fetchOne($sql);
		if($ret > 0){
			return $ret;
		} else {
			return 0;
		}
	}
}
