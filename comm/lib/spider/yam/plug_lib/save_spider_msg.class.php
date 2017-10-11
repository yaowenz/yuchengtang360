<?php
require_once dirname(__FILE__).'/../../../ip/ip.class.php';
require_once dirname(__FILE__).'/../../../db/db.class.php';
class save_spider_msg{
	public $error = '';
	public $ad_comment = 'user_ad_comment';
	public $user_ad_arrival = 'user_ad_arrival';

	//得到一个单例
	static private $instance = NULL;
	public static function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	//保存广告到达率信息
	public function save_ad_arrival($ary){
		//echo "<br/>--------------保存广告到达率信息-------------<br/>";print_r($ary);exit;
		$ary['updatedate'] = time();
		$ar = cm_db_config::get_ini('ncg_yam','master');
		$db = cm_db::get_db($ar);
		//print_r($ary);exit;

		$arrival = $this->get_ad_arrival($ary['ad_id'],$ary['domain_id']);
		if($arrival > 0){
			$row = array();
			$row['comment_count'] = $ary['comment_count'];
			$row['repest_count'] = $ary['repest_count'];
			$row['fans_count'] = $ary['fans_count'];
			$row['updatedate'] = $ary['updatedate'];
			//print_r($row);
			$where = $db->quoteInto('ad_id = ?', $ary['ad_id']) . ' AND ' .
					 $db->quoteInto('domain_id = ?', $ary['domain_id']);
			//echo $where;exit;
			$ret = $db->update($this->user_ad_arrival, $row, $where);
		} else {
			try{
				$ret = $db->insert($this->user_ad_arrival, $ary);
			}catch(Exception $e){
				$ret = 0;
			}
		}
		if($ret>0){
			return $db->lastInsertId();
		}else{
			return 0;
		}
	}
	//查询广告到达率信息是否存在
	public function get_ad_arrival($ad_id,$domain_id){
		$ary = array();

		$ar = cm_db_config::get_ini('ncg_yam');
		$db = cm_db::get_db($ar);

		$select = $db->select();
		$select->from($this->user_ad_arrival,'count(*)');
		$select->where('ad_id = ?',$ad_id);
		$select->where('domain_id = ?',$domain_id);

		$sql = $select->__toString();	//print_R($sql);exit;
		$ret = $db->fetchOne($sql);
		return $ret;
	}
	
	//保存广告评论信息
	public function save_ad_comment($ary){
		$ary['createdate'] = time();

		//echo "<br/>--------------保存广告评论信息-------------<br/>";print_r($ary);exit;
		$ar = cm_db_config::get_ini('ncg_yam','master');
		$db = cm_db::get_db($ar);
		$ret = $db->insert($this->ad_comment, $ary);
		if($ret>0){
			return $db->lastInsertId();
		}else{
			return 0;
		}
	}

	
	//按广告ID获取最后10个评论的ID
	public function get_last_comment($ad_id){
		$ary = array();
		//$ary['comment_id'] = '2121103251229581';
		//return $ary;

		$ar = cm_db_config::get_ini('ncg_yam');
		$db = cm_db::get_db($ar);

		$select = $db->select();
		$select->from($this->ad_comment,'*');
		$select->where('ad_id = ?',$ad_id);
		$select->order('id DESC');
		$select->limit(10,'0');

		$sql = $select->__toString();	//print_R($sql);exit;
		$ret = $db->fetchAll($sql);
		return $ret;
	}
}