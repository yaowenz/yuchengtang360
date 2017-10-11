<?php
//转链接，短链接 2011/11/23 by ziv 
require_once dirname(__FILE__) . '/spider/my_spider/myspider.class.php';
require_once dirname(__FILE__) . '/../comm.functions.php';  
require_once dirname(__FILE__) . '/cache/cache.class.php';

class link_url_class
{
	static $error;
	
	static function get_from_baidus_cache($url,$proxy)
	{
		if(!$url) 
		{ 
			self::$error='no_url';
			return false;
		}
		$key = md5('likullasefbiuccev2'.$url);
		$time = 120; 
		$cache = new temp_cache;
		$info = $cache->get($key); 
		if (!$info){
			$info=self::get_from_baidus($url,$proxy);
			$cache->saveOrUpdate($key,$info,$time);
		}
		$cache->close();
		return $info;
	}

	static function get_from_baidus($url,$proxy)
	{
		if(!$url) 
		{ 
			self::$error='no_url';
			return false;
		}
		if(!$proxy) 
		{ 
			self::$error='baidus_no_proxy';
			return false;
		}
		//$url ='http://s20.duwub.com/s.php?k=28';
		$url.='&t='.time();
		$a['url']='http://baid.us/index.php?url='.urlencode($url).'&alias=&gen_url.x='.rand(1,72).'&gen_url.y='.rand(1,24);
		$a['ref']='http://baid.us/';
		$a['proxy'] = $proxy;
		$c = my_spider::get_info_from_curl($a);
		$header=null;
		$header[]="Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8"; 
		$header[]="Accept-Language: zh-cn,zh;q=0.5";
		$header[]="Accept-Encoding: gzip, deflate"; 
		$header[]="Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7";
		$header[]="Connection: keep-alive";
		$a['header'] = $header; 
		$c = my_spider::get_info_from_curl($a);
		if(!$c['body']) 
		{
			self::$error='body_error';
			return false;
		}
		$c['body']=gzdecode($c['body']);
		$match1=null;
		preg_match('/id=\"tinyurl\" value=\"(.*)\"/iUs',$c['body'],$match1);
		$surl=$match1[1];
		if(!$surl)
		{
			self::$error='match_error';
			return false;
		}
		return $surl; 
	}

}//end class