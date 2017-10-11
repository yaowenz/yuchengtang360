<?php
//判断google、百度机器人 sapphirelyt 2011.8.1

require_once dirname(__FILE__).'/../db/db.class.php';
class htdocs_identification{
	function robot_from(){
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if(stristr($user_agent,'Googlebot')!=''){
			return google;
		}else if(stristr($user_agent,'Baiduspider')!=''){
			return baidu;
		}
		//print_R($_SERVER);
	}
}