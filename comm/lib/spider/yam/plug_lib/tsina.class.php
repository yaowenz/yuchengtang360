<?php
error_reporting(7); 
ini_set('display_errors','On');
if(!defined('COMM_PATH')) {
	define('COMM_PATH', dirname(__FILE__).'/../../../../');//共用库目录
}

//require_once COMM_PATH.'/../config/base.path.config.php';

require_once COMM_PATH.'/lib/spider/my_spider/myspider.class.php';
require_once COMM_PATH.'/lib/spider/snoopy/Snoopy.class.php';
require_once COMM_PATH.'/lib/spider/yam/plug_lib/save_spider_msg.class.php';
require_once COMM_PATH.'/lib/spider/yam/plug_lib/tsina_ad_comment_page.class.php';
require_once COMM_PATH.'/lib/domain/duap_domain.class.php';
require_once COMM_PATH.'/lib/spider/config/config.class.php';
require_once COMM_PATH.'/lib/spider/my_spider/tsina.login.php';

class tsina{
	public $error = '';
	public $domain;
	public $domain_msg;
	public $username;
	public $pwd;

	//得到一个单例
	static private $instance = NULL;
	public static function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function __construct($domain = 'weibo.com'){
		$this->domain = $domain;
		$this->domain_msg = duap_domain::get_domain_name($this->domain);

		$user = spider_config::get_user_tsina();
		$this->username = $user['user'];
		$this->pwd = $user['pwd'];
	}
	
	//执行广告抓取
	public function do_ad_spider($url,$ad_id){
		//到数据库获取广告的评论页面url
		$comment_page_msg = tsina_ad_comment_page::getInstance()->get_comment_page($ad_id);

		//分析并获取广告的评论页面url
		if(empty($comment_page_msg) || !is_array($comment_page_msg)){
			//抓取页面
			$content = $this->get_content($url);
			if(!$content){
				return false;
			}
			
			//echo $content;exit;

			//分析url
			$url_list = $this->get_ad_url($content);
			//echo 'aa';
			//print_r($url_list);exit;

			if(!$url_list ){
				return false;
			}

			//url入库
			foreach($url_list as $adid=>$value){
				$ary = array();
				$ary['adid'] = $adid;
				$ary['comment_page'] = $value['url'];
				$ary['resId'] = $value['resId'];
				$ary['tsina_uid'] = $value['tsina_uid'];
				tsina_ad_comment_page::getInstance()->save_comment_page($ary);
			}
			$comment_page_msg = $url_list[$ad_id];
		} else {
			$nowtime = time();
			//echo $comment_page_msg['updatetime'] + 5*60) .'>'. $nowtime;
			if(($comment_page_msg['updatetime'] + 5*60) > $nowtime){
				echo '--------->';
				return true;
			}
			tsina_ad_comment_page::getInstance()->update_comment_page($ad_id);
		}
		//print_r($comment_page_msg);exit;
		
		//分析评论页面
		if(is_array($comment_page_msg) && !empty($comment_page_msg)){
			$rs = $this->analyze_comment_page($comment_page_msg,$ad_id);
			return $rs;
		} else {
			$this->error = "没有获取到id为$ad_id所对应的评论页面url";
			return false;
		}
	}

	//从url上获取用户的新浪微博ID
	public function get_user_tsina_id($url){
		preg_match('/'.$this->domain.'\/(\d+)(\/.*|\/?)$/iU',$url, $match);
		$tsina_id = $match[1];
		return $tsina_id;
	}

	/***************
	 * 用户页面分析
	 ***************/
	//分析并获取用户广告的url
	public function get_ad_url($content){
		//取动态内容
		$feed_msg = $this->get_feed_list($content,'feed_list');
		if(!$feed_msg){
			$this->error = "没有获取到匹配的动态内容列表，请检查URL是否正确";
			return false;
		}
		$feed_mid = $feed_msg['mid'];
		$feed_list = $feed_msg['list'];
		
		//分析并获取评论页面的url
		$comment_page = $this->get_comment_page_url($feed_list,$feed_mid);
		return $comment_page;
	}

