<?php
require_once dirname(__FILE__).'/change.class.php';
require_once dirname(__FILE__).'/../../../string/string.class.php';
require_once dirname(__FILE__).'/../../../process.class.php';
//tsina 反馈内容变化保护类
class tsina_change{
	
	/***********
	 * 登录请求
	 ***********/

	//登录第一次请求
	static function login_1($back_content,$note){
		//echo '<br/>'.$back_content;
		//匹配基本格式
		$preg = '/sinaSSOController.preloginCallBack\(\{"retcode":\d*,"servertime":\d*,"nonce":"[^"]*"\}\)/iU';
		if(preg_match($preg,$back_content, $match_prelogin)){
			return true;
		}
		self::close_all_switch();
		self::save_error_log(__FUNCTION__,$back_content,$preg,$note);
		return false;
	}

	//登录第二次请求
	static function login_2($back_content,$note){
		//echo '<br/>'.htmlspecialchars($back_content);
		//匹配基本格式
		$preg = '/<script>\s*try\{sinaSSOController.setCrossDomainUrlList\(\{"retcode":\d*,"arrURL":\[[^\]]*\]\}\);\}catch\(e\)\{\}try\{sinaSSOController.crossDomainAction\(\'login\',function\(\)\{location.replace\([^\)]*\);\}\);\}catch\(e\)\{\}\s*<\/script>\s*<\/body>\s*<\/html>/iU';
		if(preg_match($preg,$back_content, $match_prelogin)){
			return true;
		}
		self::close_all_switch();
		self::save_error_log(__FUNCTION__,$back_content,$preg,$note);
		return false;
	}

	//登录第三次请求
	static function login_3($back_content,$note){
		if($back_content){
			//匹配基本格式
			$preg_1 = '/<html><head><script[^>]*>\(\{"result":[^,]*,"userinfo":\{"uniqueid":"\d*","userid":"[^"]*","displayname":"[^"]*","userdomain":"[^"]*"\}\}\);<\/script><\/head><body><\/body><\/html>/iUs';

			$preg_2 = '/<html><head><script[^>]*>\(\{"result":[^,]*,"errno":\d*,"reason":"[^"]*"\}\);<\/script><\/head><body><\/body><\/html>/iUs';
			if(preg_match($preg_1,$back_content, $match_1)){
				return true;
			} else if(preg_match($preg_2,$back_content, $match_2)){
				return true;
			}
			$preg = $preg_1 .'<br/>-----<br/>'. $preg_2;
		} else{
			if(self::close_all_switch_by_threshold('login_3',60,10)){
				return true;
			}
			$preg = '60秒内反馈内容为空连续10次';
		}
		self::close_all_switch();
		self::save_error_log(__FUNCTION__,$back_content,$preg,$note);
		return false;
	}

	//判断默认版本号
	static function login_wvr($back_content,$cookie_jar,$note){
		//return true;
		//echo '<br/>'.$back_content;exit;
		//匹配基本格式
		$preg = '/Set-Cookie:.*wvr=(\d*);.*/iUs';
		preg_match($preg,$back_content, $match_prelogin);
		$wvr = $match_prelogin[1];
		//版本号判断(3 老版本 3.6新2栏 4新3栏) 
		if($wvr > 0){
			if($wvr == 3){
				return true;
			} else {
				self::close_all_switch();
				self::save_error_log(__FUNCTION__,'wvr change',$wvr,$note);
				if(file_exists($cookie_jar)){
					@unlink($cookie_jar);
				}
				return false;
			}
		}
		if(self::close_all_switch_by_threshold('login_wvr',60,10)){
			return true;
		}

		self::close_all_switch();
		self::save_error_log(__FUNCTION__,$back_content,$preg,$note);
		return false;
	}
	
	/*
	//判断获取token后的反馈是否变化
	static function login_token($back_content){
		//echo '<br/>'.htmlspecialchars($back_content);exit;
		//匹配基本格式
		$preg = '/<html><head><script[^>]*>parent.sinaSSOController.feedBackUrlCallBack\(\{"result":.*,"userinfo":\{"uniqueid":"\d*","userid":"[^"]*","displayname":"[^"]*","userdomain":"[^"]*"\}\}\);<\/script><\/head><body><\/body><\/html>/iUs';
		if(preg_match($preg,$back_content, $match_prelogin)){
			return true;
		}
		self::close_all_switch();
		self::save_error_log(__FUNCTION__,$back_content,$preg);
		return false;
	}
	*/

