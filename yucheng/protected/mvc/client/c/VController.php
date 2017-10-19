<?php
class Client_VController extends Client_Controller_Action
{
	public function __v_pre()
	{
		
	}

	public function lAction()
	{
		$this->__v_pre();
		require_once LIB_PATH . '/item.class.php';
		$type=$this->getRequest()->t;
		$mtype=$this->getRequest()->m;//馆藏展品栏目
		if($type=='z')//一周一珍
		{
			$a['id_in']=array(38,76,63,10,14,12);
		}
		else if($mtype)
		{
			if($mtype==1) //明清
			{
				$a['niandai_in']=array('明','清');
			}
			else if($mtype==2) //宋元
			{
				$a['niandai_in']=array('宋','元');
			}
		}
		$a['state']='>0';
		$l=yc_item::get_list($a);
		
		$this->view->l=$l;
		$this->view->type=$type;
		//print_R($this->view);exit;
	}
	
	public function d_post()
	{
		if(!$this->view->acl || !$_POST) return false;
		$p=$_POST;
		$a=null;
		
		$a['id']=intval($this->getRequest()->i);
		if(!$a['id']) die('参数错误!');

		require_once LIB_PATH . '/item.class.php';
		
		if($p['step1'])	//修改基本信息
		{
			/*
			if($_FILES['m3dzip'] && !$_FILES['m3dzip']['error'])
			{
				require_once COMM_PATH . '/lib/files/dir.class.php';
				$dir1=realpath(cm_url::get('yucheng-img-path').'/m3d/3dpic'); if(!$dir1) die('');
				$dir2=$dir1.'/'.$a['id'];
				if(realpath($dir2)){
					$dir2=realpath($dir2);
					$r=dirs::del($dir2);
				}
				file_dir::create_dir($dir2);

				require_once COMM_PATH . '/lib/files/pclzip.lib.php';
				$archive = new PclZip($_FILES['m3dzip']['tmp_name']);
				$list = $archive->extract(PCLZIP_OPT_PATH, $dir2);
				print_R($list);exit;
			}
			*/

			if($p['m_3d']) $a['m_3d']=$p['m_3d'];
			yc_item::update($a);
		}

		$_GET['__DEBUG']=1;
	}

	public function dAction()
	{
		$this->d_post();
		
		$id=$this->getRequest()->i;
		if(!$id || !is_numeric($id)) die('');
		
		require_once LIB_PATH . '/item.class.php';
		$i = yc_item::get_item($id);
		if(!$i) die('');
		if($i['img_name'])
		{
			$files = scandir(M_PIC_PATH.'/'.$i['id'].'/s');
			foreach($files as $f)
			{
				if(is_file(M_PIC_PATH.'/'.$i['id'].'/s/'.$f))
				{
					$fs[basename(M_PIC_PATH.'/'.$i['id'].'/s/'.$f,'.jpg')]=$f;
				}
			}
			$i['pics']=$fs;
		}
		$this->view->detail=$i;

		$a=null;
		$a['state']='>0';
		$a['limit']=array(0,10);
		$l=yc_item::get_list($a);
		$this->view->l=$l;
	}
	
	public function spg20Action()
	{
		require_once LIB_PATH . '/item.class.php';
		$a = [];
		$a['id_in'] = [150,151,152,153,154,155,156,157,158,159,160];
		//$a['state']='>0';
		$l = yc_item::get_list($a);	
		$this->view->l=$l;
		$this->view->type=$type;
	}
	
	

	public function mAction()
	{

	}
	public function aAction()
	{

	}
	public function bAction()
	{

	}

	public function m2Action() //二维码
	{
		require_once COMM_PATH .'/lib/phpqrcode.php';
		$value=$_SERVER['HTTP_REFERER'];
		if(!$value) $value='null';
		$errorCorrectionLevel = "M"; // L-smallest, M, Q, H-best
		$matrixPointSize = "7"; // 1-50
		QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize);
		exit;
	}

	public function m22Action() //二维码
	{
		require_once COMM_PATH .'/lib/phpqrcode.php';
		$value=base64_decode($this->getRequest()->v);
		if(!$value) exit;
		//die($value);
		$errorCorrectionLevel = "H"; // L-smallest, M, Q, H-best
		$matrixPointSize = "30"; // 1-50
		QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize);
		exit;
	}

	public function imgAction()
	{
			
	}

}//end class