<?php
/*
	db操作
	by zx 2010-6-10 
*/
define('COMM_FRAME_PATH',dirname(__FILE__).'/../../../frame/');
ini_set("include_path",ini_get("include_path").PATH_SEPARATOR.COMM_FRAME_PATH);
require_once COMM_FRAME_PATH.'Zend/Db.php'; 
require_once dirname(__FILE__).'/../../../config/db.config.php';
 
class cm_db  
{   
	static $db=null; 

	static function get_db($ar) 
	{ 
		if($ar['pdoType']=='dblib') return self::get_db_mssql($ar);
		else if($ar['pdoType']=='sqlsrv') return self::get_db_sqlsrv($ar);
		else if($ar['pdoType']=='mssql') return self::get_db_mssql_win($ar);

		//$ar_s = md5(serialize($ar));
		//if(isset(self::$db[$ar_s]) && self::$db[$ar_s]) return self::$db[$ar_s];
		//else{
			$ar['persistent'] = true;
			$r = Zend_Db::factory('Pdo_Mysql', $ar);
			$r->query("SET NAMES 'utf8'"); 
			//self::$db[$ar_s]=$r; 
			return $r;
		//}
	}
	
	static function get_db_mssql_query($ar)  
	{ 
		require_once dirname(__FILE__).'/mssql.class.php';  
		ini_set('mssql.charset','utf8');  
		$r = new cm_mssql($ar);  
		return $r;  
	}
	
	static function get_db_mssql($ar) 
	{ 
		if(!is_array($ar)) return false;
		if($ar['pdoType']=='sqlsrv') return self::get_db_sqlsrv($ar); 
		else if($ar['pdoType']=='mssql') return self::get_db_mssql_win($ar);
		if(!isset($ar['pdoType'])) $ar['pdoType']='dblib';
		//if($ar['pdoType']=='dblib') require_once COMM_FRAME_PATH.'zend_ext/db_pdo_libdb.php'; 
		 
		$ar_s = md5(serialize($ar));
		if(isset(self::$db[$ar_s]) && self::$db[$ar_s]) return self::$db[$ar_s];
		else{
			$r = Zend_Db::factory('Pdo_Mssql', $ar);
			self::$db[$ar_s]=$r; 
			return $r; 
		}
	}

	static function get_db_mssql_win($ar) 
	{ 
		if(!is_array($ar)) return false;
		if(!isset($ar['pdoType'])) $ar['pdoType']='mssql';
		if($ar['pdoType']=='mssql') require_once COMM_FRAME_PATH.'zend_ext/db_pdo_mssql.php'; 
		$ar_s = md5(serialize($ar));
		if(isset(self::$db[$ar_s]) && self::$db[$ar_s]) return self::$db[$ar_s];
		else{
			$r = zend_ext_db_pdo_mssql::factory($ar); 
			self::$db[$ar_s]=$r; 
			return $r; 
		}
	}
	 
	 
	static function get_db_sqlsrv($ar) 
	{ 
		if(!is_array($ar)) return false;
		if(!isset($ar['pdoType'])) $ar['pdoType']='sqlsrv';
		if($ar['pdoType']=='sqlsrv') require_once COMM_FRAME_PATH.'zend_ext/zend_ext_db_pdo_sqlsrv.php'; 
		else return false;
		$ar_s = md5(serialize($ar));
		if(isset(self::$db[$ar_s]) && self::$db[$ar_s]) return self::$db[$ar_s]; 
		else{
			$r = zend_ext_db_pdo_sqlsrv::factory($ar); 
			self::$db[$ar_s]=$r; 
			return $r;  
		} 
	}
	
}
