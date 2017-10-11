<?php
//sina微博 账户池管理 by ziv 2011/6/13

require_once dirname(__FILE__).'/../db/db.class.php';
class duap_account_tsina
{	
	static $key_account_list='key_account_list_v2'; 
	static $tag_account_list='tag_account_list_v2';
	static $accounts_table = 'reach_accounts_tsina';
	static $accounts_class_table = 'reach_accounts_tsina_classify';

	static function get_account_list_from_cache($a=array(),$page=null,$pagesize=10,$order=null,$limit=null){
		require_once dirname(__FILE__).'/../cache/cache.class.php';
		$expire = 1800;
		$t = serialize($a).$page.$pagesize.$order.$limit;
		$key = md5(self::$key_account_list.$t);
		$tag = self::$tag_account_list; 
		$cache = new temp_cache; 
		$info = $cache->get($key); 
		if (empty($info)){  
			$info = self::get_account_list_from_db($a,$page,$pagesize,$order,$limit);
			$cache->saveByTag($key,$info,$tag,$expire); 
		} 
		$cache->close();
		if($info) return $info; else return false; 
	} 

	static function get_account_list_from_db($a=array(),$page=null,$pagesize=10,$order=null,$limit=null){
		//var_dump($a);//exit;
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single'));
		$select = $db->select();
		$select->from('reach_accounts_tsina', '*');
		$select->joinLeft('reach_accounts_tsina_classify as b','reach_accounts_tsina.class_id = b.id','b.name'); 
		//$select->joinLeft('platform as p','reach_accounts_tsina.pt = p.id','p.name as pname');
		if(isset($a['state'])){
			$select->where('state = ?',$a['state']);
		}
		if($a['pt']){  
			$select->where('pt = ?',$a['pt']); 
		}

		if(isset($a['identifying'])){
			$select->where('identifying = ?',$a['identifying']);
		}

		if(isset($a['select_pt'])){ 
			$select->where('pt = ?',$a['select_pt']);
		}

		if(isset($a['than_last_time'])){
			$str = ' -'.$a['than_last_time'].' days';
			$last_time = strtotime($str);
			$select->where('last_time < ?',$last_time);
		}
		
		if(is_numeric($a['class_id'])){
			$select->where('class_id = ?',$a['class_id']);
		}

		if(isset($a['last_time'])){
			$select->where('last_time = ?',$a['last_time']);
		}

		if(isset($a['user_name'])){
			$select->where('user_name = ?',$a['user_name']);
		}

		if(isset($a['use_type'])){
			$select->where('use_type = ?',$a['use_type']);
		}

		if(isset($a['qid']) && is_numeric($a['qid'])){
			$select->where('qid = ?',$a['qid']);
		}

		if(isset($a['uid']) && is_numeric($a['uid'])){
			$select->where('uid = ?',$a['uid']);
		}
		
		if($a['name']){
			$select->where('b.name = ?',$a['name']);
		} 

		if($page != null){ 
			$startMsg = ($page-1)*$pagesize;
			$select->limit($pagesize,$startMsg);
		} 
					
		if($order != null){
			$select->order($order);
		}

		if($limit != null && is_numeric($limit)){
			$select->limit($limit); 
		}
		
		$sql = $select->__toString();	//print_R($sql);exit;     
		$ret=$db->fetchAll($sql);
		require_once COMM_PATH . '/lib/spider/platform.class.php'; 
		foreach($ret as $k=>$i)
		{
			$ret[$k]['pname']=duap_platform::get_platform_name($i['pt']);
		}
		return $ret;  
	} 

	
	static function update_account($a=array(),$where_ary = null,$clear_cache=false){
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single','master'));
		$table = 'reach_accounts_tsina';
		if(!$where_ary){
			if(!is_numeric($a['id'])) return false;
			$where = $db->quoteInto('id = ? ', $a['id']);
		} else {
			foreach($where_ary as $key=>$val){
				$where = $db->quoteInto("$key = ? ",$val);
			}
		}
		//print_r($a);print_r($where);print_r($where_ary);
		if($a['id']) unset($a['id']);

		$rows_affected = $db->update($table, $a, $where); 
		if($clear_cache){
			require_once dirname(__FILE__).'/../cache/cache.class.php';
			$tag = self::$tag_account_list; 
			$cache = new temp_cache; 
			$cache->removeTag($tag); 
			$cache->close();
		}

		return $rows_affected;     
	} 
	
