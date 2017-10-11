<?
/**
 * 动态 feeds log记录相关
 * created by zx 2010-6-10
 */
require_once dirname(__FILE__).'/../container.class.php';
require_once dirname(__FILE__).'/../db/db.class.php'; 
require_once dirname(__FILE__).'/../cache/cache.class.php'; 
class feeds{
	
	static $key_user = 'cm_FEEDS_CACHE_USER_';//二级缓存key全局设置 跟 uid

	private $uid;
	private $content;
	private $system;
	private $createtime;
	private $type; 

	public function set_uid($u){$this->uid = $u;return $this;} 
	public function set_content($c){$this->content = $c;return $this;}
	public function set_system($s){$this->system = $s;return $this;}
	public function set_createtime($c){$this->createtime = $c;return $this;}
	public function set_type($t){$this->type = $t;return $this;}
	public function set_img_url($x){$this->img_url = $x;return $this;}
	public function set_link_url($x){$this->link_url = $x;return $this;}
	public function set_video_url($x){$this->video_url = $x;return $this;}

	protected function set_wrong($w){$this->wrong = $w;}

	public function get_uid(){return $this->uid;}
	public function get_content(){return $this->content;}
	public function get_system(){return $this->system;}
	public function get_createtime(){return $this->createtime;}
	public function get_type(){return $this->type;}
	public function get_img_url(){return $this->img_url;}
	public function get_link_url(){return $this->link_url;}
	public function get_video_url(){return $this->video_url;}

	public function get_wrong(){return $this->wrong;}
		
	//记录插入
	public function insert()
	{
		if(!$this->uid || !$this->content || !$this->system || !$this->createtime || !$this->type) {$this->wrong = '参数不全';return false;}
		$db = cm_db::get_db(cm_db_config::get_ini('logs','master'));  
		$a['uid'] = $this->uid;
		$a['content'] = $this->content;
		$a['system'] = $this->system;
		$a['createtime'] = $this->createtime; 
		$a['type'] = $this->type; 
		if($this->img_url) $a['img_url'] = $this->img_url; 
		if($this->link_url) $a['link_url'] = $this->link_url; 
		if($this->video_url) $a['video_url'] = $this->video_url; 

		// 插入数据的数据表
		$table = 'cm_feeds_log';

		// i插入数据行并返回行数
		$rows_affected = $db->insert($table, $a);
		$last_insert_id = $db->lastInsertId();

		self::clear_cache_user($this->uid);

		return $last_insert_id;
	}
	
	//删除用户2级缓存
	static function clear_cache_user($uid){  
		if(!is_numeric($uid)) return false;
		if($uid){
			$cache = new seria_cache; 
			$cache->delete(self::$key_user . $uid);
			$cache->close();
		}
	} 

	public function __destruct(){}
}
?>