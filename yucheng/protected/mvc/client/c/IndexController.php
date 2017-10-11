<?php
class Client_IndexController extends Client_Controller_Action
{
	public function indexAction()
	{
		require_once LIB_PATH . '/item.class.php';
		$a['id_in']=array(38,76,63);
		$l=yc_item::get_list($a);
		$this->view->l=$l;
	}
}