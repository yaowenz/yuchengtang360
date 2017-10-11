<?php
/*
* by zx 2013/2/26
*/
class Admin_MatchController extends Admin_Controller_Action
{
	public function shuangtvAction(){
	
		require_once LIB_PATH . '/db.class.php';
		$db=db_o::get_single('master');
		
		if($_GET['mtime_id'] && $_GET['none']==1)
		{
			$sql="update tq_match_shuangtv_mtime set state=-1 where mtime_id=".intval($_GET['mtime_id']);
			$db->query($sql);
			$this->view->res1='上一部电影全部不匹配！';
		}
		else if($_GET['mtime_id'] && $_GET['match'])
		{
			if(count($_GET['match'])>3) $this->view->res1='最多匹配3个!';
			else{
				$set='';
				if($_GET['match'][0]) $set.='shuangtv_id_1='.intval($_GET['match'][0]);
				if($_GET['match'][1]) $set.=',shuangtv_id_2='.intval($_GET['match'][1]);
				if($_GET['match'][2]) $set.=',shuangtv_id_3='.intval($_GET['match'][2]);
				$sql="update tq_video_main_index set ".$set." where mtime_id=".intval($_GET['mtime_id']);
				$db->query($sql);
				$this->view->res1='上一部电影匹配成功！';
			}
		}

		$sql="select count(1) from (select mtime_id from tq_match_shuangtv_mtime where state=1 group by mtime_id having count(1)>1) as b join tq_video_main_index c on b.mtime_id=c.mtime_id where c.shuangtv_id_1=0";
		$this->view->cnt=$db->fetchOne($sql);

		$sql="select a.mtime_id,a.shuangtv_id,f.video_uri,f.img as shuayc_img from tq_match_shuangtv_mtime a join (	select c.mtime_id from (select mtime_id from tq_match_shuangtv_mtime where state=1 group by mtime_id having count(1)>1	) as b join tq_video_main_index c on b.mtime_id=c.mtime_id where c.shuangtv_id_1=0 limit 1) as d on a.mtime_id=d.mtime_id join spider_shuangtv_movie_info f on a.shuangtv_id=f.video_id where a.state=1";
		$this->view->r=$db->fetchAll($sql);
		
		if($this->view->r) foreach($this->view->r as $k=>$i)
		{
			$sql="select `name` from spider_mtime_movie_name where movie_id=".$i['mtime_id'];
			$this->view->r[$k]['mnames']=$db->fetchAll($sql);

			$sql="select * from spider_mtime_movie_shangyin where movie_id=".$i['mtime_id'];
			$this->view->r[$k]['shangyin']=$db->fetchAll($sql);

			$sql="select * from spider_mtime_movie_zhuyan where movie_id=".$i['mtime_id'];
			$this->view->r[$k]['zhuyan']=$db->fetchAll($sql);

			$sql="select `name` from spider_shuangtv_movie_name where video_id=".$i['shuangtv_id'];
			$this->view->r[$k]['names']=$db->fetchAll($sql);

			$sql="select * from spider_mtime_movie_info where movie_id=".$i['mtime_id'];
			$this->view->r[$k]['mtime_info']=$db->fetchRow($sql);

			//$sql="select * from spider_shuangtv_movie_info where video_id=".$i['shuangtv_id'];
			//$this->view->r[$k]['shuangtv_info']=$db->fetchRow($sql);
			
			//判断是否匹配过
			$sql="select count(1) from tq_video_main_index where shuangtv_id_1 = ".intval($i['shuangtv_id'])." or shuangtv_id_2 = ".intval($i['shuangtv_id'])." or shuangtv_id_3 = ".intval($i['shuangtv_id']);
			$x=$db->fetchOne($sql);
			if($x>0) $this->view->r[$k]['pi_pei_guo']=1;
		}
	}

