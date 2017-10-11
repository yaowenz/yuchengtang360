<?php
/*
DUAP用户相关处理类
by jay 2011-5-4
*/ 

require_once dirname(__FILE__).'/../db/db.class.php';
class duap_user_pro{
	static $user_pro = 'user_pro';
	static $user_keyword_detail = 'user_keyword_detail';
	static $db_name = 'user';

	static function add_user_pro($row){
		$kw_row = array();
		$kw_row['pro_id'] = $row['pro_id'];
		$kw_row['uid'] = $row['uid'];

		$detail_row = array();
		$detail_row['content'] = $row['content'];
		$detail_row['from_plug'] = $row['from_plug'];

		$keyword = self::get_user_pro($kw_row,1,1); 
		if(is_array($keyword[0]) && !empty($keyword[0])){
			$kw = $keyword[0];
			if(!isset($row['content']) && !isset($row['from'])){
				return 0;
			}
			
			/* ziv 成本太高
			//判断详细信息是否存在
			$d_ary = array();
			$d_ary['param'] = 'count';
			$d_ary['uk_id'] = $kw['id'];
			$d_ary['content'] = $row['content'];
			$d_ary['from_plug'] = $row['from_plug'];
			$d_kw = self::get_user_keyword_detail($d_ary);
			if($d_kw > 0){
				return $kw['id'];
			}
			*/
			
			//保存详细信息
			$detail_row['uk_id'] = $kw['id'];
			$rt = self::add_user_keyword_detail($detail_row);
			if($rt > 0){
				$up_row = array();
				$up_row['uid'] = $kw['uid'];
				$up_row['pro_id'] = $kw['pro_id'];

				//获取数量
				/* 
				$d_ary = array();
				$d_ary['param'] = 'count';
				$d_ary['uk_id'] = $kw['id'];
				$count_t=self::get_user_keyword_detail($d_ary); 
				*/

				$up_row['p_count'] = $kw['p_count'] + 1;
				self::update_user_pro($up_row); 
				return $kw['id'];
			} else {
				return 0;
			}
		}
		
		$table = self::$user_pro;
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_pro','master'));
		try{
			$ret = @$db->insert($table,$kw_row);
			if($ret>0){
				$kw_id = $db->lastInsertId();
				$detail_row['uk_id'] = $kw_id;
				$rt = self::add_user_keyword_detail($detail_row);
				return $kw_id;
			} else {
				return 0;
			}
		}catch(Exception $e){return 0;}
	}

	static function update_user_pro($ary){
		if(!$ary['uid'] || !$ary['pro_id']) return false;
		$table = self::$user_pro;
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_pro','master'));

		$set = array();
		$set['p_count'] = $ary['p_count'];
		// where语句
		$where = $db->quoteInto('uid = ?',$ary['uid']);
		$where.= $db->quoteInto(' AND pro_id = ?', $ary['pro_id']);

		// 更新表数据,返回更新的行数
		$rt = $db->update($table, $set, $where);
		return $rt;
	}

	static function get_user_pro($ary,$page=null,$pagesize=1,$order=null){
		$table = self::$user_pro;
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_pro'));
		$select = $db->select();

		if($page==1 && $pagesize==1) $select->select_with='nolock';
		
		$param = '*';
		if(isset($ary['param'])){
			if($ary['param'] == 'count'){
				$param = 'count(*)';
			}else{
				$param = $ary['param'];
			}
		}
		$select->from($table,$param);

		if(isset($ary['pro_id'])){
			$select->where('pro_id = ?',$ary['pro_id']);
		}
		if(isset($ary['in_id'])){
			$select->where('pro_id in(?)',$ary['in_id']);
		}

		if(isset($ary['uid'])){
			$select->where('uid = ?',$ary['uid']);
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

	//添加用户属性详细信息
	static function add_user_keyword_detail($row){
		$table = self::$user_keyword_detail;
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_pro','master'));
		try{
			$ret = @$db->insert($table,$row);
			if($ret>0){
				return $db->lastInsertId();
			} else {
				return 0;
			}
		}catch(Exception $e){return 0;}
	}
	
	//获取用户属性详细信息列表
	static function get_user_keyword_detail($ary,$page=null,$pagesize=1,$order=null){
		$table = self::$user_keyword_detail;
		$table_1 = self::$user_pro;
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_pro')); 
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
		
		if($ary['distinct']) $select->distinct();  

		if(isset($ary['uk_id'])){
			$select->where('uk_id = ?',$ary['uk_id']);
		}

		if(isset($ary['content'])){
			$select->where('content = ?',$ary['content']);
		}
		if(isset($ary['from_plug'])){
			$select->where('from_plug = ?',$ary['from_plug']);
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