	//获取转贴内容
	public function get_feed_list($content,$feed_list_id){
		//preg_match_all("/<ul[^>]*feed_list[^>]*>(.+)<\/ul>/iUsu",
		preg_match_all('/<li[^>]*MIB_linedot_l[^>]*mid_(\d+)">(.+)<\/li>/iUsu',$content, $match);
		$ary = array();
		$ary['mid'] = $match[1];
		$ary['list'] = $match[2];
		return $ary;
	}
	
	//获取评论页面的URL
	public function get_comment_page_url($feed_list,$feed_mid){
		$tsina_url_list = array();
		$new_feed_list = array();
		
		$param = '';
		foreach($feed_list as $key=>$feed){
			$preg = '/href="http:\/\/t.cn\/(.*)"/iU';
			preg_match_all($preg,$feed,$match);
			if(!empty($match[1])){
				$new_feed_list[$feed_mid[$key]] = $feed;
				foreach($match[1] as $m){
					$tsina_url_list[$m] = $feed_mid[$key];
					$param .= $m.',';
				}
			}
		}

		if(substr($param,-1,1) == ','){
			$param = substr($param,0,-1);
		}

		$formvars["url"] = $param;
		$formvars["lang"] = "zh";
		$action = "http://".$this->domain."/mblog/sinaurl_info.php";
		
		//抓取所有链接地址的原始地址信息
		$url_list = $this->get_content_snoopy($action,$formvars,true);

		//print_r($url_list);
		foreach($url_list['data'] as $key=>$value){
			$url = $value['url'];
			$preg = '/(http:\/\/'.AD_DOMAIN_PREG.'\/client-share-adshare-).*adid-(\d+).*\.html/i';
			preg_match($preg,$url,$match_url);
			//print_r($match_url);
			if(!empty($match_url[1])){
				$need_mid = $tsina_url_list[$key];
				$need_feed[$match_url[2]]['feed'] = $new_feed_list[$need_mid];
				$need_feed[$match_url[2]]['mid'] = $need_mid;
			}
		}
		//print_r(AD_DOMAIN_PREG);
		//print_r($url_list);

		if(!is_array($need_feed) || empty($need_feed)){
			$this->error = "没有获取到分享的YAM广告动态";
			return false;
		}
		//print_r($need_feed);exit;
		
		$comment_page = array();
		foreach($need_feed as $key=>$feed){
			$c_url = $this->analyze_url($feed['feed']);
			$comment_page[$key]['url'] = $c_url['url'];
			$comment_page[$key]['resId'] = isset($c_url['mid'])?$c_url['mid']:$feed['mid'];
			$comment_page[$key]['tsina_uid'] = $this->get_user_tsina_id($c_url['url']);
		}
		//print_r($comment_page);exit;

		return $comment_page;
	}
	//分析页面并取出评论页面的url
	public function analyze_url($feed){
		//print_r($feed);exit;
		$ary = array();
		if(!stripos($feed,'原文转发')){
			$preg = '/href\s*=\s*"(http:\/\/'.$this->domain.'\/\d+\/.+)"/iU';
			preg_match($preg,$feed,$match);
			$ary['url'] = $match[1];
		} else {
			$preg = '/<a.*href\s*=\s*"(http:\/\/'.$this->domain.'\/\d+\/.+)".*原文转发.*rid\s*=\s*"(\d+)".*<\/a>/iU';
			preg_match($preg,$feed,$match);
			$ary['url'] = $match[1];
			$ary['mid'] = $match[2];
			//return '原文转发';
		}
		//print_R($ary);
		return $ary;
	}
	

