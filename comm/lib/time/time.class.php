<?php
//by ziv 2011/3/22
class time_class{

	static function s2dhis($s){ //时间差 秒转换成天时分秒
		if(!is_numeric($s)) return false;
		$r['d']=floor($s/86400);
		$r['h']=floor($s/3600)-$r['d']*24;
		$r['i']=floor($s/60)-$r['d']*24*60-$r['h']*60;
		$r['s']=$s-$r['d']*24*3600-$r['h']*3600-$r['i']*60;
		return $r;
	}
	
	static function get_day_between($t){ 
		if(!is_numeric($t)) return false;
		$t+=3600*8;
		$r['from']=$t-$t%86400-3600*8;
		$r['to']=$r['from']+3600*24;
		return $r;
	}
}
?>