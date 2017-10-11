<?php
/* 
  全站各部分 controller_action 的超类
* by zx 2011/2/23
*/
require_once 'Zend/Controller/Action.php';
require_once LIB_PATH . '/user.class.php';
require_once LIB_PATH . '/offen.php';

//所有controller都执行
class Pre_Controller_Action extends Zend_Controller_Action 
{
	public function preDispatch(){
		parent::preDispatch();

		$this->view->module = $this->_getParam('module');
		$this->view->controller = $this->_getParam('controller');
		$this->view->action = $this->_getParam('action');
		$this->view->request =$this->getRequest();
		
		//取管理员权限
			if(isset($_SESSION['cm_comm_admin_info']) && $_SESSION['cm_comm_admin_info'] && $_SERVER['HTTP_HOST']==ADMIN_DOMAIN) $this->view->admin_info = $_SESSION['cm_comm_admin_info'];
			$this->view->acl = $this->view->admin_info['acl']; 
			if($this->view->acl && $_SERVER['HTTP_HOST']!=MAIN_DOMAIN) $_GET['__DEBUG'] = 1;

			//print_R($this->view->admin_info);exit;
		///
	}  

	public function postDispatch(){	
		parent::postDispatch();
	}

	protected function _redirect($url, array $options = array())
    {
        $pathInfo=str_replace("-","/",$pathInfo);//用 - 代替 /
		$this->_helper->redirector->gotoUrl($url, $options);
    }

	public function __call($m,$a){
		//if ($_GET['__DEBUG'] != 1) $this->_redirect("/");
	} 
}

//默认前台 controller执行
class Client_Controller_Action extends Pre_Controller_Action 
{
	function init(){

	}
	public function preDispatch(){	
		parent::preDispatch();
		$this->view->setScriptPath(realpath(global_info::$v_path_client));
  	}
}


//管理员后台 controller执行
class Admin_Controller_Action extends Pre_Controller_Action
{
	function init(){
		if($_SERVER['HTTP_HOST']!=ADMIN_DOMAIN) die('');
		//if($_SERVER['REMOTE_ADDR']!='116.231.253.144' && $_SERVER['HTTP_HOST']!=TEST_DOMAIN) die('');
	}
	public function preDispatch(){
		parent::preDispatch();
		$this->view->setScriptPath(realpath(global_info::$v_path_admin)); 
		//登录相关
				//登录 
				if(isset($_POST['ad_name']) && $_POST['ad_name']){
					$rt = comm_admin_user::login($_POST['ad_name'],$_POST['ad_password']);
					if($rt) 
					{	
						$rt_url = $_POST['ad_requestUrl'];
						unset($_POST['ad_name']);
						unset($_POST['ad_password']); 
						unset($_POST['ad_submit']);
						unset($_POST['ad_requestUrl']);
						
						$this->_redirect($rt_url);
					}
					else
					{
						echo "用户名密码错误！<br><br>";
					}
				}

				//登出
				$g=$_GET;
				if(isset($g['logout']) && $g['logout']) 
				{
					comm_admin_user::logout();
					$this->_redirect("/admin.html");	 
				}
				
				if($this->view->module=='admin' && $this->view->controller=='sub' && $this->view->action=='subsend'){} //排除部分
				else if($this->view->module=='admin' && $this->view->controller=='pro' && $this->view->action=='json'){}
				else if(!$this->view->acl) //die('没有登录，或者没有权限！');
				{
					require_once(global_info::$v_path_admin.'/comm/login.phtml');
					die('');
					//$this->_helper->viewRenderer->setNoRender(true);
				}
		///

		//print_R($this->view->user_info);echo '</br>';print_R(session_get_cookie_params()); //显示session 相关 

	}///preDispatch
	
}//end class




//Webservice
class Webservice_Controller_Action extends Zend_Controller_Action 
{
	public function preDispatch(){	
		parent::preDispatch();
		$this->view->setScriptPath(realpath(global_info::$v_path_webservice)); 
  	}
}//end class Webservice