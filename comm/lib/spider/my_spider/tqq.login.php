<?php
require_once COMM_PATH . '/lib/spider/my_spider/myspider.class.php';
require_once COMM_PATH . '/lib/spider/my_spider/spider.model.php';
require_once COMM_PATH . '/lib/spider/config/config.class.php';
require_once COMM_PATH . '/../config/comm.config.php';
class tqq_login{
	public $error;
	private $obj;
	private $tqq_login_domain = 'ptlogin2.qq.com';
	private $out_in_user = true; //user信息是否是外部传入的标识变量
	static $cookie_dir_style = 'by_pt';

	//得到一个单例
	static private $instance = NULL;
	public static function getInstance($obj = null) {
		if (self::$instance === NULL) {
			self::$instance = new self($obj);
		}
		return self::$instance;
	}

	function __construct($obj = null){
		if(!$obj){
			$obj = new spider_model();
		}

		//判断是否有外部传入的domain，没有则设置一个默认值
		if(!$obj->get_domain()){
			$obj->set_domain('t.qq.com');
		}
		$this->obj = $obj;
	}

	//模拟登录
	function tqq_simulate_login($time_out = 0){
		$process_id = $this->obj->get_process_id();
		$domain = $this->obj->get_domain();
		$user = $this->obj->get_user();
		$proxy = $this->obj->get_proxy();
		$user_browser = $this->obj->get_user_browser();
		my_spider::$time_out = $time_out;
		my_spider::$pt_name = 'tqq';
		//如果没有外部传入的user则返回错误
		if(!$user){
			$this->error = 'no user';
			return false;
		}
		
		//如果没有外部传入的浏览器信息则获取一个浏览器信息
		$user_key = $user['user'] . $process_id;
		if(!$user_browser){
			$user_browser = spider_config::get_spider_browser($user_key);
		}
		
		//获取登陆的验证码
		$u = $user['user'];
		$pwd = $user['pwd'];
		$uname = $user['uname'];
		
		//是否已登陆判断
		$cookie_key = $domain . $u;
		$cookie_jar = my_spider::get_cookie_jar_path($cookie_key,self::$cookie_dir_style);
		if(file_exists($cookie_jar)){
			return $cookie_jar;
		}
		
		//去tqq获取验证码信息及第一个cookie
		$uname = '@'.$uname;
		$vc_ary = $this->get_vc_code($uname,$proxy,$user_browser,$cookie_key);
		if($vc_ary === false){
			$this->error = 'get vc error';
			return false;
		}
		if($vc_ary['is_vc'] == 1){
			$this->error = 'account need vc';
			return false;
		}

		$cookie_jar = $vc_ary['cookie_jar'];
		$key = $vc_ary['vc_code'];
		if(!$key || !$cookie_jar){
			$this->error = 'get key or first cookie error';
			return false;
		}
		
		//获取加密后的密码
		$p = tqq_login_encoder::get_enp_pwd($pwd,$key);
		//登录
		$login_url =  "http://".$this->tqq_login_domain."/login".
				"?u=".$uname.
				"&p=".$p.
				"&verifycode=".$key.
				"&low_login_enable=1".
				"&low_login_hour=720".
				"&aid=46000101".
				"&u1=".urlencode("http://".$domain).
				"&ptredirect=1".
				"&h=1".
				"&from_ui=1".
				"&dumy=".
				"&fp=loginerroralert".
				"&action=".rand(5,20)."-".rand(5,20)."-".rand(100000,999999);
		$ref = 'http://' . $domain;
		$ary_login = array();
		$ary_login['url'] = $login_url;
		if($proxy){
			$ary_login['proxy'] = $proxy;
		}
		$ary_login['browser'] = $user_browser;
		$ary_login['cookie_jar'] = $cookie_jar;
		$ary_login['ref'] = $ref;
		$login_back_content = my_spider::get_info_from_curl($ary_login,$cookie_key,self::$cookie_dir_style);
		if(!$login_back_content && !$login_back_content['cookie_jar']){
			$this->error = my_spider::$error;
			return false;
		}
		
		if(strstr($login_back_content['body'],'http://aq.qq.com/cn/services/abnormal/abnormal_index')){
			$this->error = 'account block';
			@unlink($login_back_content['cookie_jar']);
			return false;
		}
		return $login_back_content['cookie_jar'];
	}
	
