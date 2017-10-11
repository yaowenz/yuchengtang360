<?php
/*
* by ziv 2011/4/28
*/
class Admin_UserController extends Admin_Controller_Action
{
	public function preDispatch(){
		parent::preDispatch();
		if($this->view->acl!='高级管理员') die('没有权限!'); 
	}

	public function indexAction(){
		$this->view->user_list = comm_admin_user::get_list(); 
		
	}
	
	//修改密码
	public function pweditAction(){
		
		$p = $_POST;
		while($p)
		{
			if(!$p['op'] || !$p['np'] || !is_numeric($this->view->admin_info['id'])) {$this->view->res1 = '请正确填写信息!';break;}
			$uid = $this->view->admin_info['id']; 
			$r1 = comm_admin_user::pw_edit($uid,$p['op'],$p['np']);
			if($r1){$this->view->res1 = '修改密码成功';}
			else {$this->view->res1 = '请正确填写信息!';}
			break; 
		}
		
	}
	
	//添加用户
	public function addAction(){
		$this->view->acls = comm_admin_user::get_acls(); 
		$p = $_POST;
		while($p)
		{
 			//判断用户名是否存在，存在的话，增加为管理员用户  
			if(!$p['u_n'] || !$p['u_p'] || $p['acl']=='-1') {$this->view->res1 = '请正确填写信息!';break;}
			$a['user_name'] = $p['u_n'];
			$a['user_pass'] = $p['u_p'];
			$a['acl'] = $p['acl'];
			if($p['desc']) $a['desc'] = $p['desc'];
			$r1 = comm_admin_user::add($a);
			
			if($r1=='have') $this->view->res1 = '该管理员已经存在';
			else if(is_numeric($r1)) $this->view->res1 = '添加成功';	
			break;
		}
	}

	//修改用户
	public function editAction(){ 
		$uid = $this->getRequest()->id;
		if(!$uid) exit();
		$this->view->acls = comm_admin_user::get_acls(); 
		$p = $_POST;
		while($p)
		{
 			$a=null;
			$a['id']=$uid; 
			if(!$p['u_n'] || $p['acl']=='-1') {$this->view->res1 = '请正确填写信息!';break;}
			$a['user_name'] = $p['u_n'];
			if($p['u_p']) $a['user_pass'] = $p['u_p'];
			$a['acl'] = $p['acl'];
			if($p['desc']) $a['desc'] = $p['desc'];
			$r1 = comm_admin_user::update($a); 
			
			if(is_numeric($r1)) $this->view->res1 = '修改成功';	
			break;
		}
		
		$this->view->admin = comm_admin_user::get_admin($uid);   
	}

	//删除管理员用户
	public function deleteAction(){
		$uid = $this->getRequest()->id;
		$r = comm_admin_user::del($uid);  
		if($r>=1) $this->_redirect('/admin-user.html');
		else die('error!');
	}
	
	
}