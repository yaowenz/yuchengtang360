<?php
/*
	管路员用户相关	  
	by zx 2010-5-17
*/ 
require_once LIB_PATH . '/db.class.php';   

class comm_admin_user 
{ 
	//登录
	static function login($un,$up){  
		if(!$un || !$up) return false;

		$db = db_o::get_single();  
		//print_r($db);exit;
		$select = $db->select();
		$select	->from('admin_main', '*')
				->where('user_name = ?', $un)
				->where('user_pass = ?', md5($up))
				->limit(1); 
		
		$sql = $select->__toString();     //print_R($sql);exit; 
		$rt=$db->fetchRow($sql);				//print_R($rt);exit; 
		if($rt)
		{ 		
			$_SESSION['cm_comm_admin_info']['user_name'] = $rt['user_name']; 
			$_SESSION['cm_comm_admin_info']['acl'] = $rt['acl']; 
			$_SESSION['cm_comm_admin_info']['desc'] = $rt['desc']; 
			$_SESSION['cm_comm_admin_info']['id'] = $rt['id']; 
			return true; 
		}
		return false;
	}

	//登出
	static function logout(){ 
		$_SESSION['cm_comm_admin_info']=null;
		return true;
	}
	
	//取角色权限 
	static function get_acls(){  
		$db = db_o::get_single();  
		 
		$select = $db->select();
		$select	->from('admin_acl', 'acl'); 
		$sql = $select->__toString();     
		return $db->fetchAll($sql); 
	}
	
	//判断登录用户是否是管路员，如果是，返回管路员权限 
	static function get_acl($uid){ 
		if(!$uid) return false;
		$db = db_o::get_single();  
		 
		$select = $db->select();
		$select	->from('admin_main', 'acl')
				->where('uid = ?', $uid)
				->limit(1); 
		$sql = $select->__toString();     
		return $db->fetchOne($sql); 
	}
	
	//判断用户名是否存在，存在的话，增加为管理员用户 
	static function add($a){ 
		if(!$a) return false;
		
		$db = db_o::get_single();   
		 
		$select = $db->select();
		$select	->from('admin_main', 'id')
				->where('user_name = ?', $a['user_name']);
		$sql = $select->__toString();     
		$r1 = $db->fetchOne($sql); 
		
		if($r1) return 'have';//已存在该管理员 
		else
		{
			$db = db_o::get_single('master');

			$a['inputdate'] = time();
			$a['user_pass'] = md5($a['user_pass']);
			
			// 插入数据的数据表
			$table = 'admin_main';

			// i插入数据行并返回行数
			$rows_affected = $db->insert($table, $a);
			$last_insert_id = $db->lastInsertId();
		}
		return $last_insert_id; 
	}

	static function update($a){
		if(!$a) return false;
		if($a['user_pass']) $a['user_pass'] = md5($a['user_pass']);
		$db = db_o::get_single('master');
		$table = 'admin_main';
		$where = $db->quoteInto('id = ? ', $a['id']); 
		$rows_affected = $s->update($table, $a, $where);
		return $rows_affected;    
	}

	//获取管理员列表
	static function get_list(){ 
		$db = db_o::get_single(); 
		$select = $db->select();
		$select	->from('admin_main', '*');  
		$select->order(array('acl','user_name'));   
		$sql = $select->__toString();    
		$r1 = $db->fetchAll($sql); 	
		return $r1; 
	}

	//获取管理员
	static function get_admin($id){ 
		if(!$id) return false;
		$db = db_o::get_single(); 
		$select = $db->select();
		$select	->from('admin_main', '*')
				->where('id = ?', $id);
		$sql = $select->__toString();      
		$r1 = $db->fetchRow($sql); 	 
		return $r1; 
	}
	
	//删除管理员
	static function del($uid){ 
		if(!$uid) return false;
		$db = db_o::get_single('master');
		$table = 'admin_main';
		$where = $db->quoteInto('id = ?', $uid); 
		$rows_affected = $db->delete($table, $where);
		return $rows_affected; 
	}
	
	//修改密码
	static function pw_edit($uid,$op,$np){ 
		if(!$uid || !$op || !$np) return false;
		$db = db_o::get_single('master');
		$table = 'admin_main';
		$where = $db->quoteInto('id = ? ', $uid); 
		$where .= $db->quoteInto('and user_pass = ?', md5($op));  
		$a['user_pass'] = md5($np); 
		$rows_affected = $db->update($table, $a, $where);
		return $rows_affected;  
	}
 
	//取主持人信息
	static function get_zcr_info(){ 
		$db = db_o::get_single(); 
		$select = $db->select();
		$select->from('admin_main', '*');  
		$select->where('user_name = ?', '主持人');
		$sql = $select->__toString();
		$zcr = $db->fetchRow($sql); 
		$db = null;
		if(!$zcr) return false;
		else return $zcr;
	}
	
} 
   