	public function shuangtvfanAction()
	{
		require_once LIB_PATH . '/db.class.php';
		$db=db_o::get_single('master');

		//if($_GET['shuangtv_id']) {print_R($_GET);exit;}
		
		if($_GET['shuangtv_id'] && $_GET['none']==1)
		{
			$sql="update tq_video_main_index set shuangtv_id_1=0,shuangtv_id_2=0,shuangtv_id_3=0 where shuangtv_id_1=".intval($_GET['shuangtv_id']);
			$db->query($sql);
			$this->view->res1='上一部电影全部不匹配！';
		}
		else if($_GET['shuangtv_id'] && $_GET['mtime_id_shoudong'])
		{
			if(!is_numeric($_GET['mtime_id_shoudong'])) $this->view->res1='手动输入mtime_id错误!';
			else
			{
				$sql="select mtime_id from tq_video_main_index where shuangtv_id_1=0 and mtime_id = ".intval($_GET['mtime_id_shoudong']);
				$r1=$db->fetchOne($sql);
				if($r1)
				{
					$sql="update tq_video_main_index set shuangtv_id_1=".intval($_GET['shuangtv_id'])." where mtime_id = ".intval($_GET['mtime_id_shoudong']);
					$db->query($sql);
					$sql="update tq_video_main_index set shuangtv_id_1=0,shuangtv_id_2=0,shuangtv_id_3=0 where shuangtv_id_1=".intval($_GET['shuangtv_id'])." and mtime_id !=".intval($_GET['mtime_id_shoudong']);
					$db->query($sql);
					$this->view->res1='上一部电影手动匹配成功！';
				}
				else
				{
					$sql="select mtime_id from tq_video_main_index where shuangtv_id_2=0 and mtime_id = ".intval($_GET['mtime_id_shoudong']);
					$r1=$db->fetchOne($sql);
					if($r1)
					{
						$sql="update tq_video_main_index set shuangtv_id_2=".intval($_GET['shuangtv_id'])." where mtime_id = ".intval($_GET['mtime_id_shoudong']);
						$db->query($sql);
						$sql="update tq_video_main_index set shuangtv_id_1=0,shuangtv_id_2=0,shuangtv_id_3=0 where shuangtv_id_1=".intval($_GET['shuangtv_id'])." and mtime_id !=".intval($_GET['mtime_id_shoudong']);
						$db->query($sql);
						$this->view->res1='上一部电影手动匹配成功！';
					}
					else
					{
						$sql="select mtime_id from tq_video_main_index where shuangtv_id_3=0 and mtime_id = ".intval($_GET['mtime_id_shoudong']);
						$r1=$db->fetchOne($sql);
						if($r1)
						{
							$sql="update tq_video_main_index set shuangtv_id_3=".intval($_GET['shuangtv_id'])." where mtime_id = ".intval($_GET['mtime_id_shoudong']);
							$db->query($sql);
							$sql="update tq_video_main_index set shuangtv_id_1=0,shuangtv_id_2=0,shuangtv_id_3=0 where shuangtv_id_1=".intval($_GET['shuangtv_id'])." and mtime_id !=".intval($_GET['mtime_id_shoudong']);
							$db->query($sql);
							$this->view->res1='上一部电影手动匹配成功！';
						}
						else
						{
							$this->view->res1='失败！可能该电影mtime上还没有抓取，或者已经匹配了3部双视电影了。';
						}
					}
				}
			}
		}
		else if($_GET['shuangtv_id'] && $_GET['match'])
		{
			$sql="update tq_video_main_index set shuangtv_id_1=0,shuangtv_id_2=0,shuangtv_id_3=0 where shuangtv_id_1=".intval($_GET['shuangtv_id'])." and mtime_id !=".intval($_GET['match']);
			$db->query($sql);
			$this->view->res1='上一部电影匹配成功！';
		}

		$sql="select count(1) from (select shuangtv_id_1 from tq_video_main_index where shuangtv_id_1 !=0 group by shuangtv_id_1 having count(1)>1 ) as a";
		$this->view->cnt=$db->fetchOne($sql);
		
		$sql="select a.mtime_id,a.shuangtv_id_1,b.video_uri,b.img as shuayc_img from tq_video_main_index a join spider_shuangtv_movie_info b on a.shuangtv_id_1=b.video_id join (select shuangtv_id_1 from (select shuangtv_id_1 from tq_video_main_index where shuangtv_id_1 !=0 group by shuangtv_id_1 having count(1)>1) as c limit 1) as d on a.shuangtv_id_1=d.shuangtv_id_1";
		$this->view->r=$db->fetchAll($sql);
		
		if($this->view->r) foreach($this->view->r as $k=>$i)
		{
			$sql="select `name` from spider_mtime_movie_name where movie_id=".$i['mtime_id'];
			$this->view->r[$k]['mnames']=$db->fetchAll($sql);

			$sql="select * from spider_mtime_movie_shangyin where movie_id=".$i['mtime_id'];
			$this->view->r[$k]['shangyin']=$db->fetchAll($sql);

			$sql="select * from spider_mtime_movie_zhuyan where movie_id=".$i['mtime_id'];
			$this->view->r[$k]['zhuyan']=$db->fetchAll($sql);

			$sql="select `name` from spider_shuangtv_movie_name where video_id=".$i['shuangtv_id_1'];
			$this->view->r[$k]['names']=$db->fetchAll($sql);

			$sql="select * from spider_mtime_movie_info where movie_id=".$i['mtime_id'];
			$this->view->r[$k]['mtime_info']=$db->fetchRow($sql);

			//$sql="select * from spider_shuangtv_movie_info where video_id=".$i['shuangtv_id_1'];
			//$this->view->r[$k]['shuangtv_info']=$db->fetchRow($sql);
			
		}

		//print_R($this->view->r);exit;
	}

