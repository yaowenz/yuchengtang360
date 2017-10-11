<?php //2014/8/19

require_once LIB_PATH . '/db.class.php';
require_once LIB_PATH.'/q.cache.php';
 
class yc_main_class
{
	public $db;

	public function __construct()
	{
		$this->db=db_o::get_single('master');
	}
	
	//获取每日最新最近n天的内容列表
	public function get_mrzx_ndays_list($a,$time=3600,$clear_cache=false)
	{
		if(!$a || !$a['key1']) return false;
		require_once COMM_PATH.'/lib/cache/cache.class.php';
		require_once COMM_PATH.'/comm.functions.php';

		$key=md5(serialize(array_deep_to_char($a)).$time);

		$cache = new temp_cache;

		$info = $cache->get($key);

		if (!$info || $_GET['__DEBUG'] == 1 || $clear_cache){
			$info=$this->get_mrzx_ndays_list_db($a);
			if($info) $cache->saveOrUpdate($key,$info,$time); 
		} 
		$cache->close();
		if($info) return $info; else return null;  
	}

	public function get_mrzx_ndays_list_db($a=array())
	{
		if(!$a || !$a['key1']) return false;
		if($a['n']) $n=intval($a['n']);
		if(!$n) $n=10;

		if(!$a['no_new']) $r['最 新']=$this->get_mrzx_list_db($a);
		
		//$sql="select count(distinct year,month,day) from (select * from yc_meirizuixin order by id desc limit 1000) as a where key1=".intval($a['key1']);
		//$n2=$this->db->fetchOne($sql);
		$sql="select distinct year,month,day from (select * from yc_meirizuixin order by id desc limit 1000) as a where key1=".intval($a['key1']);
		$rt1=$this->db->fetchAll($sql);
		$n2=count($rt1);
		if($n2<$n) $n=$n2+1;
		
		$i=0;$m=0;
		if($rt1) foreach($rt1 as $k2=>$i2)
		{
			//$time=strtotime("-".$m." day");
			$d['year']=$i2['year'];
			$d['month']=$i2['month'];
			$d['day']=$i2['day'];
			$sql="select * from yc_meirizuixin where year=".$d['year']." and month=".intval($d['month'])." and day=".intval($d['day'])." and key1=".intval($a['key1'])." order by sort desc,create_time desc";
			$rmr=$this->db->fetchAll($sql);
			if($rmr)
			{
				$r[$d['month'].' - '.$d['day'].':'.$d['year']]=$rmr;
				$i++;
			}
			$m++;
			if($i>=$n) break;
		}
		return $r;
	}
	
	public function get_mrzx_list($a,$time=3600,$clear_cache=false)
	{
		if(!$a || !$a['key1']) return false;
		require_once COMM_PATH.'/lib/cache/cache.class.php';
		require_once COMM_PATH.'/comm.functions.php';

		$key=md5(serialize(array_deep_to_char($a)).$time);

		$cache = new temp_cache;

		$info = $cache->get($key);

		if (!$info || $_GET['__DEBUG'] == 1 || $clear_cache){
			$info=$this->get_mrzx_list_db($a);
			if($info) $cache->saveOrUpdate($key,$info,$time); 
		} 
		$cache->close();
		if($info) return $info; else return null;
	}

	public function get_mrzx_list_db($a=array())
	{
		if(!$a || !$a['key1']) return false;
		if(!$a['limit']) $limit=array(0,20);
		else $limit=$a['limit'];

		if(!$a['order']) $a['order']=array('year desc','month desc','day desc','sort desc','id desc');

		$select = $this->db->select();
		$select->from('yc_meirizuixin', '*');
		$select->where("key1 = ?",$a['key1']);
		$select->order($a['order']);
		$select->limit($limit[1],$limit[0]);
		$sql = $select->__toString();//echo $sql;exit;
		$r=$this->db->fetchAll($sql);
		//if($r['html']) $r['html']=stripslashes($r['html']);

		return $r;
	}

	//每日最新 tq_video_meirizuixin  key1 对应的含义
	static function get_mrzx_keys($flip=null)
	{
		$r[1]="GIF";
		$r[2]="热帖";
		$r[3]="视频";
		if($flip) $r=array_flip($r);
		return $r;
	}
	static function get_mrzx_keys_des($flip=null)
	{
		$r[1]="篮球运动相关的动图资源";
		$r[2]="收集篮球话题相关的热帖";
		$r[3]="篮球比赛直播、录播资源";
		if($flip) $r=array_flip($r);
		return $r;
	}

	function get_html_list($a=array(),$time=1800,$clear_cache=false) 
	{
		$o=new q_cache;
		$o->k='ngmancgethltv1';
		$o->a=$a;
		$o->clear_cache=$clear_cache;
		$o->time1=$time;
		$o->step1();
		if($o->then==1)
		{
			$o->info=$this->get_html_list_db($a);
		}
		$o->step2();
		if($o->info) return $o->info; else return null;
	}

