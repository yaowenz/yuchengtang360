<?php
require_once dirname(__FILE__).'/db/db.class.php';

class crawl_article{
	

	//天涯获取内容
	static function rand_article($url_id){ 
		$url = "http://www.tianya.cn/publicforum/content/funinfo/1/$url_id.shtml"; 
		$r1=@file_get_contents($url); //把文件读入一个字符串
		if(!$r1)return false;
		$lines_array = file($url); //将文件作为一个数组返回
		$lines_string = implode('', $lines_array); //把数组元素组合为一个字符串
		$txt = '<div class="post" _app="eleshare">(.*)</div>';
		eregi($txt, $lines_string, $body); 
		$html_str1=string::convert_encoding_deep("$body[0]");
		$charset='utf-8';
		$str=strip_tags($html_str1,"");  
		$str=preg_replace("{&nbsp;}","",$str);  
		$str=preg_replace("/[\s]{2,}/","",$str); //去除连续空格及回车
		$ra=rand(40,100);
		$str = mb_substr($str,0,$ra,$charset);
		$shot_txt = strpos($str, '回复日期');
		if(is_numeric($shot_txt)){
			$str = '';
		}
		return $str;
	}

	//获取url_id 
	static function get_url_id(){ 
		$db = cm_db::get_db(cm_db_config::get_ini('comm','master'));
		$select = $db->select();
		$table= 'crawl_article_url_id';
		$select->from($table, 'url_id');

		$sql = $select->__toString();
		$result = $db->fetchOne($sql);
		return $result;
	}
		
	//内容插入数据库  
	static function article_insert($txt){
		$db = cm_db::get_db(cm_db_config::get_ini('comm','master'));
		$table= 'crawl_article';
		$a['txt'] = $txt;

		$ret = $db->insert($table, $a);
		if($ret>0){
			return $db->lastInsertId();
 		}else{
			return 0;
		}
	}

	//url_id插入数据库 
	static function article_update($url_id){ 
		$db = cm_db::get_db(cm_db_config::get_ini('comm','master'));
		$table= 'crawl_article_url_id';
		$a['url_id'] = $url_id;

		$where = $db->quoteInto('id = ?', 1);
		$rows_affected = $db->update($table, $a, $where); 
		return $rows_affected;
	}

	//从数据库取出文章内容
	static function get_article(){ 
		$db = cm_db::get_db(cm_db_config::get_ini('comm','master'));
		$select = $db->select();
		$table= 'crawl_article';
		$select->from($table, 'max(id)');
		$sql = $select->__toString();
		$maxid = $db->fetchOne($sql);

		$select = $db->select();
		$id = rand(1,$maxid);
		$select->from($table, 'txt');
		$select->where('id = ?', $id);
		$sql = $select->__toString();//print_r($sql);exit;
		$r = $db->fetchOne($sql);
		return $r;
	}
}//end class
