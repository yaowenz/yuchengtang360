<?php
/*
weibo 常用
2011/10/21 by ziv
*/

class t_sina_comm
{
	static function not_need_adsl_proxy() //如果是工作站模式，就不需要用adsl代理发curl
	{
		$computername=strtolower($_SERVER['COMPUTERNAME']);
		$a1=null;
		$a1[]='tsina1';
		$a1[]='tsina2';
		$a1[]='tsina3';
		$a1[]='tsina4';
		$a1[]='tsina5';
		$a1[]='tsina6';
		$a1[]='tsina7';
		$a1[]='tsina8';
		$a1[]='tsina9';
		$a1[]='tsina10';
		if(in_array($computername, $a1, true)) 
		{
			$_GET['__PROXY'] = 1;
			return true;
		}
		else return true; 
	}
}//end class