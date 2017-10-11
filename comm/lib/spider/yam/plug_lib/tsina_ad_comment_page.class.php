<?php
require_once dirname(__FILE__).'/../../../ip/ip.class.php';
require_once dirname(__FILE__).'/../../../db/db.class.php';

class tsina_ad_comment_page{
	public $error = '';
	public $tsina_ad_comment_page = 'tsina_ad_comment_page';

	//得到一个单例
	static private $instance = NULL;
	public static function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	//保存广告的评论页面信息
	public function save_comment_page($ary){
		//print_r($ary);exit;
		$ary['updatetime'] = time();
		$ar = cm_db_config::get_ini('ncg_yam_single','master');
		$db = cm_db::get_db($ar);
		
		try{
			$ret = $db->insert($this->tsina_ad_comment_page, $ary);
		}catch(Exception $e){
			$ret = 0;
		}

		if($ret>0){
			return $db->lastInsertId();
		}else{
			return 0;
		}
	}

	//保存广告的评论页面信息
	public function update_comment_page($adid,$row = array()){
		//echo $adid;

		$ar = cm_db_config::get_ini('ncg_yam_single','master');
		$db = cm_db::get_db($ar);

		$row['updatetime'] = time();
		$where = $db->quoteInto('adid = ?', $adid);
		//echo $where;
		$ret = $db->update($this->tsina_ad_comment_page, $row, $where);

		if($ret>0){
			return $ret;
		}else{
			return 0;
		}
	}

	public function get_comment_page_for_cach($ad_id){
		return $this->get_comment_page($ad_id);
	}

	public function get_comment_page($ad_id){
		$ary = array();
		//$ary['comment_id'] = '2121103251229581';
		//return $ary;

		$ar = cm_db_config::get_ini('ncg_yam_single');
		$db = cm_db::get_db($ar);

		$select = $db->select();
		$select->from($this->tsina_ad_comment_page,'*');
		$select->where('adid = ?',$ad_id);

		$sql = $select->__toString();	//print_R($sql);exit;
		$templet = $db->fetchRow($sql);
		return $templet;
	}

}