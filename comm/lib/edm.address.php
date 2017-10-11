<?php
//edm 地址相关
require_once dirname(__FILE__).'/db/db.class.php';
require_once dirname(__FILE__).'/ip/ip.class.php'; 
class edm_address_class 
{
	function __construct()
	{
		$this->db = cm_db::get_db(cm_db_config::get_ini('comm','master'));
	}

	function insert_email_address_click_back_qq($mail,$key=null) 
	{
		if(!$mail) return false;
		$a['mail']=$mail;
		$a['ip']=ip_class::get_ip();
		$a['key']=intval($key);
		try{
			@$this->db->insert('email_address_click_back_qq',$a); 
		}catch(Exception $e){} 
		return true;  
	}
	
	function insert_email_address_click_back_active($mail,$key=null)
	{
		if(!$mail) return false;
		$a['mail']=$mail;
		$a['ip']=ip_class::get_ip(); 
		$a['key']=intval($key);
		try{
			@$this->db->insert('email_address_click_back_active',$a); 
		}catch(Exception $e){} 
		return true;  
	}
	
}//end class