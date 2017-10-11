<?php
require_once COMM_PATH . '/lib/spider/my_spider/myspider.class.php';
require_once COMM_PATH . '/lib/spider/config/config.class.php';
require_once COMM_PATH . '/../config/comm.config.php';
class kaixin_login{
	public $error = '';
	static $user=array();
	static $cookie_dir_style = 'by_pt';
	
	//模拟登录
	static function kaixin_simulate_login($u,$obj,$time_out = 0){
		my_spider::$time_out = $time_out;
		my_spider::$pt_name = 'kaixin001';
		
		//print_r($u);exit;
		//获取用户登录信息
		if(!self::$user){
			self::$user = $u;
		}
		$username = self::$user['user'];
		$pwd = self::$user['pwd'];
		$process_id = $obj -> get_process_id();
		$domain = $obj -> get_domain();
		//取浏览器信息
		$user_key = self::$user['user'] . $process_id;
		$browser = spider_config::get_spider_browser($user_key);

		$user_key = $domain.$username;
		$cookie_jar= my_spider::get_cookie_jar_path($user_key,self::$cookie_dir_style);
		if(!file_exists($cookie_jar)){
			$ary = array();
			$ary['url'] = 'http://'.$domain.'/login/index.php';
			$encypt = self::get_kaixin_login_encypt($ary);
			$rpasswd = kaixin_login_encoder::get_enp_pwd($pwd,$encypt);//得到加密的密码
	
			$post_p=array();
			$post_p['email'] = $username;
			$post_p['encypt'] = $encypt;
			$post_p['rpasswd'] = $rpasswd;
			$post_p['url'] = '/home/';
			$post_p['ver']= 1;
			$ary=null;
			$ary['post_request']=$post_p;
			$ary['url']='http://www.kaixin001.com/login/login_api.php';
			$ary['browser']=$browser;
			$ary['ref'] = 'http://www.kaixin001.com/';
			//print_r($ary);exit;
			$con_ary=my_spider::get_info_from_curl($ary,$user_key,self::$cookie_dir_style);
			$cookie_jar = $con_ary['cookie_jar'];
		}
		//echo $cookie_jar;exit;
		return $cookie_jar;
	}
	
	//获得开心encypt
	static function get_kaixin_login_encypt($ary = array()){
		$html_msg=my_spider::get_info_from_curl($ary,self::$cookie_dir_style);
		$preg_key = '/EnLogin\(\'([^\']*)\',/iU';
		preg_match($preg_key,$html_msg['body'],$ary_key);
		return $ary_key[1];
	}
}


class kaixin_login_encoder{
	static function get_enp_pwd($pwd,$key){
		if(isset($_SERVER['windir'])){
			return self::enp($pwd,$key);
		}else {
			require_once COMM_PATH.'/lib/http/HttpClient.class.php'; 
			$url = "http://crontab.duap.newcogs.win/webservice/kaixinencoder.crontab.php?pwd=$pwd&key=$key";
			$r = HttpClient::quickGet($url);
			return $r;
		}
	}
	
	static function enp($pwd,$key){
		$js = 'function enp(a){return h(en(uen(a)))}function uen(a){a=(a+"").toString();return encodeURIComponent(a).replace(/!/g,"%21").replace(/\'/g,"%27").replace(/\(/g,"%28").replace(/\)/g,"%29").replace(/\*/g,"%2A").replace(/%20/g,"+").replace(/~/g,"%7E")}function bh(c){var b=new Array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");var g="";var a=c.length;for(var e=0,d=a<<2;e<d;e++){g+=b[((c[e>>2]>>(((e&3)<<3)+4))&15)]+b[((c[e>>2]>>((e&3)<<3))&15)]}return g}function sl(e,b){var a=e.length;var c=[];for(var d=0;d<a;d+=4){c[d>>2]=e.charCodeAt(d)|e.charCodeAt(d+1)<<8|e.charCodeAt(d+2)<<16|e.charCodeAt(d+3)<<24}if(b){c[c.length]=a}return c}function en(b){if(b==""){return""}var r=sl(b,true);var d=sl(key,false);if(d.length<4){d.length=4}var c=r.length-1;var j=r[c],l=r[0],m=2654435769;var o,i,b,a=Math.floor(6+52/(c+1)),g=0;while(0<a--){g=g+m&4294967295;i=g>>>2&3;for(b=0;b<c;b++){l=r[b+1];o=(j>>>5^l<<2)+(l>>>3^j<<4)^(g^l)+(d[b&3^i]^j);j=r[b]=r[b]+o&4294967295}l=r[0];o=(j>>>5^l<<2)+(l>>>3^j<<4)^(g^l)+(d[b&3^i]^j);j=r[c]=r[c]+o&4294967295}return bh(r)}function f(b,a,d,c){switch(b){case 0:return(a&d)^(~a&c);case 1:return a^d^c;case 2:return(a&d)^(a&c)^(d&c);case 3:return a^d^c}}function rotl(a,b){return(a<<b)|(a>>>(32-b))}function tohs(d){var c="",a;for(var b=7;b>=0;b--){a=(d>>>(b*4))&15;c+=a.toString(16)}return c}function h(m){var p=[1518500249,1859775393,2400959708,3395469782];m+=String.fromCharCode(128);var z=m.length/4+2;var n=Math.ceil(z/16);var o=new Array(n);for(var B=0;B<n;B++){o[B]=new Array(16);for(var A=0;A<16;A++){o[B][A]=(m.charCodeAt(B*64+A*4)<<24)|(m.charCodeAt(B*64+A*4+1)<<16)|(m.charCodeAt(B*64+A*4+2)<<8)|(m.charCodeAt(B*64+A*4+3))}}o[n-1][14]=((m.length-1)*8)/Math.pow(2,32);o[n-1][14]=Math.floor(o[n-1][14]);o[n-1][15]=((m.length-1)*8)&4294967295;var w=1732584193;var v=4023233417;var u=2562383102;var r=271733878;var q=3285377520;var g=new Array(80);var G,F,E,D,C;for(var B=0;B<n;B++){for(var x=0;x<16;x++){g[x]=o[B][x]}for(var x=16;x<80;x++){g[x]=rotl(g[x-3]^g[x-8]^g[x-14]^g[x-16],1)}G=w;F=v;E=u;D=r;C=q;for(var x=0;x<80;x++){var y=Math.floor(x/20);var k=(rotl(G,5)+f(y,F,E,D)+C+p[y]+g[x])&4294967295;C=D;D=E;E=rotl(F,30);F=G;G=k}w=(w+G)&4294967295;v=(v+F)&4294967295;u=(u+E)&4294967295;r=(r+D)&4294967295;q=(q+C)&4294967295}return tohs(w)+tohs(v)+tohs(u)+tohs(r)+tohs(q)};';
		
		$oScript = new COM("MSScriptControl.ScriptControl"); 
		$oScript->Language = "JavaScript"; 
		$oScript->AllowUI = false; 
		$oScript->AddCode("var key = '$key';"); 
		$oScript->AddCode($js); 
		$enc_pwd = $oScript->Run("enp","$pwd"); 
		return $enc_pwd;
	}
}//end class
