<?php
require_once LIB_PATH . '/html.class.php';
require_once LIB_PATH . '/db.class.php';

class Admin_HtmlController extends Admin_Controller_Action
{
	//添加帖子
	public function createAction()
	{
		$p=$_POST;
		if($p['html_name'])
		{
			$o=new yc_html_class;
			$r=$o->create_html($p['html_name']);
			if($r)
			{
				require_once COMM_PATH .'/lib/string/str10_62.class.php';
				$o1=new Base62;
				$this->view->res1='<br/>添加成功<br/><br/><a href="/v-d-i-'.$o1->base62_encode($r).'.htm" style="color:blue" target=_blank>立即去编辑：'.$p['html_name'].'</a><br/><br/>';
			}
		}
	}

	//最近添加帖子列表
	public function createlistAction()
	{
		$db=db_o::get_single('master');
		$sql="select *,(select `name` from yc_html_name where html_id=a.id order by sort desc limit 1) as name from yc_html_main_index a where state=-2 order by id desc limit 100";
		$r=$db->fetchAll($sql);

		require_once COMM_PATH .'/lib/string/str10_62.class.php';
		$o1=new Base62;
			
		foreach($r as $k=>$i)
		{
			$r[$k]['id2']=$o1->base62_encode($i['id']);
		}
		$this->view->r=$r;
	}
	
	//删除最近添加帖子
	public function createdelAction()
	{
		$id=intval($_GET['id']);
		if(!$id) die('参数错误!');
 		$db=db_o::get_single('master');
		$sql="delete from yc_html_name where html_id=".$id;
		$db->query($sql);
		$sql="delete from yc_html_main_index where id=".$id." and state=-2";
		$db->query($sql);
		die("<script>window.opener.location=window.opener.location.pathname+'?if_shangjia=1';window.opener=null;window.open('','_self');window.close();</script>");
	}
	
	public function editAction()
	{
		$o=new yc_html_class;
		
		$html1=$_REQUEST['edit1'];
		$key1=$_REQUEST['key1'];
		
		if($html1 && $key1)
		{
			$o->update_html_by_key($key1,$html1);
		}

		if($key1)
		{
			$this->view->info=$o->get_html_by_key($key1);
		}
	}
	
	public function imgsAction()
	{
		$o=new yc_html_class;
		if($_FILES['img1'])
		{
			$this->view->info=$o->upload_img('img1');
			//print_R($this->view->info);exit;
			if($this->view->info)
			{
				$this->view->res1='<br/><br/>图片上传成功 ：<br/><br/>  '.URL_MAIN_IMG.'/'.$this->view->info.' <br/><br/><a href="'.URL_MAIN_IMG.'/'.$this->view->info.'" target=_blank>点击查看图片</a><br/><br/>';
			}
			else
			{
				$this->view->res1='图片上传失败，请联系管理员。';	
			}
		}
	}
	
	public function playtongbu()
	{
		$o=new yc_html_class;
		$o->playtongbu();
	}

	public function playtongbuAction()
	{
		$this->playtongbu();
		die("<script>window.opener.location=window.opener.location.pathname+'?if_shangjia=1';window.opener=null;window.open('','_self');window.close();</script>");
	}

	public function playaddAction()
	{
		$g=$_GET;
		if(!is_numeric($g['tq'])) die('参数错误');
		$this->playtongbu();
		
		$db=db_o::get_single('master');

		$sql="select * from tq_play_key_list where html_id=".intval($g['tq'])." order by series desc,episode desc limit 1";
		$this->view->d=$db->fetchRow($sql);
		//print_R($this->view->d);exit;

		$p=$_POST;
		if($p)
		{
			if(!$p['episode_name'] || !$p['video_key'] || !is_numeric($p['series']) || !is_numeric($p['episode'])) die('参数不全');
			if($p['episode']<10000) die('新增的编号不能小于10000');

			$a=null;
			$a['html_id']=$g['tq'];
			$a['episode']=$p['episode'];
			$a['series']=$p['series'];
			$a['episode_name']=$p['episode_name'];
			$a['video_key']=$p['video_key'];
			try{
				$db->insert('tq_play_key_list', $a);
			}catch(Exception $e){
				$this->view->res1='失败! 可能冲突。';
			}
			if(!$this->view->res1) die("<script>window.opener.location=window.opener.location.pathname+'?if_shangjia=1';window.opener=null;window.open('','_self');window.close();</script>");
		}
	}