	//获取服务器端验证码
	function get_vc_code($u,$proxy,$browser,$cookie_key){
		//获取验证码
		$check_url = 'http://'.$this->tqq_login_domain.'/check?uin='.$u.'&appid=46000101&r=' . substr(str_replace(" ", "", microtime()),0,18);
		//echo $check_url.'<br/>';
		$ary_check = array();
		$ary_check['url'] = $check_url;
		if($proxy){
			$ary_check['proxy'] = $proxy;
		}
		$ary_check['browser'] = $browser;
		$ary_check['ref'] = 'http://t.qq.com';
		//$cookie_key = 'vc_cookie:' . $u;
		$back_content = my_spider::get_info_from_curl($ary_check,$cookie_key,self::$cookie_dir_style);
		if(!$back_content){
			$this->error = my_spider::$error;
			@unlink($cookie_key);
			return false;
		}
		//print_r($back_content['body']);exit;

		$preg_vc = '/ptui_checkVC\(\'(\d)\',\'([^\']*)\'\)/iU';
		preg_match($preg_vc,$back_content['body'],$match_vc);
		$vc = array();
		$vc['is_vc'] = trim($match_vc[1]);
		$vc['vc_code'] = trim($match_vc[2]);
		$vc['cookie_jar'] = $back_content['cookie_jar'];
		return $vc;
	}

	//返回全局的obj
	function get_obj(){
		return $this->obj;
	}
}

class tqq_login_encoder{
	static function get_enp_pwd($pwd,$key){
		if(isset($_SERVER['windir'])){
			return self::enp($pwd,$key);
		}else {
			require_once COMM_PATH.'/lib/http/HttpClient.class.php'; 
			$url = "http://crontab.duap.newcogs.win/webservice/tqqencoder.crontab.php?pwd=$pwd&key=$key";//echo $url;exit;
			$r = HttpClient::quickGet($url);
			return $r;
		}
	}
	
