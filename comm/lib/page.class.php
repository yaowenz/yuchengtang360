<?php
//各种分页相关 2012/7/22
class cm_page
{
	public $db;
	public function __construct()
	{
		$this->db=db_o::get_single('master');
	}
	
	public function get_pages($a=array())
	{
		if(!$a['page'] || !$a['max']) return false;
		if(!$a['num']) $a['num']=30; //显示多少分页
		
		if($a['page']+floor($a['num']/2)<$a['max']) $begin=$a['page']+floor($a['num']/2);
		else $begin=$a['max'];
		
		for($i=0;$i<$a['num'];$i++)
		{
			$r[]=$begin;
			$begin--;
			if($begin<=0) break;
		}

		return $r;  
	} 

}//end class