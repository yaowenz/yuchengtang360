<?php
//163 注册相关类  2011/12/7 by ziv
require_once dirname(__FILE__).'/db/db.class.php';
class edm_reg_163_class 
{
	public $error = null;
	public $db; 

	function __construct()
	{
		$this->db = cm_db::get_db(cm_db_config::get_ini('edm_send_account','master'));
	}

	//取一用户名注册163 mail
	public function get_one_account()
	{
		$key = 'dmrg1etoeacntitrv1';
		if(!cm_process::runing($key,5)){
			echo ' --- get_one_account_ing ! ---> '; 
			$this->error='get_one_account_ing';
			return false; 
		} 
		$select = $this->db->select(); 
		$select->from('edm_mails_163_not_exist','*');
		$select->where('reg_state is null',''); 
		$select->order('id');
		$select->limit(1);
		$sql = $select->__toString();	 

		$rt=$this->db->fetchRow($sql);
		if(!$rt)
		{
			echo ' --- none_163_not_exist  ---> ';
			$this->error='none_163_not_exist';
			return false; 
		} 
		$a=null;
		$a['reg_state']=1;
		$where = $this->db->quoteInto('id = ?', $rt['id']); 
		$this->db->update('edm_mails_163_not_exist', $a, $where);
		cm_process::done($key);  
		return $rt; 
	}
	
	//当失败后，回滚之前取的账户 reg_state = null
	public function rollback_account($a)
	{
		if(!$a || !is_numeric($a['id']))
		{
			echo ' --- rollback_account_error  ---> ';
			$this->error='rollback_account_error';
			return false; 
		}
		$a1=null;
		$a1['reg_state']=null;
		$where = $this->db->quoteInto('id = ?', intval($a['id'])); 
		return $this->db->update('edm_mails_163_not_exist', $a1, $where); 
	}
	
	//说明这个用户名注册的时刻 已经被别人注册了，从edm_mails_163_not_exist 转移到 edm_mails_163 表
	public function not_exist_to_exist($a)
	{
		if(!$a || !is_numeric($a['id']))
		{
			echo ' --- not_exist_to_exist_error  ---> ';
			$this->error='not_exist_to_exist';
			return false; 
		}
		
		unset($a['reg_state']);

		$this->db->insert('edm_mails_163',$a); 
		
		$where = $this->db->quoteInto('id = ?', $a['id']);  
		$this->db->delete('edm_mails_163_not_exist', $where); 
	}


}//end class