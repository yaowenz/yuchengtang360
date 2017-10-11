<?php

//by sapphire 2011-10-8
class edm_article{
	static function ele_rand($a=null) 
	{ 
		$pattern='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
		$b_nub=$pattern{mt_rand(0,26)}.rand(1,1000000);
		$sx1 = self::sx_rand();
		$sx2 = self::sx_rand();
		if(!$a)
		{
			$a[]="<b ".$sx1."='$b_nub'></b>"; 
			$a[]="<h ".$sx2."='$b_nub'></h>"; 
		} 
		$c=count($a);
		$k=rand(0,$c-1);
		return $a[$k];     
	} 
	static function br_rand($a=null) 
	{ 
		if(!$a) 
		{
			$a[]="\r\n";$a[]="\r\n";$a[]="\r\n";$a[]="\r\n";$a[]="\r\n";$a[]="\r\n";$a[]="\r\n";$a[]="\r\n";$a[]="\r\n";$a[]="\r\n";$a[]="\r\n";$a[]="\r\n";$a[]="\r\n";$a[]="\r\n";$a[]="\r\n";
			$a[]=' ';
			$a[]='  ';
			$a[]='   ';
			$a[]='    ';
			$a[]='     ';
			$a[]='      ';
			$a[]='       ';
			$a[]='        ';
			$a[]='         ';
			$a[]='          ';
		} 
		$c=count($a);
		$k=rand(0,$c-1);
		return $a[$k];     
	}

	static function txt_rand($a=null) 
	{ 
		if(!$a) 
		{
			$a[]='come';
			$a[]='go';
			$a[]='let';
			$a[]='us';
			$a[]='go';
			$a[]='we';
			$a[]='me';
			$a[]='you';
			$a[]='well';
		} 
		$c=count($a);
		$k=rand(0,$c-1);
		return $a[$k];     
	}

	static function sx_rand($a=null) 
	{ 
		if(!$a) 
		{
			$a[]='id';
			$a[]='class';
			$a[]='name';
		} 
		$c=count($a);
		$k=rand(0,$c-1);
		return $a[$k];     
	}

	static function color_rand($a=null) 
	{ 
		if(!$a) 
		{
			$a[]='#c20b0d';
			$a[]='#014e9d';
			$a[]='#e0a300';
		} 
		$c=count($a);
		$k=rand(0,$c-1);
		return $a[$k];     
	}

	static function b_rand(){
		$pattern='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
		return $pattern{mt_rand(0,26)}.rand(1,1000000);
	}

	static function edm_rand($img_url = null,$img_a = null,$p_txt = null,$para){ 
		$txt = self::txt_rand();
		$color = self::color_rand();
		
		$str =  "<table width='200' border='0' ".self::sx_rand()."='".self::b_rand()."'>".self::br_rand().
				"<tr ".self::sx_rand()."='".self::b_rand()."'>".self::br_rand().
					"<td ".self::sx_rand()."='".self::b_rand()."'>".self::br_rand().self::ele_rand().
						"<a href='".$img_a."' ".self::sx_rand()."='".self::b_rand()."'>".self::br_rand();
		
		$i = 2;
		foreach($img_url as $file_name){
			//"<img src='/js4/s?func=mbox:getComposeData&amp;sid=".$para['sid']."&amp;composeId=c:".$para['time']."&amp;attachId=1' ".$sx6."='".$b9."' border='0' style='display:block;' />".
				$str .= '<img src="s?func=mbox:getComposeData&amp;sid='.$para['sid'].'&amp;composeId=c:'.$para['time'].'&amp;attachId='.$i.'&amp;rnd=0.0'.rand(1000000000000000,9999999999999999).'"'.self::sx_rand().'="'.self::b_rand().'" border="0" style="display:block;" />';
			$i++;
		}
		$str .= self::br_rand()."</a>" . self::br_rand()."</td>".self::br_rand()."</tr>".self::br_rand();

		if($p_txt){
			$str .= "<tr ".self::sx_rand()."='".self::b_rand()."'>".self::br_rand().
						"<td ".self::sx_rand()."='".self::br_rand()."'>".self::br_rand().self::ele_rand().self::br_rand().
							"<a ".self::sx_rand()."='".self::b_rand()."' style='color:#fff;'>".$p_txt."</a>".self::br_rand().
						"</td>".
					"</tr>";
		}
		$str .= "</table>";
		return $str;
	}

	//标题插入随机符
	static function title_rand($s,$a=null){ 
		$charset='utf-8'; 
		$r=mb_strlen($s,$charset);
		$num=0;          //获取数组起始位置
		$x=0;            //数组起始
		while($num < $r)
		{
			if(!$a) 
			{
				$a[]='·'; 
				$a[]='.'; 
				$a[]='۰';
				$a[]='▪'; 
				$a[]='▫';
				$a[]='◊';
				$a[]='▲'; 
				$a[]='♦';
				$a[]='×'; 
				$a[]='●';
				$a[]='˙';
				$a[]='ˇ';
				$a[]='ˊ';
				$a[]='ˉ';
				$a[]='▼';
			} 
			$c=count($a);
			$k=rand(0,$c-1);

			$test[$x]=mb_substr($s,$num,1,$charset);
			$tit.= $test[$x].$a[$k];
			$num=$num+1;
			++$x;
			
		}
		return $tit;
		
	}
	
	//生成用来测试的常用的，用户可能使用的账户 
	static function get_test_uname()
	{
		$rand1=rand(1,1);
		$fun='get_test_uname_'.$rand1;
		self::$fun();
	}
	
	//生成器1
	static function get_test_uname_1()
	{
		die('1');
	}

}//end class
?>