	/***************
	 * 评论分析
	 ***************/
	//分析评论页面
	public function analyze_comment_page($ary,$ad_id){
		$comment_page_url = $ary['url'];
		$resId = $ary['resId'];
		$tsina_uid = $ary['tsina_uid'];
		
		$content = $this->get_content($comment_page_url);
		if(!$content){
			return false;
		}

		//匹配转发数
		$preg_zf = '/转发.*\((\d)\)/iU';
		preg_match($preg_zf,$content,$match_zf);

		//匹配评论数
		$preg_pl = '/评论.*\((\d)\)/iU';
		preg_match($preg_pl,$content,$match_pl);
		
		
		//回复数
		$repest_count= 0;
		if(!empty($match_zf[1])){
			$repest_count = $match_zf[1];
		}

		//评论数
		$comment_count= 0;
		if(!empty($match_pl[1])){
			$comment_count = $match_pl[1];
		}

		//print_r($arrival_ary);exit;
		/*
		echo '转发：';
		echo $repest_count;
		echo '评论：';
		echo $comment_count;exit;
		*/

		$user_info_url = "http://weibo.com/mblog/aj_getuserinfo.php?oid=$tsina_uid&rnd=0.".mt_rand();
		$user_info = json_decode($this->get_content($user_info_url),true);
		if(!$user_info){
			return false;
		}
		$user_info_data = $user_info['data'];
		$fans_count = 0;
		if(!empty($user_info_data['myfans'])){
			$fans_count = $user_info_data['myfans'];
		}
		//echo $fans_count;exit;
		
		//保存数据
		$arrival_ary = array();
		$arrival_ary['ad_id'] = $ad_id;
		$arrival_ary['domain_id'] = $this->domain_msg['id'];
		$arrival_ary['comment_count'] = $comment_count;
		$arrival_ary['repest_count'] = $repest_count;
		$arrival_ary['fans_count'] = $fans_count;
		$arrival_rs = save_spider_msg::getInstance()->save_ad_arrival($arrival_ary);

		//获取最后一个评论的信息
		$last_comment = save_spider_msg::getInstance()->get_last_comment($ad_id);
		$last_comment_list = array();
		foreach($last_comment as $l_cmt){
			$last_comment_list[$l_cmt['comment_id']] = $l_cmt;
		}
		//print_r($last_comment_list);exit;

		$from = 0;
		$comment_page_list = array();
		$stop = false;
		while(true){
			$from++;
			//echo '<br/><br/><br/>===='.$from.'====<br/><br/><br/>';
			//if($from > 100){
			//	break;
			//}

			$url = 'http://'.$this->domain.'/comment/commentlist.php'.
					'?act=2'.
					'&from='.$from.
					//'&ownerUid=1191220232'.
					'&ownerUid='.$tsina_uid.
					'&productId=weibo'.
					//'&resId=201110326358153474'.
					'&resId='.$resId.
					'&resInfo='.
					'&type=2'.
					'&role=-1'.
					'&rnd=0.'.mt_rand();

			//echo $url;
			$content = $this->get_content($url);
			if(!$content){
				return false;
			}
			$content = json_decode($content,true);
			if(!is_array($content)){
				$this->error = "抓取评论列表数据错误，请检查目标URL";
				return false;
			}
			//print_r($content);exit;

			$comment_lst = $this->get_comment_list($content,$ad_id,$last_comment_list,$stop);
			if(!is_array($comment_lst) || $comment_lst == 'over'){
				break;
			}

			//将数组按key降序排列
			krsort($comment_lst);
			$comment_page_list[$from] = $comment_lst;
			if($stop){
				break;
			}
		}
		//将数组按key降序排列
		krsort($comment_page_list);
		//print_r($comment_page_list);exit;
		
		//数据入库
		foreach($comment_page_list as $comment_page){
			foreach($comment_page as $comment){
				save_spider_msg::getInstance()->save_ad_comment($comment);
				//print_r($comment);
			}
		}
		//exit;

		return true;
	}
	//获取评论列表内容
	public function get_comment_list($content,$ad_id,$last_comment,&$stop = false){
		//echo $content;
		$over = false;

		//提取评论列表
		$preg_comment_list = '/<\s*li[^>]*>(.+)<\s*\/\s*li\s*>/iUsu';
		preg_match_all($preg_comment_list,$content['data'],$match_comment_list);
		$comment_list = $match_comment_list[1];
		if(empty($comment_list) || !is_array($comment_list)){
			$stop = true;
			return 'over';
		}
		//print_r($comment_list);exit;

		$comment_ary = array();
		foreach($comment_list as $comment){
			$c_msg = $this->analyze_comment($comment);

			if(!empty($last_comment[$c_msg['c_id']])){
				$stop = true;
				break;
			}

			//评论数据入库
			$c_ary = array();
			$c_ary['ad_id'] = $ad_id;
			$c_ary['domain_id'] = $this->domain_msg['id'];
			$c_ary['comment_txt'] = $c_msg['comment'];
			$c_ary['comment_uname'] = $c_msg['c_user'];
			$c_ary['comment_time'] = 0;//$c_msg['c_date'];
			$c_ary['comment_id'] = $c_msg['c_id'];
			$comment_ary[] = $c_ary;
		}
		//print_r($comment_ary);exit;

		return $comment_ary;
	}
	//分析评论内容
	public function analyze_comment($comment){
		//echo $comment;
		$ary = array();

		//匹配评论信息
		$preg = '/<\s*p[^>]*commentsParm[^>]*>\s*<a[^>]*>\s*(.+)\s*<\/a>\s*(.+)\((.+)\).*<\s*\/p\s*>/iUsu';
		//匹配评论id
		$preg_id = '/<div[^>]*class\s*=\s*"MIB_assign"[^>]*id\s*=\s*".*_(\d+)">/iU';

		preg_match($preg,$comment,$match);
		preg_match($preg_id,$comment,$match_id);
		
		//替换用户名中的图片标志
		$c_user = $match[1];
		preg_match('/<img[^>]*title\s*=\s*"(.*)"[^>]*>/iU',$c_user,$c_user_match);
		if(isset($c_user_match[1]) && !empty($c_user_match[1])){
			$c_user_title = '['.$c_user_match[1].']';
			$c_user = preg_replace('/<img[^>]*>/iU',$c_user_title,$c_user);
		}
		
		//替换评论中的表情图片
		$cmt = $match[2];
		preg_match('/<img[^>]*title\s*=\s*"(.*)"[^>]*>/iU',$cmt,$cmt_match);
		if(isset($cmt_match[1]) && !empty($cmt_match[1])){
			$cmt_title = '['.$cmt_match[1].']';
			$cmt = preg_replace('/<img[^>]*>/iU',$cmt_title,$cmt);
		}

		$ary['c_user'] = strip_tags($c_user);
		$ary['comment'] = strip_tags($cmt);
		$ary['c_date'] = $match[3];
		$ary['c_id'] = $match_id[1];

		/*
		echo '<br/>评论者：';
		echo $match[1];
		echo '<br/>评论内容：';
		echo $match[2];
		echo '<br/>评论日期：';
		echo $match[3];
		echo '<br/>评论ID：';
		echo $match_id[1];
		*/
		//echo "<br/>-----------------------------<br/>";

		return $ary;
	}