	public function playlinkaddAction()
	{
		$g=$_GET;
		if(!is_numeric($g['tq'])) die('参数错误');
		$this->playtongbu();
		
		$db=db_o::get_single('master');

		$sql="select series,episode from tq_play_link_list where html_id=".intval($g['tq'])." order by series desc,episode desc limit 1";
		$this->view->d=$db->fetchRow($sql);
		//print_R($this->view->d);exit;

		$p=$_POST;
		if($p)
		{
			if(!is_numeric(strtotime($p['onlinetime'])) || !$p['url'] || !is_numeric($p['series']) || !is_numeric($p['episode'])) die('参数不全或错误');

			$a=null;
			$a['html_id']=$g['tq'];
			$a['episode']=$p['episode'];
			$a['series']=$p['series'];
			if($p['title']) $a['title']=$p['title'];
			$a['url']=$p['url'];
			if($p['type']) $a['type']=$p['type'];
			if($p['duration']) $a['duration']=$p['duration'];
			$a['onlinetime']=strtotime($p['onlinetime']);
			if($p['site_logo']) $a['site_logo']=$p['site_logo'];
			if($p['site_name']) $a['site_name']=$p['site_name'];
			try{
				$db->insert('tq_play_link_list', $a);
			}catch(Exception $e){
				$this->view->res1='失败! 可能冲突。';
			}
			if(!$this->view->res1) die("<script>window.opener.location=window.opener.location.pathname+'?if_shangjia=1';window.opener=null;window.open('','_self');window.close();</script>");
		}
	}

	public function playextaddAction()
	{
		$g=$_GET;
		if(!is_numeric($g['tq'])) die('参数错误');
		$this->playtongbu();
		
		$db=db_o::get_single('master');

		$sql="select series,episode from tq_play_ext_list where html_id=".intval($g['tq'])." order by series desc,episode desc limit 1";
		$this->view->d=$db->fetchRow($sql);
		//print_R($this->view->d);exit;

		$p=$_POST;
		if($p)
		{
			if(!is_numeric(strtotime($p['onlinetime'])) || !$p['url'] || !is_numeric($p['series']) || !is_numeric($p['episode'])) die('参数不全或错误');

			$a=null;
			$a['html_id']=$g['tq'];
			$a['episode']=$p['episode'];
			$a['series']=$p['series'];
			if($p['title']) $a['title']=$p['title'];
			$a['url']=$p['url'];
			if($p['type']) $a['type']=$p['type'];
			if($p['duration']) $a['duration']=$p['duration'];
			$a['onlinetime']=strtotime($p['onlinetime']);
			if($p['site_logo']) $a['site_logo']=$p['site_logo'];
			if($p['site_name']) $a['site_name']=$p['site_name'];
			try{
				$db->insert('tq_play_ext_list', $a);
			}catch(Exception $e){
				$this->view->res1='失败! 可能冲突。';
			}
			if(!$this->view->res1) die("<script>window.opener.location=window.opener.location.pathname+'?if_shangjia=1';window.opener=null;window.open('','_self');window.close();</script>");
		}
	}
	
