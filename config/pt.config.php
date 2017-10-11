<?php
/*
 各平台下标
*/
class cm_pt
{
	static function get($s=null)
	{
		$r['tsina'] = "_tsina"; //新浪
		$r['weibo'] = "_tsina"; //新浪

		if($s) return $r[$s];
		else return $r;
	}
}
?>
