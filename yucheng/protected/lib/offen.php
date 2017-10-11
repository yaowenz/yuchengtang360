<?php
class tp_offen
{
	static $url1;
	
	static function make_href($x,$y,$t=0)
	{
		if(!self::$url1) return false;
		$r=preg_replace($x,$y,self::$url1);
		if(!$t) $r=preg_replace('/-t-[\d]/','',$r);
		else  $r=preg_replace('/-t-[\d]/','-t-'.$t,$r);
		$r=preg_replace('/-[^\d-]+-0/','',$r);
		$r=preg_replace('/-n-2/','',$r);
		return $r;
	}

	static function make_href_left($x,$y,$t=0)
	{
		if(!self::$url1) return false;
		$r=self::make_href($x,$y,$t);
		$r=preg_replace('/-s-[^-\.]+/','',$r);
		return $r;
	}
	
}//end class