<?php
//反验证码通用方法   by ziv 2011-7-22
class captcha_identify_general{
	//替换 疑似 数字的英文为数字
	static function replace_abc2n($s)
	{
		if(!$s) return false;
		$s = str_replace("-I-","+",$s);
		$s = str_replace("+-","+",$s);
		$s = str_replace(":2","=",$s);
		$s = str_replace(":?","=?",$s);
		$s = str_replace("=7","=?",$s);
		$s = str_replace(":7","=?",$s); 
		$s = str_replace("<_2","=?",$s); 
		$s = str_replace("2?","=?",$s); 
		$s = preg_replace("/[^\d]{1,1}\?/","=?",$s); 
		$s = str_replace("l","1",$s);   
		$s = str_replace("L","1",$s); 
		$s = str_replace("i","1",$s); 
		$s = str_replace("]","1",$s); 
		$s = str_replace("[","1",$s);  
		$s = str_replace("I","1",$s);
		$s = str_replace("J","1",$s); 
		$s = str_replace("O","0",$s); 
		$s = str_replace("o","0",$s); 
		$s = str_replace("f,","5",$s); 
		$s = str_replace("G","6",$s);
		$s = str_replace("a","8",$s);
		$s = str_replace("s","8",$s);
		$s = str_replace("v","7",$s); 
		$s = str_replace("e","6",$s); 
				
		$s = str_replace(" ","",$s);
		$s=preg_replace('/\n/','',$s);
		$s=preg_replace('/\r/','',$s);
		$s=preg_replace('/\t/','',$s); 
		return $s; 
	}
}//end class