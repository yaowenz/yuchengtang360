<?php
//zend db oo 扩展 for db_pdo_libdb by ziv 2011/5/18
require_once dirname(__FILE__).'/../Zend/Db.php'; 
require_once dirname(__FILE__).'/../Zend/Db/Adapter/Pdo/Mssql.php'; 
class zend_ext_db_pdo_libdb extends Zend_Db
{
	public static function factory($config = array()) 
    {
        $dbAdapter = new Zend_Db_Adapter_Pdo_Mssql_ext($config);

        /*
         * Verify that the object created is a descendent of the abstract adapter type.
         */
        if (! $dbAdapter instanceof Zend_Db_Adapter_Abstract) {
            /**
             * @see Zend_Db_Exception
             */
            require_once 'Zend/Db/Exception.php';
            throw new Zend_Db_Exception("Adapter class '$adapterName' does not extend Zend_Db_Adapter_Abstract");
        }

        return $dbAdapter;
    }

}//end class

class Zend_Db_Adapter_Pdo_Mssql_ext extends Zend_Db_Adapter_Pdo_Mssql
{
	//zx
	public function fetchAll($sql, $bind = array(), $fetchMode = null)
    {
		$sql=db_pdo_libdb_comm::to_gbk_deep($sql);
		if($bind) $bind=db_pdo_libdb_comm::to_gbk_deep($bind);
		$r=parent::fetchAll($sql, $bind, $fetchMode);
		//$r=db_pdo_libdb_comm::to_utf8_deep($r);
		return $r;
    }
	public function fetchRow($sql, $bind = array(), $fetchMode = null)
    {
		$sql=db_pdo_libdb_comm::to_gbk_deep($sql);
		if($bind) $bind=db_pdo_libdb_comm::to_gbk_deep($bind);
		$r=parent::fetchRow($sql, $bind, $fetchMode);
		$r2=db_pdo_libdb_comm::to_utf8_deep($r);
		return $r2;
	}
	public function fetchOne($sql, $bind = array())
    {
		$sql=db_pdo_libdb_comm::to_gbk_deep($sql);
		if($bind) $bind=db_pdo_libdb_comm::to_gbk_deep($bind);
		$r=parent::fetchOne($sql, $bind); 
		$r2=db_pdo_libdb_comm::to_utf8_deep($r); 
		return $r2;
	}
 
/*
	public function update($table, array $bind, $where = '')
    {
		if($bind) $bind=db_pdo_libdb_comm::to_gbk_deep($bind);
		if($where) $where=db_pdo_libdb_comm::to_gbk_deep($where);
		return parent::update($table, $bind, $where); 
	} 
	
	public function insert($table, array $bind)
    {
		if($bind) $bind=db_pdo_libdb_comm::to_gbk_deep($bind);
		return parent::insert($table, $bind);   
	}
*/
	
	public function query($sql, $bind = array())
    {
		//print_R($sql);exit; 
		$sql=db_pdo_libdb_comm::to_gbk_deep($sql);
		if($bind) $bind=db_pdo_libdb_comm::to_gbk_deep($bind);
		$r=parent::query($sql, $bind);
		return $r;
	}
	

	public function prepare($sql)
    {
		$this->_defaultStmtClass = 'Zend_Db_Statement_Pdo_Mssql_ext';
		return parent::prepare($sql);
    }
	///zx 
}//end class


class Zend_Db_Statement_Pdo_Mssql_ext extends Zend_Db_Statement_Pdo
{
	public function fetchAll($style = null, $col = null)
    {
		$r=parent::fetchAll($style, $col);
		$r2=db_pdo_libdb_comm::to_utf8_deep($r);
		return $r2;
	} 
}//end class


class db_pdo_libdb_comm{
	static function to_utf8_deep($value){ 
		if(is_array($value)) $value = array_map(array('db_pdo_libdb_comm', 'to_utf8_deep'),$value);
		else {
			if(!is_string($value)) return $value;
			//$codeing = mb_detect_encoding($value, array('UTF-8','GBK'));
			$value = mb_convert_encoding($value,"UTF-8","GBK");
		} 
		return $value; 
	}

	static function to_gbk_deep($value){ 
		if(is_array($value)) $value = array_map(array('db_pdo_libdb_comm', 'to_gbk_deep'),$value);
		else {
			if(!is_string($value)) return $value;
			//$codeing = mb_detect_encoding($value,array('ASCII','GB2312','UTF-8','GBK'));
			$value = mb_convert_encoding($value,"GBK","UTF-8");
		}
		return $value;  
	}
}