	public function playeditAction()
	{
		$g=$_GET;
		if(!is_numeric($g['tq']) || !is_numeric($g['s']) || !is_numeric($g['e'])) die('参数错误');
		$this->playtongbu();
		
		$db=db_o::get_single('master');

		$p=$_POST;
		if($p && $p['episode_name'] && $p['video_key'])
		{
			$a=null;
			$a['episode']=$p['episode'];
			$a['series']=$p['series'];
			$a['episode_name']=$p['episode_name'];
			$a['video_key']=$p['video_key'];
			$where = $db->quoteInto('html_id = ?',intval($g['tq']));
			$where.= $db->quoteInto(' and series = ?',intval($g['s']));
			$where.= $db->quoteInto(' and episode = ?',intval($g['e']));
			try{
				$db->update('tq_play_key_list',$a,$where);
			}catch(Exception $e){
				$this->view->res1='失败! 可能冲突。';
			} 
			die("<script>window.opener.location=window.opener.location.pathname+'?if_shangjia=1';window.opener=null;window.open('','_self');window.close();</script>");
		}
		else
		{
			$sql="select * from tq_play_key_list where html_id=".intval($g['tq'])." and series=".intval($g['s'])." and episode=".intval($g['e'])." and state=1";
			$this->view->d=$db->fetchRow($sql);
		}
	}

	public function playlinkeditAction()
	{
		$g=$_GET;
		if(!is_numeric($g['tq']) || !is_numeric($g['s']) || !is_numeric($g['e'])) die('参数错误');
		$this->playtongbu();
		
		$db=db_o::get_single('master');

		$p=$_POST;
		if($p && $p['series'] && $p['episode'])
		{
			$a=null;
			$a['episode']=$p['episode'];
			$a['series']=$p['series'];
			$a['title']=$p['title'];
			$a['url']=$p['url'];
			$a['duration']=$p['duration'];
			$a['type']=$p['type'];
			$a['onlinetime']=strtotime(intval($p['onlinetime']));
			$a['site_logo']=$p['site_logo'];
			$a['site_name']=$p['site_name'];
			$where = $db->quoteInto('html_id = ?',intval($g['tq']));
			$where.= $db->quoteInto(' and series = ?',intval($g['s']));
			$where.= $db->quoteInto(' and episode = ?',intval($g['e']));
			try{
				$db->update('tq_play_link_list',$a,$where);
			}catch(Exception $e){
				$this->view->res1='失败! 可能冲突。';
			} 
			die("<script>window.opener.location=window.opener.location.pathname+'?if_shangjia=1';window.opener=null;window.open('','_self');window.close();</script>");
		}
		else
		{
			$sql="select * from tq_play_link_list where html_id=".intval($g['tq'])." and series=".intval($g['s'])." and episode=".intval($g['e'])." and state=1";
			$this->view->d=$db->fetchRow($sql);
			//print_R($this->view->d);exit;
		}
	}

	public function playexteditAction()
	{
		$g=$_GET;
		if(!is_numeric($g['tq']) || !is_numeric($g['s']) || !is_numeric($g['e'])) die('参数错误');
		$this->playtongbu();
		
		$db=db_o::get_single('master');

		$p=$_POST;
		if($p && $p['series'] && $p['episode'])
		{
			$a=null;
			$a['episode']=$p['episode'];
			$a['series']=$p['series'];
			$a['title']=$p['title'];
			$a['url']=$p['url'];
			$a['duration']=$p['duration'];
			$a['type']=$p['type'];
			$a['onlinetime']=strtotime(intval($p['onlinetime']));
			$a['site_logo']=$p['site_logo'];
			$a['site_name']=$p['site_name'];
			$where = $db->quoteInto('html_id = ?',intval($g['tq']));
			$where.= $db->quoteInto(' and series = ?',intval($g['s']));
			$where.= $db->quoteInto(' and episode = ?',intval($g['e']));
			try{
				$db->update('tq_play_ext_list',$a,$where);
			}catch(Exception $e){
				$this->view->res1='失败! 可能冲突。';
			} 
			die("<script>window.opener.location=window.opener.location.pathname+'?if_shangjia=1';window.opener=null;window.open('','_self');window.close();</script>");
		}
		else
		{
			$sql="select * from tq_play_ext_list where html_id=".intval($g['tq'])." and series=".intval($g['s'])." and episode=".intval($g['e'])." and state=1";
			$this->view->d=$db->fetchRow($sql);
			//print_R($this->view->d);exit;
		}
	}