	/***********
	 * 发软文请求
	 ***********/

	//发送软文，上传图片的请求
	static function softarticle_send($back_content){
		//匹配基本格式
		$preg_1 ='/\{"code":"[^"]*","data":\{"html":".*","islink":\d*,"isvideo":\d*,"extinfo":[^,]*,"SIP":"\d{1,3}\.\d{1,3}.\d{1,3}.\d{1,3}"\}\}/iUs';
		$preg_2 = '/\{"code":"M00004","msg":"[^"]*"\}/iUs';
		if(preg_match($preg_1,$back_content, $match_prelogin_1)){
			return true;
		} else if(preg_match($preg_2,$back_content, $match_prelogin_2)){
			return true;
		}
		self::close_all_switch();
		$preg = $preg_1 .'<br/>-----<br/>'. $preg_2;
		self::save_error_log(__FUNCTION__,$back_content,$preg);
		return false;
	}

	//发送软文，上传图片的请求
	static function softarticle_imgcode($back_content){
		//匹配基本格式
		$preg = '/http:\/\/weibo.com\/upimgback.html\?ret=[^&]*&pid=[^&]*&token=[^&]*&path=[^&]*/iUs';
		if(preg_match($preg,$back_content, $match_prelogin)){
			return true;
		}
		self::close_all_switch();
		self::save_error_log(__FUNCTION__,$back_content,$preg);
		return false;
	}

	/***********
	 * reach 请求
	 ***********/
	//reach 关注后返回的请求
	static function reach_follow($back_content){
		//匹配基本格式
		$preg = '/\{"code":"[^"]*","data":{"global":{"recFriend":{"show":(true|false),"oid":\d*,"name":"[^"]*"\},"intro_friend":\{"show":(true|false)\},"isfans":(true|false),"sex":"[^"]*","black":\{"show":(true|false),"oid":\d*,"name":"[^"]*","ta":"[^"]*"\}\},"atnSts":\{"refer_sort":"[^"]*","atnId":"[^"]*"\},"html":".*"\}\}/iUs';
		//$preg = '/http:\/\/weibo.com\/upimgback.html\?ret=[^&]*&pid=[^&]*&token=[^&]*&path=[^&]*/iUs';
		if(preg_match($preg,$back_content, $match_prelogin)){
			return true;
		}
		self::close_all_switch();
		self::save_error_log(__FUNCTION__,$back_content,$preg);
		return false;
	}

	//reach 取消关注后返回的请求
	static function reach_cancel_follow($back_content){
		return true;
		//匹配基本格式
		$preg = '/test/iUs';
		//$preg = '/http:\/\/weibo.com\/upimgback.html\?ret=[^&]*&pid=[^&]*&token=[^&]*&path=[^&]*/iUs';
		if(preg_match($preg,$back_content, $match_prelogin)){
			return true;
		}
		self::close_all_switch();
		self::save_error_log(__FUNCTION__,$back_content,$preg);
		return false;
	}

	/***********
	 * 公共方法
	 ***********/

	static function close_all_switch_by_threshold($key,$time = 60,$threshold = 5){
		//针对每个代理ip做登录计数器，超过额定值,20，强制重启adsl
		if($key){
			return false;
		}
		$key_t1=md5('tsina_login_back_change:'.$key);
		$ts1 = cm_process::get_times($key_t1);
		$ts1++;
		if($ts1 > $threshold){
			cm_process::del_times($key_t1);
			return false;
		} else {
			cm_process::add_times($key_t1,$time);
			return true;
		}
	}

	//关闭所有的总开关
	static function close_all_switch(){
		require_once LIB_PATH . '/softarticle/tsina/softarticle.class.php';
		require_once LIB_PATH . '/reach/reach.class.php';
		require_once LIB_PATH . '/spider/spider.class.php';

		$new_state = -1;
		softarticle::update_master_switch($new_state);
		duap_reach::master_switch_off();
		duap_spider::getInstance()->update_master_switch($new_state);
	}
	
	//记录错误日志
	static function save_error_log($function_name,$back_content,$preg,$note = ''){
		$ary = array();
		$ary['pt'] = 'tsina';
		$ary['function_name'] = $function_name;
		$ary['back_content'] = string::convert_encoding_deep($back_content);
		$ary['preg'] = $preg;
		$ary['dateline'] = time();
		$ary['note'] = $note;
		//print_r($ary);
		duap_change_log::getInstance()->add_change_log($ary);
	}
}
//end class
