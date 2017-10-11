<?php
require_once dirname(__FILE__).'/../../../snoopy/Snoopy.class.php';
require_once dirname(__FILE__).'/../../../../spider/yam/plug_lib/tsina.class.php';

class spider_plug_t_sina_com_cn_d{
	public $error;
	public function run($user_ref = array()){
		$url = $user_ref['ref'];
		$ad_id = $user_ref['ad_id'];
		$domain = 'weibo.com';
		$tsina = new tsina($domain);

		//处理用户广告相关内容
		//preg_match('/(http:\/\/'.$domain.'\/[^\/]+)(\/.*|\/?)$/iU',$url, $match);
		preg_match('/(http:\/\/t.sina.com.cn\/[^\/]+)(\/.*|\/?)$/iU',$url, $match);
		$new_url =  preg_replace('(\/t.sina.com.cn\/)','/'.$domain.'/', $match[1]);
		$ret = $tsina->do_ad_spider($new_url,$ad_id);
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