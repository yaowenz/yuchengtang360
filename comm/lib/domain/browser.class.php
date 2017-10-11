<?php
//分析用户浏览器信息类 by jay 2011/3/8
//require_once dirname(__FILE__).'/../db/db.class.php'; 
class browser{
	//正值表达式比对解析$_SERVER['HTTP_USER_AGENT']中的字符串 获取访问用户的浏览器的信息
	static function determinebrowser ($Agent) {
		$browseragent="";   //浏览器
		$browserversion=""; //浏览器的版本
		if (ereg('MSIE ([0-9].[0-9]{1,2})',$Agent,$version)) {
			$browserversion=$version[1];
			$browseragent="Internet Explorer";
		} else if (ereg( 'Opera/([0-9]{1,2}.[0-9]{1,2})',$Agent,$version)) {
			$browserversion=$version[1];
			$browseragent="Opera";
		} else if (ereg( 'Firefox/([0-9.]{1,5})',$Agent,$version)) {
			$browserversion=$version[1];
			$browseragent="Firefox";
		} else if (ereg( 'Chrome/([0-9.]{1,3})',$Agent,$version)) {
			$browserversion=$version[1];
			$browseragent="Chrome";
		} else if (ereg( 'Safari/([0-9.]{1,3})',$Agent,$version)) {
			$browseragent="Safari";
			$browserversion="";
		} else {
			$browserversion="";
			$browseragent="Unknown";
		}
		return $browseragent." ".$browserversion;
	}

	// 获取访问用户的操作平台信息
	static function determineplatform ($Agent) {
		$browserplatform=='';
		if (eregi('win',$Agent) && strpos($Agent, '95')) {
			$browserplatform="Windows 95";
		} else if (eregi('win 9x',$Agent) && strpos($Agent, '4.90')) {
			$browserplatform="Windows ME";
		} else if (eregi('win',$Agent) && ereg('98',$Agent)) {
			$browserplatform="Windows 98";
		} else if (eregi('win',$Agent) && eregi('nt 5.0',$Agent)) {
			$browserplatform="Windows 2000";
		} else if (eregi('win',$Agent) && eregi('nt 5.1',$Agent)) {
			$browserplatform="Windows XP";
		} else if (eregi('win',$Agent) && eregi('nt 6.0',$Agent)) {
			$browserplatform="Windows Vista";
		} else if (eregi('win',$Agent) && eregi('nt 6.1',$Agent)) {
			$browserplatform="Windows 7";
		} else if (eregi('win',$Agent) && ereg('32',$Agent)) {
			$browserplatform="Windows 32";
		} else if (eregi('win',$Agent) && eregi('nt',$Agent)) {
			$browserplatform="Windows NT";
		} else if (eregi('Mac OS',$Agent)) {
			$browserplatform="Mac OS";
		} else if (eregi('linux',$Agent)) {
			$browserplatform="Linux";
		} else if (eregi('unix',$Agent)) {
			$browserplatform="Unix";
		} else if (eregi('sun',$Agent) && eregi('os',$Agent)) {
			$browserplatform="SunOS";
		} else if (eregi('ibm',$Agent) && eregi('os',$Agent)) {
			$browserplatform="IBM OS/2";
		} else if (eregi('Mac',$Agent) && eregi('PC',$Agent)) {
			$browserplatform="Macintosh";
		} else if (eregi('PowerPC',$Agent)) {
			$browserplatform="PowerPC";
		} else if (eregi('AIX',$Agent)) {
			$browserplatform="AIX";
		} else if (eregi('HPUX',$Agent)) {
			$browserplatform="HPUX";
		} else if (eregi('NetBSD',$Agent)) {
			$browserplatform="NetBSD";
		} else if (eregi('BSD',$Agent)) {
			$browserplatform="BSD";
		} else if (ereg('OSF1',$Agent)) {
			$browserplatform="OSF1";
		} else if (ereg('IRIX',$Agent)) {
			$browserplatform="IRIX";
		} else if (eregi('FreeBSD',$Agent)) {
			$browserplatform="FreeBSD";
		}
		if ($browserplatform=='') {$browserplatform = "Unknown"; }
		return $browserplatform;
	}
}//end class