	public function doubanAction(){
	
		require_once LIB_PATH . '/db.class.php';
		$db=db_o::get_single('master');
		
		if($_GET['mtime_id'] && $_GET['none']==1)
		{
			$sql="update tq_match_douban_mtime set state=-1 where mtime_id=".intval($_GET['mtime_id']);
			$db->query($sql);
			$this->view->res1='上一部电影全部不匹配！';
		}
		else if($_GET['mtime_id'] && $_GET['match'])
		{
			if(count($_GET['match'])>1) $this->view->res1='最多匹配1个!';
			else{
				if($_GET['match'][0]) $set='douban_id='.intval($_GET['match'][0]);
				$sql="update tq_video_main_index set ".$set." where mtime_id=".intval($_GET['mtime_id']);
				$db->query($sql);
				$this->view->res1='上一部电影匹配成功！';
			}
		}

		$sql="select count(1) from (select mtime_id from tq_match_douban_mtime where state=1 group by mtime_id having count(1)>1) as b join tq_video_main_index c on b.mtime_id=c.mtime_id where c.douban_id=0";
		$this->view->cnt=$db->fetchOne($sql);

		$sql="select a.mtime_id,a.movie_id,f.img_path,f.img_base from tq_match_douban_mtime a join (	select c.mtime_id from (select mtime_id from tq_match_douban_mtime where state=1 group by mtime_id having count(1)>1) as b join tq_video_main_index c on b.mtime_id=c.mtime_id where c.douban_id=0 limit 1) as d on a.mtime_id=d.mtime_id join spider_douban_movie_info f on a.movie_id=f.movie_id where a.state=1";
		$this->view->r=$db->fetchAll($sql);
		
		if($this->view->r) foreach($this->view->r as $k=>$i)
		{
			$sql="select `name` from spider_mtime_movie_name where movie_id=".$i['mtime_id'];
			$this->view->r[$k]['mnames']=$db->fetchAll($sql);

			$sql="select * from spider_mtime_movie_shangyin where movie_id=".$i['mtime_id'];
			$this->view->r[$k]['shangyin']=$db->fetchAll($sql);

			$sql="select * from spider_mtime_movie_zhuyan where movie_id=".$i['mtime_id'];
			$this->view->r[$k]['zhuyan']=$db->fetchAll($sql);

			$sql="select `name` from spider_douban_movie_name where movie_id=".$i['movie_id'];
			$this->view->r[$k]['names']=$db->fetchAll($sql);

			$sql="select * from spider_mtime_movie_info where movie_id=".$i['mtime_id'];
			$this->view->r[$k]['mtime_info']=$db->fetchRow($sql);

			//$sql="select * from spider_douban_movie_info where video_id=".$i['douban_id'];
			//$this->view->r[$k]['douban_info']=$db->fetchRow($sql);
			
			$sql="select `zhuyan_name` from spider_douban_movie_zhuyan where movie_id=".$i['movie_id'];
			$this->view->r[$k]['douban_zhuyan']=$db->fetchAll($sql);

			$sql="select * from spider_douban_movie_shangyin where movie_id=".$i['movie_id'];
			$this->view->r[$k]['douban_shangyin']=$db->fetchAll($sql);
						
			//判断是否匹配过
			$sql="select count(1) from tq_video_main_index where douban_id = ".intval($i['movie_id']);
			$x=$db->fetchOne($sql);
			if($x>0) $this->view->r[$k]['pi_pei_guo']=1;
		}
		//print_R($this->view->r);exit;
	}

