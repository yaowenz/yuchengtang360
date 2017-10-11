<?php
require_once LIB_PATH . '/db.class.php';
require_once LIB_PATH . '/team.class.php';

class Admin_TeamController extends Admin_Controller_Action
{
	public function addAction()
	{
		$p = $_POST;
		while($p && $p['u_c'] && $p['u_e'])
		{
 			$a['c_name'] = $p['u_c'];
			$a['e_name'] = $p['u_e'];
			if($p['from']) $a['from'] = $p['from'];
			if($p['note']) $a['note'] = $p['note'];
			$r1 = yc_team::add($a);
			
			if(!$r1) $this->view->res1 = '添加失败';
			else if(is_numeric($r1)) $this->_redirect('/admin-team-list.htm'); //$this->view->res1 = '添加成功';	
			break;
		}
	}

	public function listAction()
	{
		$this->view->user_list = yc_team::get_list();
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
			$a['c_name'] = $p['u_c'];
			if($p['u_e']) $a['e_name'] = $p['u_e'];
			if($p['from']) $a['from'] = $p['from'];
			if($p['note']) $a['note'] = $p['note'];
			$r1 = yc_team::update($a);
			
			if(is_numeric($r1)) $this->view->res1 = '修改成功';	
			break;
		}
		
		$this->view->team = yc_team::get_team($uid);
	}

	public function deleteAction(){
		$uid = $this->getRequest()->id;
		$r = yc_team::del($uid);  
		if($r>=1) $this->_redirect('/admin-team-list.htm');
		else die('error!');
	}

}//endclass