	static function update_pwd($id,$new_pwd){
		if(!$new_pwd){self::$error='参数错误';return false;};
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single','master'));
		$table = 'reach_accounts_tsina';
		$set = array('pwd'=>$new_pwd);
		$where = $db->quoteInto('id = ?', $id); 
		return $db->update($table,$set,$where);
	}

	static function block_account($id,$error=null){
		if(!is_numeric($id)) return false;
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single','master'));
		$a['id']=$id;
		$a['last_time'] = time();
		$a['state']=9;
		$r=self::update_account($a,null,true);
		
		//记录被封log
		if($error){
			$ary['error'] = $error;
		}
		$ary['account_id'] = $id;
		$ary['time'] = time();
		self::insert_block_account_log($ary);
		return $r;     
	}
	
	static function insert_block_account_log($ary){
		$table= 'reach_accounts_block_log';
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single','master'));
		$ret = $db->insert($table, $ary);
	}
	
	static function unblock_account($id){
		if(!is_numeric($id)) return false;
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single','master'));
		$a['id']=$id;
		$a['state']=1;
		$r=self::update_account($a,null,true);
		return $r;     
	}

	static function get_account_one($ary = array(),$order = null){
		if(!is_numeric($ary['pt']) || !$ary['pt']) return false;
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single'));
		$select = $db->select();
		$select->from('reach_accounts_tsina', '*');
		$select->where('pt = ?', $ary['pt']);
		if($ary['class_id']){
			$select->where('class_id = ?', $ary['class_id']);
		}
		if($ary['state']){
			$select->where('state = ?', $ary['state']);
		}
		$select->limit(1);
		if($order){
			$select->order($order);
		}
		$sql = $select->__toString();
		return $db->fetchRow($sql);
	}

	/* 获取账号列表或总数 */
	static function get_account_count($ary = array()){
		$table= self::$accounts_table;
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single'));
		$select = $db->select();

		$param = 'count(*)';
		$select->from($table,$param);

		if(isset($ary['identifying'])){
			$select->join('reach_accounts_tsina_classify as b','reach_accounts_tsina.class_id = b.id',''); 
			$select->where('identifying = ?',$ary['identifying']);
		}

		if(isset($ary['class_id']) && is_numeric($ary['class_id'])){
			$select->where('class_id = ?',$ary['class_id']);
		}
		
		if(isset($ary['qid']) && is_numeric($ary['qid'])){
			$select->where('qid = ?',$ary['qid']);
		}
		
		if(isset($ary['select_pt'])){ 
			$select->where('pt = ?',$ary['select_pt']);
		}

		if(isset($ary['state'])){
			$select->where('state = ?',$ary['state']);
		}
		if(isset($ary['than_last_time'])){
			$str = ' -'.$ary['than_last_time'].' days';
			$last_time = strtotime($str);
			$select->where('last_time < ?',$last_time);
		}

		$sql = $select->__toString();	//print_R($sql);exit;
		$ret = $db->fetchOne($sql);
		if($ret > 0){
			return $ret;
		} else {
			return 0;
		}
	}

	/* 添加账号 */
	static function add_account($a=array()){
		//if(!is_numeric($a['uid'])){self::$error='参数错误';return false;}
		if(!$a['user_name']){self::$error='参数错误';return false;}
		if(!$a['pwd']){self::$error='参数错误';return false;}
		$table= self::$accounts_table;
		$db = db_o::get_single('master');    

		$ret = $db->insert($table, $a);

		if($ret>0){
			return $db->lastInsertId();
 		}else{
			return 0;
		}
	} 
	
	/* 删除账号 */
	static function del_account($a=array()){
		$db=db_o::get_single('master');
		$table= self::$accounts_table;
		$where=null;
		if(is_numeric($a['id'])) $where = $db->quoteInto('id = ?', $a['id']);
		if(!$where) return false;
		return $db->delete($table, $where);
	}

	/*
	 * 账号分类操作方法
	 */
	static function get_account_class_id_by_name($name){
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single'));
		$select = $db->select();
		$select->from('reach_accounts_tsina_classify', 'id');
		$select->where('name = ?',$name);
		$sql = $select->__toString();	//print_R($sql);exit; 
		$ret=$db->fetchOne($sql);
		return $ret;  
	}