	/***************
	 * 公共支持方法
	 ***************/

	//用snoopy抓取内容
	public function get_content_snoopy($action,$formvars,$post = false){
		//抓取所有链接地址的原始地址信息
		$snoopy = new Snoopy;
		if($post){
			$snoopy->httpmethod = "POST";
		}
		$snoopy->agent = "(compatible; MSIE 4.01; MSN 2.5; AOL 4.0; Windows 98)"; //伪装浏览器
		$snoopy->referer = 'http://'.$this->domain; //伪装来源页地址 http_referer
		$snoopy->rawheaders["Pragma"] = "no-cache"; //cache 的http头信息
		//$snoopy->rawheaders["X_FORWARDED_FOR"] = "127.0.0.101"; //伪装ip
		$snoopy->submit($action,$formvars);
		$content = json_decode($snoopy->results,true);
		//print_r($content);exit;
		return $content;
	}
	
	//抓取页面
	public function get_content($url,$ref=null,$time_out = 60){
		my_spider::$time_out = $time_out;
		$domain = $this->domain_msg['id'];
		$cookie_jar = tsina_login::simulate_login($time_out);
		if(!$cookie_jar){
			$this->error = tsina_login::$error;
			return false;
		}
		
		//获取用户登录信息
		$user = spider_config::get_user_tsina();
		$username = $user['user'];

		$browser = spider_config::get_spider_browser($username);
		$proxy = spider_config::get_spider_proxy($username);

		$ary = array();
		$ary['url'] = $url;
		$ary['cookie_jar'] = $cookie_jar;
		$ary['proxy'] = $proxy;
		$ary['browser'] = $browser;
		$ary['ref'] = $ref;

		//$content = my_spider::get_content_for_curl($ary);
		$content = my_spider::get_info_from_curl($ary);

		if(strstr($content['location'],'/reg.php')){
			$this->error = 'unlogin';
			@unlink($cookie_jar);
			return false;
		}
		
		if(!$content['body']){
			$this->error = my_spider::$error;
			return false;
		}
		return $content['body'];
	}
}