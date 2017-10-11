<?php
/*
加投 代理相关 非adsl，代理池方式
by ziv 2011/8/29
*/

require_once dirname(__FILE__).'/../db/db.class.php';
require_once dirname(__FILE__).'/../string/string.class.php';
require_once dirname(__FILE__).'/../process.class.php';

class proxy_adshell{ 
	
	static $error='';

	static $key_p_adsl_list='key_p_adsl_list_v1'; 
	static $tag_p_adsl_list='tag_p_adsl_list_v1'; 
 
	//根据key，从db里取出一条 http代理ip，如果没有返回 false 
	static function get_proxy($key){
		if(!$key) return false;
		$a['state']='1';  
		$t1 = self::get_list_from_cache($a);  
		if(!$t1) return false;
		$hash = self::_time33Hash($key);
		$count=count($t1); 
		$rt = abs(fmod($hash, $count)); 
		$t1[$rt]['p'] = 'http://' . $t1[$rt]['ip'] . ':' . $t1[$rt]['port']; 
		return $t1[$rt];  
	}

	static function get_list_from_cache($a=array()){
		require_once dirname(__FILE__).'/../cache/cache.class.php';
		$expire = 300;
		$t = serialize(array_deep_to_char($a));
		$key = md5(self::$key_p_adsl_list.$t.'v1');
		$tag = self::$tag_p_adsl_list; 
		$cache = new temp_cache; 
		$info = $cache->get($key); 
		if (empty($info)){  
			$info = self::get_list($a);
			$cache->saveByTag($key,$info,$tag,$expire);
		} 
		$cache->close();
		if($info) return $info; else return false; 
	} 

	static function get_list($a=array()){   
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm'));  
		$select = $db->select();
		$select	->from('proxy_pond', '*'); 
		if(is_numeric($a['state'])){
			$select->where('state = ?',$a['state']);
		} 
		if($a['region_key']){
			$select->where('region_key = ?',$a['region_key']); 
		} 
		$sql = $select->__toString();      
		$r=$db->fetchAll($sql); 
		return $r; 
	}

	static function delete_one($ip){   
		if(!$ip) return false;
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm','master'));   

		$sql="insert proxy_pond_deleted select * from proxy_pond where ip = '".$ip."'"; 
		$r=$db->query($sql); 

		$where = $db->quoteInto('ip = ?',$ip); 
		$db->delete('proxy_pond', $where); 
		return true;
	}

	
	static function update_adsl_ip($id,$ip){
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm','master'));
		$set = array (
			'ip' => $ip,
			'state' => 0
		);
		$table= 'proxy_pond';
		$where= $db->quoteInto('id = ?',$id);
		$db->update($table,$set,$where);
	}

	static function update_proxy_state($ip,$state){
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm','master'));
		$set = array ('state' => $state);
		$table= 'proxy_pond';
		$where= $db->quoteInto('ip = ?',$ip);
		$db->update($table,$set,$where);
	}
		
		
	
	static function proxy_insert($a){
		$db =cm_db::get_db(cm_db_config::get_ini('ncg_comm','master')); 
		$a['create_time']=time();
		try{
			@$db->insert('proxy_pond', $a);  
		}catch(Exception $e){} 
		

			$lid=$db->lastInsertId(); 
			if($lid) $a['id'] = $lid;
			$db->insert('proxy_pond_insert_log', $a); 
	}
 
	//从 QQ：1695834915 批发商 提供的提取ip接口 $dq 地区 如:上海市 
	static function get_from_taobao1_into_poll($num=1,$dq=null) 
	{
		//http://www.cw999.com/b1.asp?old=1&ddbh=106341983787380&sl=1&dq=上海
		//http://60.173.11.232:2222/b1.asp?old=1&ddbh=168858248737380&sl=1&dq=上海
		//http://60.173.11.232:2222/b1.asp?old=1&ddbh=kjsdfk345&sl=1&dq=上海
		//168858248737380
		
		$dq_s='';
		if($dq)
		{
			$tct=string::$code_to;
			string::$code_to='gb2312';
			$dq_s='&dq='.urlencode(string::convert_encoding_deep($dq));  
			string::$code_to=$tct;
		}
		
		@header("content-type:text/html;charset=utf-8");
		
		require_once COMM_PATH . '/lib/spider/my_spider/myspider.class.php';

		$host = 'http://60.173.11.232:2222'; 
		$ddbh = '168858248737380';	//订单编号：kjsdfk345
		//$num=1; //每次取多少个ip
		
		$a['url']=$host.'/b1.asp?old=1&ddbh='.$ddbh.'&sl='.$num.$dq_s; 

		//echo '<br/>url:<br/>';print_R($a['url']);echo '<br/>';   exit; 
		 
		$c = my_spider::get_info_from_curl($a);  
		$s=$c['body'];
		$a=null; 

		//echo '<br/>s:<br/>';print_R($s);echo '<br/>'; 

		//$s='113.227.164.212:8909@HTTP$匿名#辽宁省大连市联通ADSL<br>123.170.250.179:8909@HTTP$匿名#山东省临沂市 <br>'; 
		$s=string::convert_encoding_deep($s); 

		//echo '<br/>convert_encoding_deep:<br/>';print_R($s);echo '<br/>';

		$t1=explode("<br>", $s);

		//echo '<br/>t1:<br/>';print_R($t1);echo '<br/>'; 
		$a=null;$ra=null;

		//增加到 ip 库
		foreach ($t1 as $k1=>$i1)
		{
			if($i1)
			{
				$matches=null;
				$preg1="/^(.*):(.*)@HTTP[\$].*#(.*)$/";     
				preg_match($preg1,trim($i1),$matches);
				//$a[$k1]['matches']=$matches;
				$a=null;
				$a['ip']=$matches[1];
				$a['port']=$matches[2];
				$a['region']=$matches[3]; 
				if($a['region'])
				{
					$a['region_key']=mb_substr($a['region'],0,3,'utf-8'); 
				}
				if($a && is_array($a)) 
				{	
					self::proxy_insert($a);
				}
				$ra[]=$a;
			}
		}
		//echo '<br/>ra:<br/>';print_R($ra);echo '<br/>';
		
		if($ra && is_array($ra))
		{
			//增加到 edm reg 和 edm run 轮循列表 
			require_once dirname(__FILE__).'/proxy.edm.php';

			proxy_poll_bl::$m='MEMCACHE_COMM_BASHIDASHA';

			proxy_poll_bl::$k=proxy_edm::$k;  
			proxy_poll_bl::add_poll_list($ra);

			proxy_poll_bl::$k=proxy_edm::$k2;  
			proxy_poll_bl::add_poll_list($ra); 
			
			/// 
		}

		return true;  
	}
	
	static function _time33Hash($str) {
        $hash = 0;
        $n = strlen($str);
        for ($i = 0; $i <$n; $i++) {
            $hash += ($hash <<5 ) + ord($str[$i]);
        }
        return $hash;
    } 
}//end class