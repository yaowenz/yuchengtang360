<?php
//混淆字符串 相关
//2011/10/28 by ziv

class rand_string_s
{
	//随机取字符组成mail内容
	static function rand_get_con($n,$n2=null){
		if($n2)
		{
			$n2=intval($n2);
			$n=rand($n-$n2,$n+$n2);	
		}

		$s = array();
		$s[] = 'a';
		$s[] = 'b';
		$s[] = 'c';
		$s[] = 'd';
		$s[] = 'e';
		$s[] = 'f';
		$s[] = 'g';
		$s[] = 'h';
		$s[] = 'i';
		$s[] = 'j';
		$s[] = 'k';
		$s[] = 'l';
		$s[] = 'm';
		$s[] = 'n';
		$s[] = 'o';
		$s[] = 'p';
		$s[] = 'q';
		$s[] = 'r';
		$s[] = 's';
		$s[] = 't';
		$s[] = 'u';
		$s[] = 'v';
		$s[] = 'w';
		$s[] = 'x';
		$s[] = 'y';
		$s[] = 'z';
		$s[] = '9';
		$s[] = '8';
		$s[] = '7';
		$s[] = '6';
		$s[] = '5';
		$s[] = '4';
		$s[] = '3';
		$s[] = '2';
		$s[] = '1';
		$s[] = '0';
		$s[] = 'A';
		$s[] = 'B';
		$s[] = 'C';
		$s[] = 'D';
		$s[] = 'E';
		$s[] = 'F';
		$s[] = 'G';
		$s[] = 'H';
		$s[] = 'I';
		$s[] = 'J';
		$s[] = 'K';
		$s[] = 'L';
		$s[] = 'M';
		$s[] = 'N';
		$s[] = 'O';
		$s[] = 'P';
		$s[] = 'Q';
		$s[] = 'R';
		$s[] = 'S';
		
		$str = '';
		for($i=0;$i<$n;$i++){
			$tmp = array_rand($s,1);
			$str .= $s[$tmp];
		}
		return $str;
	}

	static function rand_abc_26($n,$n2=null){
		if($n2)
		{
			$n2=intval($n2);
			$n=rand($n-$n2,$n+$n2);	
		}

		$s = array();
		$s[] = 'a';
		$s[] = 'b';
		$s[] = 'c';
		$s[] = 'd';
		$s[] = 'e';
		$s[] = 'f';
		$s[] = 'g';
		$s[] = 'h';
		$s[] = 'i';
		$s[] = 'j';
		$s[] = 'k';
		$s[] = 'l';
		$s[] = 'm';
		$s[] = 'n';
		$s[] = 'o';
		$s[] = 'p';
		$s[] = 'q';
		$s[] = 'r';
		$s[] = 's';
		$s[] = 't';
		$s[] = 'u';
		$s[] = 'v';
		$s[] = 'w';
		$s[] = 'x';
		$s[] = 'y';
		$s[] = 'z';
			
		$str = '';
		for($i=0;$i<$n;$i++){
			$tmp = array_rand($s,1);
			$str .= $s[$tmp];
		}
		return $str;
	}

}//end class