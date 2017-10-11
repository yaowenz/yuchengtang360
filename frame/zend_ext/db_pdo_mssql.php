<?php
//zend db oo 扩展 for db_pdo_mssql by ziv 2011-5-31
require_once dirname(__FILE__).'/../Zend/Db.php'; 
require_once dirname(__FILE__).'/../Zend/Db/Adapter/Pdo/Mssql.php'; 
require_once dirname(__FILE__).'/../Zend/Db/Select.php';  
class zend_ext_db_pdo_mssql extends Zend_Db
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
	public function select()
    {
		return new Zend_Db_Select_Mssql_ext($this);
    }
	public function fetchAll($sql, $bind = array(), $fetchMode = null)
    {
		//$sql=db_pdo_mssql_comm::to_gbk_deep($sql);
		//if($bind) $bind=db_pdo_mssql_comm::to_gbk_deep($bind);
		$r=parent::fetchAll($sql, $bind, $fetchMode);
		$r=db_pdo_mssql_comm::to_int($r);
		return $r;
    }
	public function fetchRow($sql, $bind = array(), $fetchMode = null)
    {
		//$sql=db_pdo_mssql_comm::to_gbk_deep($sql);
		//if($bind) $bind=db_pdo_mssql_comm::to_gbk_deep($bind);
		$r=parent::fetchRow($sql, $bind, $fetchMode);
		$r=db_pdo_mssql_comm::to_utf8_deep($r);
		$r=db_pdo_mssql_comm::to_int($r);
		return $r;
	}
	public function fetchOne($sql, $bind = array())
    {
		//$sql=db_pdo_mssql_comm::to_gbk_deep($sql);
		//if($bind) $bind=db_pdo_mssql_comm::to_gbk_deep($bind);
		$r=parent::fetchOne($sql, $bind); 
		$r=db_pdo_mssql_comm::to_utf8_deep($r); 
		$r=db_pdo_mssql_comm::to_int($r);
		return $r;
	}
	
	public function update($table, array $bind, $where = '')
    {
        /**
         * Build "col = ?" pairs for the statement,
         * except for Zend_Db_Expr which is treated literally.
         */
        $set = array();
        $i = 0;
        foreach ($bind as $col => $val) {
            if ($val instanceof Zend_Db_Expr) {
                $val = $val->__toString();
                unset($bind[$col]);
            } else {
                if ($this->supportsParameters('positional')) {
                    $val = '?';
                } else {
                    if ($this->supportsParameters('named')) {
                        unset($bind[$col]);
                        $bind[':'.$col.$i] = $val;
                        $val = ':'.$col.$i;
                        $i++;
                    } else {
                        /** @see Zend_Db_Adapter_Exception */
                        require_once 'Zend/Db/Adapter/Exception.php';
                        throw new Zend_Db_Adapter_Exception(get_class($this) ." doesn't support positional or named binding");
                    }
                }
            }
            $set[] = $this->quoteIdentifier($col, true) . ' = ' . $val;
        }

        $where = $this->_whereExpr($where);

        /**
         * Build the UPDATE statement
         */
        $sql = "UPDATE "
             . $this->quoteIdentifier($table, true)
			 //. " with(ROWLOCK) "								//ziv  
             . ' SET ' . implode(', ', $set)
             . (($where) ? " WHERE $where" : '');

			 //print_R($sql);echo '<br/>'; 
 
		/**
         * Execute the statement and return the number of affected rows
         */
        if ($this->supportsParameters('positional')) {
            $stmt = $this->query($sql, array_values($bind));
        } else {
            $stmt = $this->query($sql, $bind);
        }
        $result = $stmt->rowCount();
        return $result;
    }

/*	
	public function insert($table, array $bind)
    {
		if($bind) $bind=db_pdo_mssql_comm::to_gbk_deep($bind);
		return parent::insert($table, $bind);   
	}
*/
	
	public function query($sql, $bind = array())
    {
		//print_R($sql);//exit; 
		$sql=db_pdo_mssql_comm::to_gbk_deep($sql);
		if($bind) $bind=db_pdo_mssql_comm::to_gbk_deep($bind);
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
		$r=db_pdo_mssql_comm::to_utf8_deep($r);
		$r=db_pdo_mssql_comm::to_int($r);  
		return $r;
	} 
}//end class

class Zend_Db_Select_Mssql_ext extends Zend_Db_Select
{
	public $select_with='';
	protected function _renderFrom($sql)
    {
        /*
         * If no table specified, use RDBMS-dependent solution
         * for table-less query.  e.g. DUAL in Oracle.
         */
        if (empty($this->_parts[self::FROM])) {
            $this->_parts[self::FROM] = $this->_getDummyTable();
        }
 
        $from = array();

        foreach ($this->_parts[self::FROM] as $correlationName => $table) {
            $tmp = '';

            $joinType = ($table['joinType'] == self::FROM) ? self::INNER_JOIN : $table['joinType'];

            // Add join clause (if applicable)
            if (! empty($from)) {
                $tmp .= ' ' . strtoupper($joinType) . ' ';
            }

            $tmp .= $this->_getQuotedSchema($table['schema']);
            $tmp .= $this->_getQuotedTable($table['tableName'], $correlationName);
			if($this->select_with) $tmp .= ' with('.$this->select_with.') ';

            // Add join conditions (if applicable)
            if (!empty($from) && ! empty($table['joinCondition'])) {
                $tmp .= ' ' . self::SQL_ON . ' ' . $table['joinCondition'];
            }

            // Add the table name and condition add to the list
            $from[] = $tmp;
        }

        // Add the list of all joins
        if (!empty($from)) {
            $sql .= ' ' . self::SQL_FROM . ' ' . implode("\n", $from);
        }

		//print_R($sql);echo '<br/>';  //ziv  

        return $sql; 
    }
}//end class

class db_pdo_mssql_comm{
	static function to_utf8_deep($value){ 
		if(is_array($value)) $value = array_map(array('db_pdo_mssql_comm', 'to_utf8_deep'),$value);
		else {
			if(!is_string($value)) return $value;
			//$codeing = mb_detect_encoding($value, array('UTF-8','GBK'));
			$value = mb_convert_encoding($value,"UTF-8","GBK");
		} 
		return $value; 
	}

	static function to_gbk_deep($value){ 
		if(is_array($value)) $value = array_map(array('db_pdo_mssql_comm', 'to_gbk_deep'),$value);
		else {
			if(!is_string($value)) return $value;
			//$codeing = mb_detect_encoding($value,array('ASCII','GB2312','UTF-8','GBK'));
			$value = mb_convert_encoding($value,"GBK","UTF-8");
		}
		return $value;  
	}
	static function to_int($value){ 
		if(is_array($value)) $value = array_map(array('db_pdo_mssql_comm', 'to_int'),$value);
		else {
			if(!is_numeric($value)) return $value;
			if(intval($value)==$value*1) $value=intval($value);
		}
		return $value;  
	}
}//end class