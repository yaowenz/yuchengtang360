<?php
/*
全站 各分站 根url
*/
class cm_url
{
	static function get($s=null)
	{
		$r['yucheng-top'] = MAIN_DOMAIN;
		$r['yucheng'] = 'http://'.MAIN_DOMAIN;
		$r['yucheng-img'] = 'http://'.MAIN_DOMAIN.'/files';
		$r['yucheng-img-path'] = realpath(COMM_PATH .'/../yucheng/web/files');

		if($s) return $r[$s];
		else return $r;
	}
}
?>