	/* 查找标识类 */
	static function get_account_class_name_for_project($a){
		$table= self::$accounts_table;
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single'));
		$select = $db->select();
		$select->distinct()->from($table.' as a', '');
		$select->join(self::$accounts_class_table . ' as b', 'a.class_id = b.id',array('id','name'));
		if(isset($a['identifying'])){
			$select->where('b.identifying = ?', $a['identifying']);
		}
		if(isset($a['pt'])){
			$select->where('a.pt = ?', $a['pt']);
		}
		$select->where('a.qid = ?', '0');
		$select->where('a.state = ?', '1');
		$sql = $select->__toString();	//print_R($sql);exit; 
		$ret=$db->fetchAll($sql);
		return $ret;
	}

	/* 获取账号分类名 */
	static function get_account_class_name($id){
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single'));
		$select = $db->select();
		$select->from(self::$accounts_class_table , 'name');
		$select->where('id = ?',$id);
		$sql = $select->__toString();	//print_R($sql);exit; 
		$ret=$db->fetchOne($sql);
		return $ret;
	}

	//获取所有的账号分类
	static function get_account_class(){
		$db = db_o::get_single();
		$select = $db->select();
		$select->from(self::$accounts_class_table , '*');
		$select->order('name');
		$sql = $select->__toString();	//print_R($sql);exit; 
		$ret=$db->fetchAll($sql);
		return $ret;  
	}

	//按分类表识获取账号分类列表
	static function get_account_class_ident($a){
		$db = db_o::get_single();
		$select = $db->select();
		$select->from(self::$accounts_class_table , '*');
		$select->order('name');
		if(isset($a['identifying'])){
			$select->where('identifying = ?', $a[identifying]);
		}
		$sql = $select->__toString();	//print_R($sql);exit; 
		$ret=$db->fetchAll($sql);
		return $ret;  
	}

	//获取账号分类的总数
	static function get_account_class_count(){
		$db = db_o::get_single();
		$select = $db->select();
		$select->from(self::$accounts_class_table , 'count(*)');
		$sql = $select->__toString();	//print_R($sql);exit; 
		$ret=$db->fetchOne($sql);
		return $ret;  
	}

	/* 添加一个账号分类 */
	static function add_account_class($a=array()){
		if(!$a['name']){self::$error='参数错误';return false;} 
		$a['name']=htmlspecialchars($a['name'], ENT_QUOTES);
		$db = db_o::get_single('master');
		$ret = $db->insert(self::$accounts_class_table , $a); 
		if($ret>0){
			return $db->lastInsertId();
 		}else{
			return 0;
		}
	} 
	
	/* 删除一个账号分类 */
	static function del_account_class($a=array()){
		if(!is_numeric($a['id'])) return false;
		if($a['id']==1) {self::$error='对不起,此类是保留类,不能删除';return false;}  
		$a1['class_id']=$a['id'];
		$r=self::get_account_list($a1,null,null,null,1);
		if($r) {self::$error='请先删除所属该分类的所有账户';return false;}  
		$db=db_o::get_single('master');
		$table= self::$accounts_class_table; 
		$where = $db->quoteInto('id !=1 and id = ?', $a['id']); 
		return $db->delete($table, $where);  
	} 

	/* 修改一个账号分类 */
	static function update_account_class($a=array()){
		if(!is_numeric($a['id'])) return false;
		$db = db_o::get_single('master');
		$table = self::$accounts_class_table;
		$where = $db->quoteInto('id = ? ', $a['id']); 
		unset($a['id']);
		$rows_affected = $db->update($table, $a, $where);
		return $rows_affected;    
	}

	/* 修改密码 */
	static function old_pwd($id){
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single'));
		$select = $db->select();
		$select->from('reach_accounts_tsina', 'pwd');
		$select->where('id = ?',$id);
		$sql = $select->__toString();	//print_R($sql);exit;     
		$ret=$db->fetchAll($sql);
		return $ret;
	}

