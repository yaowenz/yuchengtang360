<?php
class cm_article{

	//取随机特殊符号
	static function rand_spc($a=null) 
	{ 
		if(!$a) 
		{
			$a[]='☻'; 
			$a[]='☺'; 
			$a[]='℡';
			$a[]=' '; 
			$a[]='　';
			$a[]='☆';
			$a[]='๑'; 
			$a[]='™';
			$a[]='ღ'; 
		} 
		$c=count($a);
		$k=rand(0,$c-1);
		return $a[$k];     
	} 



	static function rand_article($s,$n = 3){ 
		$s=trim($s).' ';
		$charset='utf-8'; 
		$r=mb_strlen($s,$charset);
		if(strpos($s, "http://")){ 
			$url_b =$r - mb_strlen(strstr($s,"http://"),$charset);//链接位置 如 49
			$url = '/http(s)?:\/\/.*[ ]/iU';
			preg_match($url,$s,$match);			//echo'<br/>';echo ' is: ';print_R($match);echo '<br/>';  
			$s_nl = preg_replace($url,'',$s);	//echo'<br/>';echo ' is: ';print_R($s_nl);echo '<br/>';  
			$r_nl = mb_strlen($s_nl,$charset);  
			$r1=ceil($r_nl*0.1);  
			$r2=floor($r_nl*0.9);  
			for($i=0;$i<$n;$i++){
				$ra=rand($r1,$r2);
				if($ra<$url_b){
					$url_b = $url_b+1;
				}
				$s1=mb_substr($s_nl,0,$ra,$charset); $s2=mb_substr($s_nl,$ra,$r+$i,$charset);  
				$spc=self::rand_spc();
				$s_nl=$s1.$spc.$s2;
			}
			$r=mb_strlen($s_nl,$charset);
			$s1=mb_substr($s_nl,0,$url_b,$charset); $s2=mb_substr($s_nl,$url_b,$r,$charset); 
			$rall = $s1.$match[0].$s2;
			return trim($rall);
		}else{ 
			$r1=ceil($r*0.1);  
			$r2=floor($r*0.9);
			for($i=0;$i<$n;$i++){
				$ra=rand($r1,$r2);
				$s1=mb_substr($s,0,$ra,$charset); $s2=mb_substr($s,$ra,$r+$i,$charset); 
				$spc=self::rand_spc();
				$s=$s1.$spc.$s2; 
			}
			return trim($s);   
		} 
	}

}//end class
?>