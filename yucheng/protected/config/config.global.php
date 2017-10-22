<?php
/*
配置 + 初始化文件
by zx 2016/2/2
*/
define('CSS_VERSION','52');
ini_set("include_path",ini_get("include_path").PATH_SEPARATOR.COMM_PATH . '/../frame/');
require_once COMM_PATH . '/../config/url.config.php';

define('LIB_PATH',realpath(DOCUMENT_ROOT . '/../protected/lib'));
//define('FONTS_PATH',realpath(COMM_PATH . '/../fonts'));
define('M_UPLOAD_PATH', cm_url::get('yucheng-img-path'));
define('M_PIC_PATH',realpath(M_UPLOAD_PATH .'/m3d/pic'));
if($_SERVER['HTTP_HOST']==TEST_DOMAIN) {
	define('FILE_URL','http://'.TEST_DOMAIN.'/files');
}else{
	define('FILE_URL',cm_url::get('yucheng-img'));
}
define('M_PIC_URL',FILE_URL .'/m3d/pic');

define('CACHE_BASE_KEY','yc');

define('URL_MAIN_IMG',FILE_URL .'/images');
define('SITE_NAME','御承堂');

define('WX_APPID','wx950b5604c8e0ed5f');
define('WX_APPSEC','85a12f09b629d0b82c87dda8aed5b8f0');

if($_SERVER['HTTP_HOST']==TEST_DOMAIN) {
	define('ADMIN_DOMAIN',TEST_DOMAIN);
}else{
	define('ADMIN_DOMAIN','admin360.yuchengtang.com.cn');
}

//必须 htm 结尾，否则跳首页
if(preg_match("/^\/u\/\d+.*$/i",$_SERVER['REQUEST_URI'])){}
else if(preg_match("/^\/search\/.+.*$/i",$_SERVER['REQUEST_URI'])){}
else if(strpos($_SERVER['REQUEST_URI'],'.htm')===false && $_SERVER['REQUEST_URI']!='/')
{
	//header("HTTP/1.0 404 Not Found");
	//echo "<script>location=\"http://".MAIN_DOMAIN."\"</script>";
	//exit;
}
require_once COMM_PATH . '/../config/cache.config.php';
ini_set('session.name','yctsession');

//memecached session
//$t_memcache=cm_cache_config::get('MEMCACHE_SERVER_SINGLE');
//ini_set('session.save_handler','memcache');
//ini_set("session.save_path",$t_memcache[0][0].':'.$t_memcache[0][1]); $t_memcache=null;
///

//ini_set('session.gc_maxlifetime','172800');//默认memcache里存放2天

require_once COMM_PATH . '/lib/ip/ip.class.php';
require_once LIB_PATH . '/admin/admin.user.class.php';
require_once COMM_PATH . '/lib/user/user.class.php';
require_once COMM_PATH . '/comm.functions.php';

class global_info{
	static $vs=1;//版本号 控制 etag
	static $p_key='yct';//平台关键字，重要比如 qq kaixing001 sina_microblog 等
	static $p_id;//平台id
	static $client_cache_time=300; //客户端缓存5分钟
	
	static $ip;
	static $mvc_path;//mvc基准目录
	static $v_path_client; //view目录
	static $v_path_admin;
	static $v_path_webservice;
	static $cm_cache_time=3600; //通用cache过期时间

	static $self_url_requests;
	static $self_url;
	static $file_path;
	
	//负载均衡
	static function get_img_path($s,$add='/upload/main_img'){
		if(!$s) return false;
		$mp = array();
		if($_SERVER['HTTP_HOST']==TEST_DOMAIN) $t1='.'.TEST_DOMAIN.$add;
		else $t1='.'.MAIN_DOMAIN.$add;
		$mp[] = 'http://img'.$t1;
		$mp[] = 'http://img2'.$t1;
		if(!$mp) return false;
		$s=md5($s);
		$hash = self::_time33Hash($s);
		$count=count($mp);
		$rt = abs(fmod($hash,$count));
		return $mp[$rt];
	}
 
	static function _time33Hash($str) {
        $hash = 0;
        $n = strlen($str);
        for ($i = 0; $i <$n; $i++) {
            $hash += ($hash <<5 ) + ord($str[$i]);
        }
        return $hash;
    }
}

global_info::$ip=ip_class::get_ip();
global_info::$mvc_path=realpath(DOCUMENT_ROOT."/../protected/mvc");

global_info::$self_url_requests=$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
global_info::$self_url=preg_replace("/(^[^\?]+)(\?.*)/i", "$1", global_info::$self_url_requests);
global_info::$file_path=realpath(DOCUMENT_ROOT . '/../files');
 
session_set_cookie_params(99999999,'/');
session_start();
require_once CONFIG_PATH . '/action.global.php';

//ini_set('mbstring.http_input','auto');
//ini_set('mbstring.detect_order','auto');
//ini_set('mbstring.internal_encoding','utf-8');
//ini_set('mbstring.http_output','utf-8');

class zf_mvc{
	static function get_front()
	{
		require_once 'Zend/Loader/Autoloader.php';Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);//新版本
		$f = Zend_Controller_Front::getInstance();
		return $f;
	}
	static function mvc_front($f)
	{
		$f->addControllerDirectory(global_info::$mvc_path.'/admin/c/','admin');
		$f->addControllerDirectory(global_info::$mvc_path.'/client/c/','client');
		$f->setDefaultModule('client');
		
		global_info::$v_path_admin = global_info::$mvc_path.'/admin/v';
		global_info::$v_path_client = global_info::$mvc_path.'/client/v';

		return $f;
	}
	static function init_front()
	{
		$f = self::get_front();
		$f = self::mvc_front($f);
		
		if(strtolower(ini_get('display_errors'))=='on') $noErrorHandler=false;else $noErrorHandler=true;
		$f->setParam('noErrorHandler', $noErrorHandler);//不显示错误
		$f->setParam('noViewRenderer', false);//禁止自动分配模板
		$f->setParam("prefixDefaultModule",true);		// 模块需要增加namespacing
		$f->setParam("useDefaultControllerAlways",false);

		$f->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(array(
			'module'     => 'client',
			'controller' => 'Error',
			'action'     => 'error'
		)));
		return $f;
	}
}

//停止自动加载类
class autoload_switch{
	static function on(){
		//Zend_Loader::registerAutoload('Zend_Loader',true); //老版本
		Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);
	}

	static function off(){
		//Zend_Loader::registerAutoload('Zend_Loader',false);
		Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(false);
	}
}
///

@header("content-type:text/html;charset=utf-8");