	public function get_html_list_db($a=array()) 
	{
		if(!$a['page']) $a['page']=1;
		if(!$a['pagesize']) $a['pagesize']=50;
		if(!isset($a['type'])) $a['type']='0';

		//-----------------------------------------------------

		$select = $this->db->select();
		$select->from('yc_html_main_index as a', 'count(1)');
		if($search_tq_id_in) $select->where('a.id IN (?)',$search_tq_id_in);

		if($a['a_state']==9999){}
		else if($a['a_state']) $select->where("a.state=?",$a['a_state']);
		else
		{
		}
		
		if(!$a['no_count'])
		{
			$sql = $select->__toString();//echo $sql;exit;
			$r['count']=$this->db->fetchOne($sql); 
		}
		
		//-----------------------------------------------------

		$select->reset('order');
		$select->reset('limitcount');
		$select->reset('limitoffset'); 
		$select->reset('columns');
		//$select->columns('*');
		$columns_a[]="a.*";
		
		$select->columns($columns_a);
		if($a['order']) $select->order($a['order']);
		else if($a['like']) $select->order('a.id desc');
		else if(!$a['type']) $select->order('a.id desc');
		else $select->order('a.id desc');

		if($a['page'] && $a['pagesize'])  
		{
			$page=intval($a['page']);
			$pagesize=intval($a['pagesize']);
			$startMsg = ($page-1)*$pagesize;
			$select->limit($pagesize,$startMsg);
		}
		if(is_array($a['limit']))
		{
			$select->limit($a['limit'][0],$a['limit'][1]);  
		}
		$sql = $select->__toString();//echo $sql;exit;
		$r['list']=$this->db->fetchAll($sql);
		
		//-----------------------------------------------------

		require_once COMM_PATH .'/lib/string/str10_62.class.php';
		$o1=new Base62;
		//print_R($o1->base62_encode(2125));print_R($o1->base62_decode('Ci'));exit;
		foreach($r['list'] as $k=>$i)
		{
			$r['list'][$k]['id_o']=$r['list'][$k]['id'];
			$r['list'][$k]['id']=$o1->base62_encode($r['list'][$k]['id']);
			

			//按电影名称排序
				$dyname=null;
				
				$sql="select name from yc_html_name where html_id=".$r['list'][$k]['id_o']." order by sort desc limit 1";
				$dyname=$this->db->fetchOne($sql);
				if($dyname){}
				else $dyname=$k;
				$r['list'][$k]['dyname']=$dyname;

				if($a['order_by_name'])
				{
					$dyname_gbk=str_replace(array('一','二','三','四','五','六','七','八','九','十'),array('1','2','3','4','5','6','7','8','9','10'),$dyname);
					$dyname_gbk=mb_convert_encoding($dyname_gbk,'GBK','utf-8');
					$r['list'][$dyname_gbk.$k]=$r['list'][$k];
					unset($r['list'][$k]);
					ksort($r['list']);
				}
			///
			
		}

		if(!$r['list']) return false;
		if($a['no_count']) return $r['list'];
	
		//print_R($r);exit;
		return $r;
	}
	
	function get_html_detail($a=array(),$time=1800,$clear_cache=false) 
	{
		$o=new q_cache; 
		$o->k='ngailasethtmailv1';
		$o->a=$a;
		$o->clear_cache=$clear_cache;
		$o->time1=$time;
		$o->step1();
		if($o->then==1)
		{
			$o->info=$this->get_html_detail_db($a);
		}
		$o->step2();
		if($o->info) return $o->info; else return null;
	}

	public function get_html_detail_db($a=array()) 
	{
		require_once COMM_PATH .'/lib/string/str10_62.class.php';
		$o1=new Base62;
		if($a['is_full_id']) $id=intval($a['id']);
		else $id=intval($o1->base62_decode($a['id']));
		if(!$id) return false;
		
		$select = $this->db->select();
		$select->from('yc_html_main_index', '*');
		$select->where("id = ?",$id);
		if($a['state']) $select->where("state = ?",$a['state']);
		$sql = $select->__toString();//echo $sql;exit;
		$r=$this->db->fetchRow($sql);
		$r['id_62']=$a['id'];
		
		$r['play_info']=null;
		
		if(!$r['type'])
		{
			$r['type']=0;
		}


		//取名
			$sql="select name,sort from yc_html_name where html_id=".$r['id']." order by sort desc";
			$r_t_names=$this->db->fetchAll($sql);
			 if($r_t_names)
			{
				$r['name']=$r_t_names[0]['name'];
			}
		///

		//print_R($r);exit;
		return $r;
	}

	public function update_html($a=array()) 
	{
		$id=$a['id'];
		if(!is_numeric($id))
		{
			require_once COMM_PATH .'/lib/string/str10_62.class.php';
			$o1=new Base62;
			$id=intval($o1->base62_decode($a['id']));
		}

		if(!$id) return false;
		unset($a['id']);
		
		$where = $this->db->quoteInto('id = ?',$id); 
		return $this->db->update('yc_html_main_index',$a,$where);
	}

}//end class