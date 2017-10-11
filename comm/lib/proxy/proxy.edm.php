<?php
/*
edm 代理相关
by ziv 2011/10/29
*/

require_once dirname(__FILE__).'/proxy.poll.blacklist.php';
class proxy_edm{ 

	static $k='xdsrepstkv12'; //default 用来发送edm
	static $k2='xdsrregtkv3ev2'; //用来注册edm账户
	static $k_bj='xdsrregtkkbjev1'; //北京账户，用来跑adshell等
	static $k_sh='xdsrregtkkshev1'; //上海账户，用来跑adshell等
	static $k_gz='xdsrregtkkgzev1'; //广州账户，用来跑adshell等
}//end class