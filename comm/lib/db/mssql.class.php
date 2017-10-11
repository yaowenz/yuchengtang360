<?php
/*
	db操作:mssql
	by zx 2010-12-27
*/
require_once dirname(__FILE__).'/../../../config/db.config.php';

class cm_mssql{
public $error;
private $dbhost;
private $dbname;
private $dbuser;
private $dbpass;
private $dblink = false;
private $errsql;

public function __construct($a){
   if($_SERVER['windir']) $t1=',';else $t1=':';
   $this->dbhost=$a['host'].$t1.$a['port']; 
   $this->dbuser=$a['username'];
   $this->dbpass=$a['password'];
   $this->dbname=$a['dbname']; 
   $this->dblink = @mssql_connect($this->dbhost,$this->dbuser,$this->dbpass) or die($this->halt()); 
   @mssql_select_db($this->dbname,$this->dblink);
}  

public function exec($sql){
    $this->errsql = $sql; 
	$query = @mssql_query($sql);
	return $query;
} 

public function fetch($query,$result_type = MSSQL_ASSOC){
	$row = @mssql_fetch_array($query,$result_type);   
    return $row;
}

public function fetchRow($sql,$result_type = MSSQL_ASSOC){
   $query = $this->exec($sql);
   $row = $this->fetch($query);
   $this->free_result($query);
   return $row;
}

public function fetchAll($sql,$result_type = MSSQL_ASSOC){
   $query = $this->exec($sql);
  	if(mssql_num_rows($query))
	{
		$r=null;
		while($row = mssql_fetch_array($query, MSSQL_NUM))
		{
			$r[]=$row;
		}
	} 
	$e=$this->free_result($query);
	if($e=='error') {$this->error='may be locked';return false;}  
   return $r;
}

public function affected_rows(){
   return mssql_rows_affected($this->dblink) or die ($this->halt());
}

public function num_rows($query){
   return mssql_num_rows($query);
}

public function free_result($query) {
	$r=@mssql_free_result($query);
	if($r===false) return 'error';
   }

public function insert_id(){
   $query = $this->query("SELECT @@IDENTITY as last_insert_id");
   $row = $this->fetch($query);
   $this->free_result($query);
   return $row['last_insert_id'];
}

public function seek($query,$offset){
   @mssql_data_seek($query,$offset) or die($this->halt());
}

public function halt(){
   $error = mssql_get_last_message();
   if(strtolower(ini_get('display_errors'))=='on') return 'MsSQL Error: '.$error."<br>".$this->errsql; 
}

public function fetchOne($sql){
   $query = $this->exec($sql);
   $row = $this->fetch($query);
   $this->free_result($query);
   if($row) $row = array_shift($row);
   return $row;
}
}