	public function doubanfanAction()
	{
		require_once LIB_PATH . '/db.class.php';
		$db=db_o::get_single('master');

		//if($_GET['douban_id']) {print_R($_GET);exit;}
		
		if($_GET['douban_id'] && $_GET['none']==1)
		{
			$sql="update tq_video_main_index set douban_id=0 where douban_id=".intval($_GET['douban_id']);
			$db->query($sql);
			$this->view->res1='上一部电影全部不匹配！';
		}
		else if($_GET['douban_id'] && $_GET['mtime_id_shoudong'])
		{
			if(!is_numeric($_GET['mtime_id_shoudong'])) $this->view->res1='手动输入mtime_id错误!';
			else
			{
				$sql="select mtime_id from tq_video_main_index where douban_id=0 and mtime_id = ".intval($_GET['mtime_id_shoudong']);
				$r1=$db->fetchOne($sql);
				if($r1)
				{
					$sql="update tq_video_main_index set douban_id=".intval($_GET['douban_id'])." where mtime_id = ".intval($_GET['mtime_id_shoudong']);
					$db->query($sql);
					$sql="update tq_video_main_index set douban_id=0 where douban_id=".intval($_GET['douban_id'])." and mtime_id !=".intval($_GET['mtime_id_shoudong']);
					$db->query($sql);
					$this->view->res1='上一部电影手动匹配成功！';
				}
				else
				{
					$this->view->res1='失败！可能该电影mtime上还没有抓取。';
				}
			}
		}
		else if($_GET['douban_id'] && $_GET['match'])
		{
			$sql="update tq_video_main_index set douban_id=0 where douban_id=".intval($_GET['douban_id'])." and mtime_id !=".intval($_GET['match']);
			$db->query($sql);
			$this->view->res1='上一部电影匹配成功！';
		}

		$sql="select count(1) from (select douban_id from tq_video_main_index where douban_id !=0 group by douban_id having count(1)>1 ) as a";
		$this->view->cnt=$db->fetchOne($sql);
		
		$sql="select a.mtime_id,a.douban_id as movie_id,b.img_path,b.img_base from tq_video_main_index a join spider_douban_movie_info b on a.douban_id=b.movie_id join (select douban_id from (select douban_id from tq_video_main_index where douban_id !=0 group by douban_id having count(1)>1) as c limit 1) as d on a.douban_id=d.douban_id";
		$this->view->r=$db->fetchAll($sql);
		
		if($this->view->r) foreach($this->view->r as $k=>$i)
		{
			$sql="select `name` from spider_mtime_movie_name where movie_id=".$i['mtime_id'];
			$this->view->r[$k]['mnames']=$db->fetchAll($sql);

			$sql="select * from spider_mtime_movie_shangyin where movie_id=".$i['mtime_id'];
			$this->view->r[$k]['shangyin']=$db->fetchAll($sql);

			$sql="select * from spider_mtime_movie_zhuyan where movie_id=".$i['mtime_id'];
			$this->view->r[$k]['zhuyan']=$db->fetchAll($sql);

			$sql="select `name` from spider_douban_movie_name where movie_id=".$i['movie_id'];
			$this->view->r[$k]['names']=$db->fetchAll($sql);

			$sql="select * from spider_mtime_movie_info where movie_id=".$i['mtime_id'];
			$this->view->r[$k]['mtime_info']=$db->fetchRow($sql);

			//$sql="select * from spider_douban_movie_info where video_id=".$i['douban_id'];
			//$this->view->r[$k]['douban_info']=$db->fetchRow($sql);
			
			$sql="select `zhuyan_name` from spider_douban_movie_zhuyan where movie_id=".$i['movie_id'];
			$this->view->r[$k]['douban_zhuyan']=$db->fetchAll($sql);
		}

		//print_R($this->view->r);exit;
	}

}