	public function playdelAction()
	{
		$g=$_GET;
		if(!is_numeric($g['tq']) || !is_numeric($g['s']) || !is_numeric($g['e'])) die('参数错误');
		$this->playtongbu();
		
		$db=db_o::get_single('master');

		$where = $db->quoteInto('html_id = ?',intval($g['tq']));
		$where.= $db->quoteInto(' and series = ?',intval($g['s']));
		$where.= $db->quoteInto(' and episode = ?',intval($g['e']));
		$db->delete('tq_play_key_list',$where);
		die("<script>window.opener.location=window.opener.location.pathname+'?if_shangjia=1';window.opener=null;window.open('','_self');window.close();</script>");
	}

	public function playlinkdelAction()
	{
		$g=$_GET;
		if(!is_numeric($g['tq']) || !is_numeric($g['s']) || !is_numeric($g['e'])) die('参数错误');
		$this->playtongbu();
		
		$db=db_o::get_single('master');

		$where = $db->quoteInto('html_id = ?',intval($g['tq']));
		$where.= $db->quoteInto(' and series = ?',intval($g['s']));
		$where.= $db->quoteInto(' and episode = ?',intval($g['e']));
		$db->delete('tq_play_link_list',$where);
		die("<script>window.opener.location=window.opener.location.pathname+'?if_shangjia=1';window.opener=null;window.open('','_self');window.close();</script>");
	}

	public function playextdelAction()
	{
		$g=$_GET;
		if(!is_numeric($g['tq']) || !is_numeric($g['s']) || !is_numeric($g['e'])) die('参数错误');
		$this->playtongbu();
		
		$db=db_o::get_single('master');

		$where = $db->quoteInto('html_id = ?',intval($g['tq']));
		$where.= $db->quoteInto(' and series = ?',intval($g['s']));
		$where.= $db->quoteInto(' and episode = ?',intval($g['e']));
		$db->delete('tq_play_ext_list',$where);
		die("<script>window.opener.location=window.opener.location.pathname+'?if_shangjia=1';window.opener=null;window.open('','_self');window.close();</script>");
	}

	public function xxjsaddAction()
	{
		$g=$_GET;
		$p=$_POST;
		if(!$g['hid']) die('参数错误');
		
		$db=db_o::get_single('master');
		
		if($g['id'])
		{
			$sql="select * from tq_video_xxjs_html where id=".intval($g['id']);
			$this->view->d=$r=$db->fetchRow($sql);
			if(!$r) $this->view->request->m='add';
		}

		if($g['hid'] && $p['description'])
		{
			if($g['id'])
			{
				$where = $db->quoteInto('html_id = ?',$g['hid']);
				$where.= $db->quoteInto(' and id = ?',$g['id']);
				$db->delete('tq_video_xxjs_html',$where);
			}
		
			$a=null;
			$a['html_id']=$g['hid'];
			$a['title']=$p['title'];
			$a['html']=$p['description'];
			$a['right']=$p['right'];
			$db->insert('tq_video_xxjs_html',$a);
			die("<script>window.opener.location=window.opener.location.pathname+'?if_shangjia=1';window.opener=null;window.open('','_self');window.close();</script>");
		}
	}

	public function xxjsdelAction()
	{
		$g=$_GET;
		$p=$_POST;
		if(!$g['hid'] || !$g['id']) die('参数错误');
		
		$db=db_o::get_single('master');
		$where = $db->quoteInto('html_id = ?',$g['hid']);
		$where.= $db->quoteInto(' and id = ?',$g['id']);
		$db->delete('tq_video_xxjs_html',$where);
		die("<script>window.opener.location=window.opener.location.pathname+'?if_shangjia=1';window.opener=null;window.open('','_self');window.close();</script>");
	}

}//end class