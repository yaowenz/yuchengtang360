<?php
//爬yam广告数据 by ziv 2011/3/11
set_time_limit(0); 
if(!defined('COMM_PATH')) { 
	define('COMM_PATH', dirname(__FILE__).'/../../../');//共用库目录
}
define('SPIDER_PLUG_PATH', COMM_PATH.'/lib/spider/yam/plug');
spider_run::$ip=$argv[1];

require_once COMM_PATH . '/lib/db/db.class.php';
require_once COMM_PATH . '/lib/spider/my_spider/myspider.class.php';
class spider_run{
	static $ip;
	public $interval=3600;//1小时 抓取间隔时间
	//public $interval=60;//测试时 
	public $error='';
	public $state=1;
	function __construct(){
		$this->db=cm_db::get_db(cm_db_config::get_ini('spider_ncg_yam','master'));
		$this->db_single=cm_db::get_db(cm_db_config::get_ini('spider_ncg_yam_single','master')); 
		$this->ip=$this->get_ip();

		//1 启动抓取log
		$this->log_id=$this->log_all_begin();
 
		require_once COMM_PATH . '/lib/spider/yam/get.plug.from.url.php';
		while(1){
			echo "\r\n";
			//2 选择出要抓取的页面
			$i=$this->get_spider_row(); 
			if(!$i && $this->state===1) {$this->log_all_end($this->log_id,$this->error);return null;}
			else if(!$i && $this->state===-2) {$this->state=1;sleep(1);continue;}//进程冲突
			else if($i===true) {$this->log_all_end($this->log_id);return true;}

			//3 抓取
			$this->log_begin_time($i['id']);
			$this->plug=get_plug_from_url::get($i['ref']);
			if(!$this->plug) {
				$this->log_error($i['id'],get_plug_from_url::$error);
				$this->update_state($i['id'],-2);
				echo "no plug";
				continue; 
			} else if($this->plug === 'ignore') {
				$this->log_end_time($i['id']);
				$this->update_state($i['id'],-1);
				echo "ignore";
				continue; 
			}

			$p=$this->load_plug($this->plug);
			if(!$p) $this->log_error($i['id'],'加载插件失败');
			$rt = $p->run($i);
			if($rt === false){
				$e_t='error';
				if($p->error) $e_t=$p->error;
				$this->log_error($i['id'],$e_t);
				$this->update_state($i['id'],1);
			} else if($rt === 'ignore') {
				$this->log_end_time($i['id']);
				$this->update_state($i['id'],-1); 
				echo "plug ignore";
			} else {
				$this->log_end_time($i['id']);
				$this->update_state($i['id'],1); 
			}
		}//end while
		my_spider::del_tmp_dir();
	}
	
	function log_error($id,$error){
		if(!$id || !$error) return false;
		$where = $this->db->quoteInto('id = ? ', $id);  
		$b['spider_error']=$error;
		$this->db->update('log_user_ref',$b,$where); 
	}
	
	//抓取url的begin_time
	function log_begin_time($id){
		if(!$id) return false;
		$where = $this->db->quoteInto('id = ? ', $id);  
		$b['spider_time']=time();
		$this->db->update('log_user_ref',$b,$where); 
	}

	//抓取url的end_time
	function log_end_time($id){
		if(!$id) return false;
		$where = $this->db->quoteInto('id = ? ', $id);  
		$b['spider_end_time']=time();
		$this->db->update('log_user_ref',$b,$where); 
	}

	//抓取总的begin
	function log_all_begin(){
		$a['begin_time']=time();
		$a['ip']=$this->ip;
		if(!$this->db_single->insert('spider_log', $a)) return false;
		$last_insert_id = $this->db_single->lastInsertId();
		return $last_insert_id;
	}

	//抓取总的end
	function log_all_end($id,$error=''){
		if(!$id) return false;
		$where = $this->db_single->quoteInto('id = ? ', $id);  
		if($error) $a['error']=$error;
		$a['end_time']=time();
		$this->db_single->update('spider_log',$a,$where); 
		return true; 
	}
	
	//取出要抓取的项目行，并对其赋予状态 2 ，告知其正在抓取 
	function get_spider_row(){

		//查看是否有正在抓取的
		$select = $this->db->select()->from('log_user_ref', 'id')->where('state=?','2')->where('ip=?',$this->ip)->limit(1);
		$sql = $select->__toString();
		$r = $this->db->fetchOne($sql);
		if($r) {$this->error='进程已在抓取中';return false;}
		
		//查出url
		$select = $this->db->select()->from('log_user_ref', '*')->where('spider_end_time < ? or spider_end_time=0 ', time()-$this->interval)->where('spider_error = ?', '')->where('state=?','1')->order('id')->limit(1);
		$sql = $select->__toString();    //print_R($sql);exit;
		$r = $this->db->fetchRow($sql);	//print_R($r);exit;
		if(!$r) {return true;}

		//抢占抓取项 state = 2
		$where = $this->db->quoteInto('id = ? ', $r['id']); 
		$where .= ' and '.$this->db->quoteInto('state=?','1');
		$update="update log_user_ref set state=2,ip='".$this->ip."' where ".$where." order by id limit 1";
		//print_R($update);exit;
		if(!$this->db->query($update)){$this->error='进程冲突';$this->state=-2;return false;};

		return $r; 
	}

	function update_state($id,$state=1)
	{
		if(!is_numeric($id)) return false;
		if(!is_numeric($state)) return false;
		$where = $this->db->quoteInto('id = ? ', $id); 
		$where .= ' and '.$this->db->quoteInto('state!=?',$state); 
		$a['state']=$state;
		return $this->db->update('log_user_ref',$a,$where); 
	}

	//加载插件
	function load_plug($p){
			$t=null;$t2=null;$t3=null;
			require_once SPIDER_PLUG_PATH.'/'.$p.'/access.php';
			$t3='spider_plug_'.$p;
			if(!class_exists($t3)) return false; 
			$t2=new $t3; 
			return $t2;
		}
	
	//获取ip
	function get_ip(){
		   if (self::$ip)
			   $ip = self::$ip;
		   else if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
			   $ip = getenv("HTTP_CLIENT_IP");
		   else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
			   $ip = getenv("HTTP_X_FORWARDED_FOR");
		   else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
			   $ip = getenv("REMOTE_ADDR");
		   else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
			   $ip = $_SERVER['REMOTE_ADDR'];
		   else
			   $ip = "unknown";
	   return($ip);
	}

}//end class
?>