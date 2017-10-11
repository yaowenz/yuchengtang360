<?php
require_once dirname(__FILE__).'/../../../snoopy/Snoopy.class.php';
require_once dirname(__FILE__).'/../../../../spider/yam/plug_lib/tsina.class.php';

class spider_plug_weibo_com_d{
	public $error;
	public function run($user_ref = array()){
		
		$url = $user_ref['ref'];
		$ad_id = $user_ref['ad_id'];
		$domain = 'weibo.com';
		//$domain = 't.sina.com.cn';
		$tsina = new tsina($domain);

		//处理用户广告相关内容
		$nurl = preg_replace('(\/user\/)','/', $url);
		preg_match('/(http:\/\/'.$domain.'\/[^\/]+)(\/.*|\/?)$/iU',$nurl, $match);
		//preg_match('/(http:\/\/weibo.com\/[^\/]+)(\/.*|\/?)$/iU',$nurl, $match);
		//$new_url =  preg_replace('(\/weibo.com\/)','/'.$domain.'/', $match[1]);
		//echo $new_url;exit;

		$ret = $tsina->do_ad_spider($nurl,$ad_id);
		if($ret){
			echo $url." ok";
			echo '<br/>';
			return true;
		} else {
			$this->error = $tsina->error;
			echo $url." error";
			echo '<br/>';
			return false;
		}
	}


}//end class
?>