	static function enp($pwd,$key){
		$js = 'var hexcase=1;var b64pad="";var chrsz=8;var mode=32;function md5_3(b){var a=new Array;a=core_md5(str2binl(b),b.length*chrsz);a=core_md5(a,16*chrsz);a=core_md5(a,16*chrsz);return binl2hex(a)}function md5(a){return hex_md5(a)}function hex_md5(a){return binl2hex(core_md5(str2binl(a),a.length*chrsz))}function str_md5(a){return binl2str(core_md5(str2binl(a),a.length*chrsz))}function core_md5(e,j){e[j>>5]|=128<<((j)%32);e[(((j+64)>>>9)<<4)+14]=j;var f=1732584193;var g=-271733879;var h=-1732584194;var i=271733878;for(var b=0;b<e.length;b+=16){var k=f;var a=g;var c=h;var d=i;f=md5_ff(f,g,h,i,e[b+0],7,-680876936);i=md5_ff(i,f,g,h,e[b+1],12,-389564586);h=md5_ff(h,i,f,g,e[b+2],17,606105819);g=md5_ff(g,h,i,f,e[b+3],22,-1044525330);f=md5_ff(f,g,h,i,e[b+4],7,-176418897);i=md5_ff(i,f,g,h,e[b+5],12,1200080426);h=md5_ff(h,i,f,g,e[b+6],17,-1473231341);g=md5_ff(g,h,i,f,e[b+7],22,-45705983);f=md5_ff(f,g,h,i,e[b+8],7,1770035416);i=md5_ff(i,f,g,h,e[b+9],12,-1958414417);h=md5_ff(h,i,f,g,e[b+10],17,-42063);g=md5_ff(g,h,i,f,e[b+11],22,-1990404162);f=md5_ff(f,g,h,i,e[b+12],7,1804603682);i=md5_ff(i,f,g,h,e[b+13],12,-40341101);h=md5_ff(h,i,f,g,e[b+14],17,-1502002290);g=md5_ff(g,h,i,f,e[b+15],22,1236535329);f=md5_gg(f,g,h,i,e[b+1],5,-165796510);i=md5_gg(i,f,g,h,e[b+6],9,-1069501632);h=md5_gg(h,i,f,g,e[b+11],14,643717713);g=md5_gg(g,h,i,f,e[b+0],20,-373897302);f=md5_gg(f,g,h,i,e[b+5],5,-701558691);i=md5_gg(i,f,g,h,e[b+10],9,38016083);h=md5_gg(h,i,f,g,e[b+15],14,-660478335);g=md5_gg(g,h,i,f,e[b+4],20,-405537848);f=md5_gg(f,g,h,i,e[b+9],5,568446438);i=md5_gg(i,f,g,h,e[b+14],9,-1019803690);h=md5_gg(h,i,f,g,e[b+3],14,-187363961);g=md5_gg(g,h,i,f,e[b+8],20,1163531501);f=md5_gg(f,g,h,i,e[b+13],5,-1444681467);i=md5_gg(i,f,g,h,e[b+2],9,-51403784);h=md5_gg(h,i,f,g,e[b+7],14,1735328473);g=md5_gg(g,h,i,f,e[b+12],20,-1926607734);f=md5_hh(f,g,h,i,e[b+5],4,-378558);i=md5_hh(i,f,g,h,e[b+8],11,-2022574463);h=md5_hh(h,i,f,g,e[b+11],16,1839030562);g=md5_hh(g,h,i,f,e[b+14],23,-35309556);f=md5_hh(f,g,h,i,e[b+1],4,-1530992060);i=md5_hh(i,f,g,h,e[b+4],11,1272893353);h=md5_hh(h,i,f,g,e[b+7],16,-155497632);g=md5_hh(g,h,i,f,e[b+10],23,-1094730640);f=md5_hh(f,g,h,i,e[b+13],4,681279174);i=md5_hh(i,f,g,h,e[b+0],11,-358537222);h=md5_hh(h,i,f,g,e[b+3],16,-722521979);g=md5_hh(g,h,i,f,e[b+6],23,76029189);f=md5_hh(f,g,h,i,e[b+9],4,-640364487);i=md5_hh(i,f,g,h,e[b+12],11,-421815835);h=md5_hh(h,i,f,g,e[b+15],16,530742520);g=md5_hh(g,h,i,f,e[b+2],23,-995338651);f=md5_ii(f,g,h,i,e[b+0],6,-198630844);i=md5_ii(i,f,g,h,e[b+7],10,1126891415);h=md5_ii(h,i,f,g,e[b+14],15,-1416354905);g=md5_ii(g,h,i,f,e[b+5],21,-57434055);f=md5_ii(f,g,h,i,e[b+12],6,1700485571);i=md5_ii(i,f,g,h,e[b+3],10,-1894986606);h=md5_ii(h,i,f,g,e[b+10],15,-1051523);g=md5_ii(g,h,i,f,e[b+1],21,-2054922799);f=md5_ii(f,g,h,i,e[b+8],6,1873313359);i=md5_ii(i,f,g,h,e[b+15],10,-30611744);h=md5_ii(h,i,f,g,e[b+6],15,-1560198380);g=md5_ii(g,h,i,f,e[b+13],21,1309151649);f=md5_ii(f,g,h,i,e[b+4],6,-145523070);i=md5_ii(i,f,g,h,e[b+11],10,-1120210379);h=md5_ii(h,i,f,g,e[b+2],15,718787259);g=md5_ii(g,h,i,f,e[b+9],21,-343485551);f=safe_add(f,k);g=safe_add(g,a);h=safe_add(h,c);i=safe_add(i,d)}if(mode==16){return Array(g,h)}else{return Array(f,g,h,i)}}function md5_cmn(b,e,f,a,c,d){return safe_add(bit_rol(safe_add(safe_add(e,b),safe_add(a,d)),c),f)}function md5_ff(f,g,b,c,a,d,e){return md5_cmn((g&b)|((~g)&c),f,g,a,d,e)}function md5_gg(f,g,b,c,a,d,e){return md5_cmn((g&c)|(b&(~c)),f,g,a,d,e)}function md5_hh(f,g,b,c,a,d,e){return md5_cmn(g^b^c,f,g,a,d,e)}function md5_ii(f,g,b,c,a,d,e){return md5_cmn(b^(g|(~c)),f,g,a,d,e)}function safe_add(a,b){var c=(a&65535)+(b&65535);var d=(a>>16)+(b>>16)+(c>>16);return(d<<16)|(c&65535)}function bit_rol(a,b){return(a<<b)|(a>>>(32-b))}function str2binl(b){var c=Array();var a=(1<<chrsz)-1;for(var d=0;d<b.length*chrsz;d+=chrsz){c[d>>5]|=(b.charCodeAt(d/chrsz)&a)<<(d%32)}return c}function binl2str(c){var b="";var a=(1<<chrsz)-1;for(var d=0;d<c.length*32;d+=chrsz){b+=String.fromCharCode((c[d>>5]>>>(d%32))&a)}return b}function binl2hex(c){var d=hexcase?"0123456789ABCDEF":"0123456789abcdef";var b="";for(var a=0;a<c.length*4;a++){b+=d.charAt((c[a>>2]>>((a%4)*8+4))&15)+d.charAt((c[a>>2]>>((a%4)*8))&15)}return b};';

		$oScript = new COM("MSScriptControl.ScriptControl"); 
		$oScript->Language = "JavaScript"; 
		$oScript->AllowUI = false; 
		$oScript->AddCode("function enc_pwd(p){var key = '$key';return md5(md5_3(p)+key.toUpperCase());}"); 
		$oScript->AddCode($js); 
		$enc_pwd = $oScript->Run("enc_pwd","$pwd");
		return $enc_pwd;
	}
}//end class
