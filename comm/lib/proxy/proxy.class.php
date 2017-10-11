<?php
/*
代理IP池
by jay 2011-5-20
*/
require_once dirname(__FILE__).'/../spider/my_spider/myspider.class.php';
require_once dirname(__FILE__).'/../db/db.class.php';
class proxy_class{
	static $db_name = 'ncg_duap_single';
	static $proxy_table = 'proxy_ip_list';

	//获取一个代理IP
	static function get_proxy(){
		while(true){
			$ret = self::get_proxy_ip_one();
			if(is_array($ret) && !empty($ret)){
				$proxy = array();
				$proxy['p'] = 'http://' . $ret['ip'] . ':' . $ret['port'];
				$proxy['id'] = $ret['id'];
				$rs = self::test_proxy_ip($proxy);
				if(!$rs){
					$rt = self::delete_proxy_ip($ret['id']);
					continue;
				}
				return $proxy;
			}
			return false;
		}
	}
	
	//获取一条代理IP信息
	static function get_proxy_ip_one(){
		$table = self::$proxy_table;
		$db = cm_db::get_db(cm_db_config::get_ini(self::$db_name));
		$select = $db->select();
		$select->from($table,'*');
		$select->limit(1);
		$select->order(array('last_time ASC','id ASC'));
		$sql = $select->__toString();	//print_R($sql);exit;
		$ret = $db->fetchRow($sql);
		return $ret;
	}
	
	//测试一个代理IP
	static function test_proxy_ip($proxy){
		if(!isset($proxy['p'])){
			return false;
		}

		$test_url = 'http://www.baidu.com';
		$ary = array();
		$ary['url'] = $test_url;
		$ary['proxy'] = $proxy;
		$content = my_spider::get_content_for_curl($ary);
		if(!$content){
			return false;
		} else {
			//echo $content;
			return true;
		}
	}

	//保存代理IP信息
	static function save_proxy_ip($row){
		$table = self::$proxy_table;
		$db = cm_db::get_db(cm_db_config::get_ini(self::$db_name,'master'));
		try{
			$ret = $db->insert($table,$row);
		} catch(Exception $e){
			$ret = 0;
		}

		if($ret>0){
			return $db->lastInsertId();
		} else {
			return 0;
		}
	}

	//更新代理IP信息
	static function update_proxy_ip($row){
		$table = self::$proxy_table;
		$db = cm_db::get_db(cm_db_config::get_ini(self::$db_name,'master'));
		$where = $db->quoteInto('id = ? ', $row['id']); 
		$data = array();
		$data['last_time'] = $row['last_time'];
		$data['state'] = $row['state'];
		$ret = $db->update($table, $data, $where);

		if($ret>0){
			return $ret;
		} else {
			return 0;
		}
	}
	
	//删除代理IP信息
	static function delete_proxy_ip($id){
		$table = self::$proxy_table;
		$db = cm_db::get_db(cm_db_config::get_ini(self::$db_name,'master'));
		$where = $db->quoteInto('id = ?', $id);
		$ret = $db->delete($table, $where);
		if($ret>0){
			return $ret;
		} else {
			return 0;
		}
	}

	/*
	 * 抓取代理IP相关
	 */

	//抓取代理IP入库
	static function spider_proxy(){
		$proxy_provide_list = self::proxy_provide();
		foreach($proxy_provide_list as $provide=>$url){
			$ary = array();
			$ary['url'] = $url;

			$error_log = array();
			$error_log['provide_site'] = $provide;
			$error_log['state'] = 2;

			$content = my_spider::get_content_for_curl($ary);
			//var_dump($content);exit;
			if(!$content){
				$error_log['error_note'] = my_spider::$error;
				$error_log['dateline'] = time();
				self::save_error_log($error_log);
				continue;
			}

			$fun = 'analyze_' . $provide;
			$ip_list = self::$fun($content);
			if(!is_array($ip_list) || empty($ip_list)){
				$error_log['error_note'] = '分析页面错误';
				$error_log['dateline'] = time();
				self::save_error_log($error_log);
				continue;
			}

			if(empty($ip_list['ip']) || !isset($ip_list['ip'])){
				$error_log['error_note'] = '没有获取到IP';
				$error_log['dateline'] = time();
				self::save_error_log($error_log);
				continue;
			}

			$ip = $ip_list['ip'];
			$port = $ip_list['port'];
			$i = 0;
			$count = 0;
			foreach($ip as $v){
				$row = array();
				$row['ip'] = $v;
				$row['port'] = $port[$i];
				$row['last_use_time'] = 0;
				$row['state'] = 0;
				$ret = self::save_proxy_ip($row);
				if($ret > 0){
					$count++;
				}
				$i++;
			}
		}
		echo '入库总数:' . $count;
	}

	//分析 proxycn_com 站点页面上的IP地址
	static function analyze_proxycn_com($content){
		$ary = array();
		$preg = '/<TR[^>]*onDblClick=\"clip\(\'([^:]*):(\d*)\'\)[^\"]*\"[^>]*>.*<\/TR>/iUs';
		preg_match_all($preg,$content, $match);
		$ary['ip'] = $match[1];
		$ary['port'] = $match[2];
		return $ary;
	}

	//代理提供站点列表
	static function proxy_provide(){
		$ary = array();
		$ary['proxycn_com'] = 'http://www.proxycn.com/html_proxy/30fastproxy-1.html';
		return $ary;
	}
	
	//保存错误日志
	static function save_error_log($row){
		$table = 'proxy_spider_log';
		$db = cm_db::get_db(cm_db_config::get_ini(self::$db_name,'master'));
		try{
			$ret = $db->insert($table,$row);
		} catch(Exception $e){
			$ret = 0;
		}

		if($ret>0){
			return $db->lastInsertId();
		} else {
			return 0;
		}
	}
}


