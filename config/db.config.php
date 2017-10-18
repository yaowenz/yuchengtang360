<?php
/**
* 全站数据库连接配置文件
* 防止重复include本文件 ,请使用 require_once
*
*/
//require_once dirname(__FILE__).'/db.duap.config.php';
//require_once dirname(__FILE__).'/db.user.config.php';
//require_once dirname(__FILE__).'/db.usertsina.config.php';
//require_once dirname(__FILE__).'/tableindex.config.php';
class cm_db_config
{
	static $db_id;
	
	static function get_ini($x,$y='slave',$db_id=null) //$y = 'list' 时返回列表
	{
		if($_SERVER['HTTP_HOST']==TEST_DOMAIN)
		{
			$r['yucheng1']['master'] = array('host'=>'m.360antique.com','port'=>'3306','username'=>'yct1','password'=>'g2_8574jfhcur(y8','dbname'=>'yucheng');
			$r['yucheng1']['slave'] = array('host'=>'master','port'=>'3306','username'=>'root','password'=>'','dbname'=>'yuchengtang_360');
		}
		else
		{
			$r['yucheng1']['master'] = array('host'=>'localhost','port'=>'3306','username'=>'yct1','password'=>'g2_8574jfhcur(y8','dbname'=>'yucheng');
			$r['yucheng1']['slave'] = array('host'=>'localhost','port'=>'3306','username'=>'yct1','password'=>'g2_8574jfhcur(y8','dbname'=>'yucheng');
		}

		if($y=='list') return $r[$x];
		return $r[$x][$y];
	}

}
