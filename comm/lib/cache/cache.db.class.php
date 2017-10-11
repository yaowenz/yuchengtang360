<?php
/**
 * 缓存类,结合数据库
 */

require_once dirname(__FILE__).'/../db/db.class.php';

class cache_db_class
{
	public function __construct()
	{
		$dbs=null;
		$dbs_db=array('host'=>'127.0.0.1','port'=>'3306','username'=>'root','password'=>'sfwe8n2s','dbname'=>'cache_db');

		/*	//生成表
		$db=cm_db::get_db($dbs_db);
		for($i=1;$i<=100;$i++)
		{
			$sql="CREATE TABLE `cache_".$i."` (`key` varchar(64) COLLATE utf8_unicode_ci NOT NULL,`value` blob,PRIMARY KEY (`key`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC";
			$db->query($sql);
		}
		die('ok');
		*/

		for($i=1;$i<=100;$i++)
		{
			$dbs[$i]['db']=$dbs_db;
			$dbs[$i]['table']='cache_'.$i;
		}

		$this->dbs=$dbs;
	}
	
	public function key_base_ful($key)
	{
		if(CACHE_BASE_KEY) return md5($key.CACHE_BASE_KEY);
		else return $key;
	}

	public function get($key)
	{
		if(!$key) return false;
		$key=$this->key_base_ful($key);
		$db_info=$this->get_db($key);
		$db=cm_db::get_db($db_info['db']);
		$select = $db->select();
		$select->from($db_info['table'], 'value');
		$select->where('`key` = ?', $key);  
 		$sql = $select->__toString();
		$r = $db->fetchOne($sql);

		return $r;
	}

	public function saveOrUpdate($key,$value)
	{
		if(!$key) return false;
		$key=$this->key_base_ful($key);
		$db_info=$this->get_db($key);
		$db=cm_db::get_db($db_info['db']);
		
		$a['value']=$value;
		$where= $db->quoteInto('`key` = ?',$key);

		$select = $db->select();
		$select->from($db_info['table'], 'key');
		$select->where('`key` = ?', $key);  
 		$sql = $select->__toString();
		$r = $db->fetchOne($sql);
		if($r)	$db->update($db_info['table'],$a,$where);
		else
		{
			try{
				$a['key']=$key;
				$db->insert($db_info['table'],$a);
			}catch(Exception $e){} 
		}
		return true;
	}

	public function delete($key)
	{
		if(!$key) return false;
		$key=$this->key_base_ful($key);
		$db_info=$this->get_db($key);
		$db=cm_db::get_db($db_info['db']);
		$where= $db->quoteInto('`key` = ?',$key);
		$db->delete($db_info['table'],$where);
		return true;
	}

	public function close()
	{
		return true;
	}
	
	public function get_db($key)
	{
		if(!$key) die('网络繁忙，请稍后再试');
		$hash = $this->_time33Hash($key);
		$rt = abs(fmod($hash,count($this->dbs)))+1;
		return $this->dbs[$rt];
	}

	public function _time33Hash($str) {
        $hash = 0;
        $n = strlen($str);
        for ($i = 0; $i <$n; $i++) {
            $hash += ($hash <<5 ) + ord($str[$i]);
        }
        return $hash;
    }

}//end class

class cache_db_big_class extends cache_db_class
{
	public function __construct()
	{
		/*	//生成表
		$dbs_db=array('host'=>'127.0.0.1','port'=>'3306','username'=>'root','password'=>'sfwe8n2s','dbname'=>'cache_db_big');
		$db=cm_db::get_db($dbs_db);
		for($i=1;$i<=1000;$i++)
		{
			$sql="CREATE TABLE `cache_".$i."` (`key` varchar(64) COLLATE utf8_unicode_ci NOT NULL,`value` mediumblob,PRIMARY KEY (`key`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC";
			$db->query($sql);
		}
		die('ok');
		*/
	}

	public function get_db($key)
	{
		if(!$key) die('网络繁忙，请稍后再试');
		$hash = $this->_time33Hash($key);
		$rt = abs(fmod($hash,1000))+1;
		$dbs=null;
		$dbs_db=array('host'=>'127.0.0.1','port'=>'3306','username'=>'root','password'=>'sfwe8n2s','dbname'=>'cache_db_big');
		$dbs['db']=$dbs_db;
		$dbs['table']='cache_'.$rt;
		return $dbs;
	}

}//end class