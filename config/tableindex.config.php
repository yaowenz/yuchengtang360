<?php
/**
* 全工作站大表分表索引配置文件
* 防止重复include本文件 ,请使用 require_once
*/

class cm_table_index_cnf
{
	static function get_index_by_pt($db_name) //$pt = 'tsina' 时返回索引列表
	{
		if(!$db_name){
			return false;
		}
		$index = array();
		$index['user_tsina'] = array(
			'171' => array('start'=>0,'end'=>199),
			'172' => array('start'=>200,'end'=>399),
			'173' => array('start'=>400,'end'=>599),
			'174' => array('start'=>600,'end'=>799),
			'175' => array('start'=>800,'end'=>999),
			'176' => array('start'=>1000,'end'=>1199),
			'177' => array('start'=>1200,'end'=>1399),
			'178' => array('start'=>1400,'end'=>1599),
			'179' => array('start'=>1600,'end'=>1799),
			'180' => array('start'=>1800,'end'=>1999)
		);

		//调试用
		//$index['user_tsina']['200']= array('start'=>0,'end'=>1999);
		return $index[$db_name];
	}
} 
 