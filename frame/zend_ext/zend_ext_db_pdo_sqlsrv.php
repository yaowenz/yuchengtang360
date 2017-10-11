<?php
//zend db oo 扩展 for db_pdo_sqlsrv by ziv 2011/5/26
require_once dirname(__FILE__).'/../Zend/Db.php'; 
require_once dirname(__FILE__).'/../Zend/Db/Adapter/Pdo/Mssql.php'; 
require_once 'Zend_Db_Adapter_Pdo_Sqlsrv_ext.php';

class zend_ext_db_pdo_sqlsrv extends Zend_Db
{
	public static function factory($config = array()) 
    {
        $dbAdapter = new Zend_Db_Adapter_Pdo_Sqlsrv_ext($config);

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


class Zend_Db_Select_sqlsrv_ext extends Zend_Db_Select
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