<?php
require_once LIB_PATH . '/db.class.php';
require_once LIB_PATH . '/item.class.php';

class Admin_ItemController extends Admin_Controller_Action
{
	//添加艺术品
	public function addAction()
	{
		$p = $_POST;
		while($p && $p['name'])
		{
 			$a['name'] = $p['name'];
			if($p['note']) $a['note'] = $p['note'];
			$r1 = yc_item::add($a);
			
			if(!$r1) $this->view->res1 = '添加失败';
			else if(is_numeric($r1)) $this->_redirect('/admin-item-list.htm'); //$this->view->res1 = '添加成功';	
			break;
		}
	}

	public function listAction()
	{
		$l = yc_item::get_list();
		if($_GET['act']=='slt')
		{
			foreach($l as $k=>$i)
			{
				$files = scandir(M_PIC_PATH.'/'.$i['id'].'/s');
				$a=null;
				if($files[2])
				{
					$a['id']=$i['id']; 
					$a['img_name']=$files[2];
					$r1 = yc_item::update($a);
				}

				if(!$a['img_name'])
				{
					$files = scandir(realpath(M_UPLOAD_PATH .'/m3d/3dpic/'.$i['id']));
					if($files[80])
					{
						$a=null;
						$a['id']=$i['id']; 
						$a['img_name']='/m3d/3dpic/'.$i['id'].'/'.$files[80];
						$r1 = yc_item::update($a);
					}
					
				}
			}
			$this->_redirect('/admin-item-list.htm');
		}
		$this->view->item_list=$l;
		//print_R($l);exit;
	}

	public function editAction(){
		$uid = $this->getRequest()->id;
		if(!$uid) exit();
		$p = $_POST;
		while($p)
		{
 			$a=null;
			$a['id']=$uid; 
			if(!$p['u_c']) {$this->view->res1 = '请正确填写信息!';break;}
			$a['name'] = $p['u_c'];
			if($p['note']) $a['note'] = $p['note'];
			if($p['niandai']) $a['niandai'] = $p['niandai'];
			if($p['koujin']) $a['koujin'] = $p['koujin'];
			if($p['dijin']) $a['dijin'] = $p['dijin'];
			if($p['height']) $a['height'] = $p['height'];
			if($p['weight']) $a['weight'] = $p['weight'];
			if($p['tongkuan']) $a['tongkuan'] = $p['tongkuan'];
			if($p['fujin']) $a['fujin'] = $p['fujin'];
			$r1 = yc_item::update($a);
			
			if(is_numeric($r1)) $this->view->res1 = '修改成功';	
			break;
		}
		
		$this->view->item = yc_item::get_item($uid);
	}

	public function deleteAction(){
		$id = $this->getRequest()->id;
		$r = yc_item::del($id);  
		if($r>=1) $this->_redirect('/admin-item-list.htm');
		else die('error!');
	}

	public function modifyAction(){
		$a=null;
		$a['id'] = $this->getRequest()->id;
		$a['state'] = $this->getRequest()->state;
		if(!$a['id']) exit();
		$r = yc_item::update($a);
		if($r>=1) $this->_redirect('/admin-item-list.htm');
		else die('error!');
	}

}//endclass