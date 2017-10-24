<?php
/*
	入口文件，zf框架
	by zx 2014/8/15
*/
//$_GET['__DEBUG']=1;
//$tt = explode(' ', microtime());$starttime = $tt[1] + $tt[0]; //起始时间
ini_set("magic_quotes_runtime", 0);
date_default_timezone_set("PRC");

//共用库目录
error_reporting(E_ALL);
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);

define('TEST_DOMAIN','360.yuchengtang');

define('COMM_PATH', __DIR__ . '/../../comm');
if($_SERVER['HTTP_HOST']==TEST_DOMAIN) {
	define('MAIN_DOMAIN',TEST_DOMAIN . '/yuchengtang/yucheng/web');
	ini_set('display_errors','on');
}
else
{
	ini_set('display_errors','off');
	define('MAIN_DOMAIN','m.360antique.com');
}


define('DOCUMENT_ROOT',realpath(dirname(__FILE__)));//本项目目录
define("CONFIG_PATH",realpath(DOCUMENT_ROOT."/../protected/config/"));//config目录
require_once (CONFIG_PATH . '/config.global.php');

//zf 配置
	$front=zf_mvc::init_front();
	autoload_switch::off();
/*
	$route = new Zend_Controller_Router_Route(
    ':module/:controller/:action/*',
    array('module' => 'default')
	);
	$router = $front->getRouter();
	$router->addRoute('default', $route);
*/
	
	$route = new Zend_Controller_Router_Route(
		'u/:uid',
		array(
			'uid'       => 1,
			'module' => 'client',
			'controller' => 'u',
			'action'     => 'u'
		),
		array('uid' => '\d+')
	);

	$route2 = new Zend_Controller_Router_Route(
		'search/:search',
		array(
			'search'       => 'gif',
			'module' => 'client',
			'controller' => 'list',
			'action'     => 'l'
		),
		array('search' => '.+')
	);

	$router = $front->getRouter();
	$router->addRoute('u', $route);
	$router->addRoute('search', $route2);

	$front->dispatch();
	
///
//echo "<hr>";$tt = explode(' ', microtime());$endtime = $tt[1] + $tt[0];echo '用时：' . ($endtime - $starttime);
