<?php
/*
记录股票信息类
by zx 2010-10-12
*/
//require_once dirname(__FILE__).'/../db/db.class.php'; 
require_once dirname(__FILE__).'/../cache/cache.class.php'; 

class logstk{

	static $key_stock_data = 'cm_COMM_STOCK_DATE';//缓存key全局设置 

	 
	//从cache get_stock_data
	static function get_stock_data_cache($stk,$d){  
		$key = self::$key_stock_data . $stk . $d;
		$time = 600;  

		$cache = new temp_cache;
		$s_d_info = $cache->get($key);
		
		if (empty($s_d_info) || $_GET['__DEBUG'] == 1){
			$s_d_info = self::get_stock_data($stk,$d);
			$cache->saveOrUpdate($key,$s_d_info,$time);
		}
		$cache->close();
		if($s_d_info) return $s_d_info; else return false;
	}

	 /*
		获取 $s 股票 $d 天的行情数据
		$d eg 2010-10-12
	 */
	static function get_stock_data($stk,$d){ 
		error_reporting(E_ERROR); 
		$d2 = date("Ymd",strtotime($d . " +1 day")); //eg 20101013
		require_once dirname(__FILE__).'/../socket/Client.php';  
		$s = new Client;
		$r = $s->AskStockKLineData($stk,7,$d2,1);
		if(!$r) return false; //参数错误
		$r2['date']=date("Y-m-d",strtotime($r['m0']));
		$r2['kpj']=$r['m1']/100;
		$r2['zgj']=$r['m2']/100;
		$r2['zdj']=$r['m3']/100;
		$r2['spj']=$r['m4']/100;
		$r2['cjl']=$r['m5'];
		$r2['cje']=$r['m6'];
		$s = null;
		if($r2) return $r2;else return null;
	} 
 
}//end class
