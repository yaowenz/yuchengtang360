<?php

require_once LIB_PATH . '/db.class.php';

class yc_html_class
{
	public $db;

	public function __construct()
	{
		$this->db=db_o::get_single('master');
	}

	public function create_html($name)
	{
		if(!$name) return false;
		
		$a=null;
		$a['state']=-2;
		$a['update_time']=time();
		$this->db->insert('yc_html_main_index',$a);
		$last_insert_id = $this->db->lastInsertId();
		if(!$last_insert_id) return false;

		$a=null;
		$a['html_id']=$last_insert_id;
		$a['name']=trim($name);
		$a['sort']=1;
		$this->db->insert('yc_html_name',$a);
		
		return $last_insert_id;
	}

}//end class