	/* 获取账号log列表和总数 */
	static function get_account_loglist($ary = array(),$para = null,$page=null,$pagesize=10,$order=null){
		$table= 'reach_accounts_block_log';
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single'));
		$select = $db->select();
		
		if($para){
			$param = '*';
			$select->joinLeft('reach_accounts_tsina as b','reach_accounts_block_log.account_id = b.id',array('b.user_name','b.pwd')); 
		}else{
			$param = 'count(*)';
		}

		$select->from($table,$param);
	
		if($order){
			$select->order($order);
		}

		if($page){ 
			$startMsg = ($page-1)*$pagesize;
			$select->limit($pagesize,$startMsg);
		} 
		

		$sql = $select->__toString();	//print_R($sql);exit;     
		if($param == '*'){
			$ret=$db->fetchAll($sql);
		}else{
			$ret=$db->fetchOne($sql);
		}
		
		if($ret > 0){
			return $ret;
		} else {
			return 0;
		}
	}
	
	//更新转发微博时间
	static function update_retransmit_time($id){
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single','master'));
		$table = 'reach_accounts_tsina';
		$set = array('last_retransmit_time'=>time());
		$where = $db->quoteInto('id = ?', $id); 
		return $db->update($table,$set,$where);
	}


	//pp时光机账户管理
	public function get_account_pplist($ary = array(),$param=null,$page=null,$pagesize=10,$order=null){
		$table= 'reach_account_pp';
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single'));
		$select = $db->select();
		
		if($param){
			$param = '*';
		}else{
			$param = 'count(*)';
		}
		
		if($ary['id']){  
			$select->where('id = ?',$ary['id']); 
		}
		if($ary['aid']){  
			$select->where('aid = ?',$ary['aid']); 
		}
		if($ary['pp_uname']){  
			$select->where('pp_uname = ?',$ary['pp_uname']); 
		}
		if($ary['pp_pwd']){  
			$select->where('pp_pwd = ?',$ary['pp_pwd']); 
		}
		if($ary['pp_email']){  
			$select->where('pp_email = ?',$ary['pp_email']); 
		}
		if(is_numeric($ary['state'])){  
			$select->where('state = ?',$ary['state']); 
		}

		$select->from($table,$param);
	
		if($order){
			$select->order($order);
		}

		if($page){ 
			$startMsg = ($page-1)*$pagesize;
			$select->limit($pagesize,$startMsg);
		} 
		

		$sql = $select->__toString();	//print_R($sql);//exit;     
		if($param == '*'){
			$ret=$db->fetchAll($sql);
		}else{
			$ret=$db->fetchOne($sql);
		}
		
		if($ret > 0){
			return $ret;
		} else {
			return 0;
		}
	}

	//pp时光机修改密码及状态
	static function get_account_ppupdate($a=array()){
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single','master'));
		$table= 'reach_account_pp';
		$where = $db->quoteInto('id = ? ', $a['id']); 
		$set = array();
		if($a['pp_uname']){
			$set['pp_uname'] = $a['pp_uname'];
		}
		if($a['pp_pwd']){
			$set['pp_pwd'] = $a['pp_pwd'];
		}
		if(is_numeric($a['state'])){
			$set['state'] = $a['state'];
		}
		$rows_affected = $db->update($table, $set, $where);
		return $rows_affected;    
	}

	/* 添加pp时光机账号 */
	static function get_account_ppadd($a=array()){
		$db = cm_db::get_db(cm_db_config::get_ini('ncg_duap_single','master'));
		$table= 'reach_account_pp';
		$data = array(
			'aid' => $a['aid'],
			'pp_uname'  => $a['pp_uname'],
			'pp_pwd' => $a['pp_pwd'],
			'pp_email'  => $a['pp_email'],
			'state' => $a['state'],
			'createtime' => time(),
		);
		$ret = $db->insert($table, $data);
		if($ret>0){
			return $db->lastInsertId();
 		}else{
			return 0;
		}
	}

	//查询库里tsina_user表总数
	static function get_tsina_user_count($table){
		$db = cm_db::get_db(cm_db_config::get_ini('user_tsina'));
		$select = $db->select();
		$select->from($table,'count(*)');
		$sql = $select->__toString();	//print_R($sql);//exit;     
		$ret=$db->fetchOne($sql);
		//print_r($ret);exit;
		return $ret;
	}

	/* accounts_table修改账号分类 */
	static function update_account_category($a=array()){
		if(!is_numeric($a['id'])) return false;
		$db = db_o::get_single('master');
		$table = self::$accounts_table;
		$where = $db->quoteInto('id = ? ', $a['id']); 
		unset($a['id']);
		$rows_affected = $db->update($table, $a, $where);
		return $rows_affected;    
	}
	
}//end class