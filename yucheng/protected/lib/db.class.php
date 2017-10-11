<?php
/*
	dbฯเนุ	
	by ziv 2011/4/25  
*/
require_once COMM_PATH . '/lib/db/db.class.php'; 
class db_o 
{ 
	static $db=null;
	/*
	static function get($t='slave')
	{
		$ar = cm_db_config::get_ini('ncg_comm_single',$t); 
		return cm_db::get_db($ar);
	}
	*/
	static function get_single($t='slave')
	{
		if($_SERVER['HTTP_HOST']==TEST_DOMAIN) $s='yucheng1';else $s='yucheng1';
		$ar = cm_db_config::get_ini($s,$t);
		return cm_db::get_db($ar);
	}
	
	static function get_single_new($t='slave')
	{
		if($_SERVER['HTTP_HOST']==TEST_DOMAIN) $s='yucheng1';else $s='yucheng1';
		$ar = cm_db_config::get_ini($s,$t); 
		$ar['time']=time();
		return cm_db::get_db($ar); 
	}

}