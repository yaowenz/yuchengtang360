<?php //2013/3/14

require_once LIB_PATH . '/db.class.php';
require_once LIB_PATH.'/q.cache.php';
 
class tq_movie_class
{
	public $db;

	public function __construct()
	{
		$this->db=db_o::get_single('master');
	}

	public function get_movie_types()
	{
		$sql = 'select * from tq_movie_type order by `right` desc';
		$r=$this->db->fetchAll($sql);
		foreach($r as $i)
		{
			$r2[$i['id']]=$i;
		}
		return $r2;
	}
	
	public function get_movie_types_cache($time=86400,$clear_cache=false)
	{
		require_once COMM_PATH.'/lib/cache/cache.class.php';
		$key = md5('tq_movie_classget_movie_types_cacheei2v3'.$time);

		$cache = new temp_cache;

		$info = $cache->get($key);

		if (!$info || $_GET['__DEBUG'] == 1 || $clear_cache){
			$info=$this->get_movie_types();
			if($info) $cache->saveOrUpdate($key,$info,$time); 
		} 
		$cache->close();
		if($info) return $info; else return null;  
	} 

	public function get_movie_diqus() 
	{
		$sql = 'select * from tq_movie_diqu order by `right` desc';
		return $this->db->fetchAll($sql);
	}
	
	public function get_movie_diqus_cache($time=86400,$clear_cache=false)
	{
		require_once COMM_PATH.'/lib/cache/cache.class.php';
		$key = md5('geovie_clat_movie_diqus_cacheffev1'.$time); 

		$cache = new temp_cache; 

		$info = $cache->get($key);

		if (!$info || $_GET['__DEBUG'] == 1 || $clear_cache){
			$info=$this->get_movie_diqus();
			if($info) $cache->saveOrUpdate($key,$info,$time); 
		} 
		$cache->close();
		if($info) return $info; else return null;
	} 

	public function get_movie_diqu_menu()
	{
		$sql = 'select * from tq_movie_diqu where `right`>=1200 order by `right` desc';
		return $this->db->fetchAll($sql);
	}
	
	public function get_movie_diqu_menu_cache($time=86400,$clear_cache=false)
	{
		require_once COMM_PATH.'/lib/cache/cache.class.php';
		$key = md5('geovie_get_movie_diqu_menuacheffev1'.$time); 

		$cache = new temp_cache; 

		$info = $cache->get($key);

		if (!$info || $_GET['__DEBUG'] == 1 || $clear_cache){
			$info=$this->get_movie_diqu_menu();
			if($info) $cache->saveOrUpdate($key,$info,$time); 
		} 
		$cache->close();
		if($info) return $info; else return null;  
	}
	
	function get_movie_list($a=array(),$time=1800,$clear_cache=false) 
	{
		$o=new q_cache;
		$o->k='tq_movie_class_get_movie_listv2';
		$o->a=$a;
		$o->clear_cache=$clear_cache;
		$o->time1=$time;
		$o->step1();
		if($o->then==1)
		{
			$o->info=$this->get_movie_list_db($a);
		}
		$o->step2();
		if($o->info) return $o->info; else return null;
	}

	public function get_movie_list_db($a=array()) 
	{
		if(!$a['page']) $a['page']=1;
		if(!$a['pagesize']) $a['pagesize']=50;
		if(!isset($a['type'])) $a['type']='0';

		if($a['like'] && $a['like']!='' && $a['search_daoyan'])	//搜索导演
		{
			$select = $this->db->select();
			$select->distinct();
			$select->from('spider_douban_movie_info as a','daoyan_name as name');
			$select->join('tq_video_main_index as b','a.movie_id=b.douban_id','b.id as tq_id');
			$select->where("a.daoyan_name like ?",'%'.$a['like'].'%');
			$select->group('a.movie_id');
			$select->order('a.daoyan_name');
			$sql = $select->__toString();//echo $sql;exit;
			$r_dy_douban=$this->db->fetchAll($sql);
			
			$select = $this->db->select();
			$select->distinct();
			$select->from('spider_mtime_movie_info as a','daoyan_name as name');
			$select->join('tq_video_main_index as b','a.movie_id=b.mtime_id','b.id as tq_id');
			$select->where("a.daoyan_name like ?",'%'.$a['like'].'%');
			$select->group('a.movie_id');
			$select->order('a.daoyan_name');
			$sql = $select->__toString();//echo $sql;exit;
			$r_dy_mtime=$this->db->fetchAll($sql);

			$r_daoyan=array();
			if($r_dy_douban) foreach($r_dy_douban as $k=>$i)
			{
				$r_daoyan[intval($i['tq_id'])]=trim($i['name']);
			}
			if($r_dy_mtime) foreach($r_dy_mtime as $k=>$i)
			{
				$r_daoyan[intval($i['tq_id'])]=trim($i['name']);
			}
			if($r_daoyan)
			{
				asort($r_daoyan);
				$search_tq_id_in=null;
				foreach($r_daoyan as $k=>$i)
				{
					$search_tq_id_in[]=$k;
				}
				//print_R($like_daoyan_in);exit;
			}
			else return false;

		}
		else if($a['like'] && $a['like']!='' && $a['search_yanyuan'])	//搜索演员
		{
			$select = $this->db->select();
			$select->distinct();
			$select->from('spider_douban_movie_zhuyan as a','zhuyan_name as name');
			$select->join('tq_video_main_index as b','a.movie_id=b.douban_id','b.id as tq_id');
			$select->where("a.zhuyan_name like ?",'%'.$a['like'].'%');
			$select->group('a.movie_id');
			$select->order('a.zhuyan_name');
			$sql = $select->__toString();//echo $sql;exit;
			$r_yy_douban=$this->db->fetchAll($sql);
			
			$select = $this->db->select();
			$select->distinct();
			$select->from('spider_mtime_movie_zhuyan as a','zhuyan_name as name');
			$select->join('tq_video_main_index as b','a.movie_id=b.mtime_id','b.id as tq_id');
			$select->where("a.zhuyan_name like ?",'%'.$a['like'].'%');
			$select->group('a.movie_id');
			$select->order('a.zhuyan_name');
			$sql = $select->__toString();//echo $sql;exit;
			$r_yy_mtime=$this->db->fetchAll($sql);

			$r_yanyuan=array();
			if($r_yy_douban) foreach($r_yy_douban as $k=>$i)
			{
				$r_yanyuan[intval($i['tq_id'])]=trim($i['name']);
			}
			if($r_yy_mtime) foreach($r_yy_mtime as $k=>$i)
			{
				$r_yanyuan[intval($i['tq_id'])]=trim($i['name']);
			}
			if($r_yanyuan)
			{
				asort($r_yanyuan);
				$search_tq_id_in=null;
				foreach($r_yanyuan as $k=>$i)
				{
					$search_tq_id_in[]=$k;
				}
				//print_R($like_yanyuan_in);exit;
			}
			else return false;

		}
		else if($a['like'] && $a['like']!='')	//like 操作 搜索
		{
			$a['order_by_name']=1;
			$select = $this->db->select();
			$select->from('tq_video_name', array('tq_id','name'));
			$select->where("name like ?",'%'.$a['like'].'%');
			$select->group('tq_id');
			$select->order('tq_id desc');
			$sql = $select->__toString();//echo $sql;exit;
			$r_base_like=$this->db->fetchAll($sql);
			$like_tq_id_in='';
			foreach($r_base_like as $k=>$i)
			{
				$like_tq_id_in[]=$i['tq_id'];
			}

			if(!$a['type'])
			{
				$select = $this->db->select();
				$select->from('spider_mtime_movie_name', array('movie_id','name'));
				$select->where("name like ?",'%'.$a['like'].'%');
				$select->group('movie_id');
				$select->order('movie_id desc');
				$sql = $select->__toString();//echo $sql;exit;
				$r_like=$this->db->fetchAll($sql);

				$like_mtime_id_in='';
				foreach($r_like as $k=>$i)
				{
					$like_mtime_id_in[]=$i['movie_id'];
					$mtime_like_names[$i['movie_id']]=$i['name'];
				}
				//print_R($like_mtime_id_in);exit;
			}
			
			if(intval($a['type'])>=0 && $a['type']!==0)
			{
				$select = $this->db->select();
				$select->from('spider_shuangtv_movie_name', array('video_id','name'));
				$select->where("name like ?",'%'.$a['like'].'%');
				$select->group('video_id');
				$select->order('video_id desc');
				$sql = $select->__toString();//echo $sql;exit;
				$r_like=$this->db->fetchAll($sql);

				$like_shuangtv_id_in='';
				foreach($r_like as $k=>$i)
				{
					$like_shuangtv_id_in[]=$i['video_id'];
					$shuangtv_like_names[$i['video_id']]=$i['name'];
				}
				//print_R($like_shuangtv_id_in);exit;
			}

			if(!$like_mtime_id_in && !$like_shuangtv_id_in) return false;
		}

		//-----------------------------------------------------

		$select = $this->db->select();
		$select->from('tq_video_main_index as a', 'count(1)');
		$select->joinLeft('tq_video_info as f','a.id=f.tq_id','');
		if($search_tq_id_in) $select->where('a.id IN (?)',$search_tq_id_in);

		$where1='1=1';
		if((!$a['type'] && !$a['like']) || $like_mtime_id_in || $search_tq_id_in)	//电影
		{
			$select->joinLeft('spider_mtime_movie_info as b','a.mtime_id=b.movie_id','');
			$select->joinLeft('spider_mtime_movie_shangyin as c','a.mtime_id=c.movie_id','');
			if($a['movie_type_id'])
			{
				$select->joinLeft('tq_movie_type_index as d','a.id=d.tq_id','');
				$where1.=$this->db->quoteInto(" and d.type_id=?",$a['movie_type_id']);
			}
			if($a['diqu'])
			{
				$select->joinLeft('tq_movie_diqu_index as e','a.id=e.tq_id','');
				if($a['diqu']==9999) $where1.=$this->db->quoteInto(" and e.diqu_id IN (?)",array(9,15,16,18,19,20,21,22,23,24,25,26,27,28,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142,143,144,145,146,147,148,149,150,151,152,153,154,155,156,157,158,159,160,161,162,163,164,165,166));
				else $where1.=$this->db->quoteInto(" and e.diqu_id=?",$a['diqu']);
			}
			if($a['m_pingfen'])
			{
				$where1.=$this->db->quoteInto(" and b.pingfen>=?",$a['m_pingfen']);
			}
			if($a['year_min'] && $a['year_max'])
			{
				if($a['year_min']==$a['year_max']) $where1.=$this->db->quoteInto(" and f.year=?",$a['year_min']);
				else
				{
					$where1.=$this->db->quoteInto(" and f.year>=?",$a['year_min']);
					$where1.=$this->db->quoteInto(" and f.year<=?",$a['year_max']);
				}
			}
			$where1.=$this->db->quoteInto(" and b.state=?",1);
			if($like_mtime_id_in)
			{
				$where1.=$this->db->quoteInto(' and a.mtime_id IN (?)', $like_mtime_id_in);
			}
			else
			{
				$where1.=$this->db->quoteInto(" and a.type=?",0);
			}
		}
		
		$where2='1=1';
		if($a['type']>0 || $like_shuangtv_id_in || $search_tq_id_in) //非电影
		{
			$select->joinLeft('spider_shuangtv_movie_info as g','a.shuangtv_id_1=g.video_id','');
			$sh_dsj_types=self::get_shuangtv_dsj_types();
			if($a['dsj_diqu']) $where2.=$this->db->quoteInto(" and g.class=?",$sh_dsj_types[$a['dsj_diqu']]);
			if($like_shuangtv_id_in)
			{
				$where2.=$this->db->quoteInto(' and a.shuangtv_id_1 IN (?)', $like_shuangtv_id_in);
				if($a['type']==='0') $where2.=$this->db->quoteInto(" and a.type>?",0);
				else $where2.=$this->db->quoteInto(" and a.type=?",$a['type']);
			}
			else $where2.=$this->db->quoteInto(" and a.type=?",$a['type']);
		}
		
		if($like_tq_id_in)
		{
			$where3=$this->db->quoteInto('a.id IN (?)', $like_tq_id_in);
		}

		if(!$search_tq_id_in && $a['a_state']!=-1)
		{
			$where_out='';
			if($where2!='1=1' || ($a['like'] && $like_shuangtv_id_in))
			{
				$where_out.=' ('.$where2.') ';
			}
			if($where1!='1=1' || ($a['like'] && $like_mtime_id_in))
			{
				if($where_out) $where_out.='or ('.$where1.')';
				else $where_out=$where1;
			}
			if($where3)
			{
				$where_out.=' or ('.$where3.')';
			}

			if($where_out) $select->where($where_out);
		}

		if($a['a_state']==9999){}
		else if($a['a_state']) $select->where("a.state=?",$a['a_state']);
		else
		{
			if($a['like'] && $a['like']!='')
			{
				if($a['is_acl']) $select->where("a.state>=?",1);
				else $select->where("a.state=?",1);
			}
			else if($a['show_all_movie']==2)
			{
				$select->where("a.state=?",1);
			}
			else if($a['show_all_movie']==1)
			{
				$select->where("a.state>=?",1);
			}
			else
			{
				$select->where("a.state>=?",1);
			}
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
		$columns_a[]="f.year as tq_year";
		$columns_a[]="f.juqing as tq_juqing";
		$columns_a[]="f.shichang as tq_shichang";
		$columns_a[]="f.img_path as tq_img_path";
		$columns_a[]="f.img_file as tq_img_file";
		
		if((!$a['type'] && !$a['like']) || $like_mtime_id_in || $search_tq_id_in)	//电影
		{
			$columns_a[]="b.cname";
			$columns_a[]="b.ename";
			$columns_a[]="b.pianchang";
			$columns_a[]="b.pingfen as mt_pingfen";
			$columns_a[]="b.diqu_cname";
			$columns_a[]="b.juqing";
			$columns_a[]="b.img_path";
			$columns_a[]="b.img_base";
			//$columns_a[]="c.year";
			$columns_a[]="year(`c`.`shangyin_time`) as year";
		}
		if($a['type'] || $like_shuangtv_id_in || $search_tq_id_in)	//非电影
		{
			$columns_a[]='g.type as shuangtv_type';
			$columns_a[]='g.class';
			$columns_a[]='g.description';
			$columns_a[]='g.img_path as shuangtv_img_path';
		}
		
		$select->joinLeft('spider_douban_movie_info as h','a.douban_id=h.movie_id','');
		$columns_a[]='h.pianchang as db_pianchang';
		$columns_a[]='h.img_path as db_img_path';
		$columns_a[]='h.img_base as db_img_base';
		$columns_a[]='h.pingfen as db_pingfen';
		//$columns_a[]='h.juqing as db_juqing';
		$columns_a[]='h.imdb';

		$select->columns($columns_a);
		if($a['order']) $select->order($a['order']);
		else if($a['like']) $select->order('a.id desc');
		else if(!$a['type']) $select->order('a.pingfen desc');
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
			
			if($a['pagesize'])
			{
				if(!$a['type'])	//电影
				{
					$r['list'][$k]['like_name']=$mtime_like_names[$i['mtime_id']];
					if($i['shuangtv_id_1'])
					{
						$stv_in=null;
						$stv_in[]=$i['shuangtv_id_1'];
						if($i['shuangtv_id_2']) $stv_in[]=$i['shuangtv_id_2'];
						if($i['shuangtv_id_3']) $stv_in[]=$i['shuangtv_id_3'];
						$select = $this->db->select();
						$shuangtv_a=null;
						$shuangtv_a[]='type';
						$shuangtv_a[]='class';
						$shuangtv_a[]='description';
						$shuangtv_a[]='img_path';
						$select->from('spider_shuangtv_movie_info',$shuangtv_a);
						$select->where("video_id in (?)",$stv_in);
						$sql = $select->__toString();//echo $sql;exit;
						$r['list'][$k]['shuangtv']=$this->db->fetchAll($sql);
					}
				}

				if($i['type'])	//非电影
				{
					$select = $this->db->select();
					$select->from('spider_shuangtv_movie_name','name');
					$select->where("video_id = ?",$i['shuangtv_id_1']);
					$sql = $select->__toString();//echo $sql;exit;
					$r['list'][$k]['shuangtv_names']=$this->db->fetchAll($sql);
				}
				
			}

			//按电影名称排序
				$dyname=null;
				
				$sql="select name from tq_video_name where tq_id=".$r['list'][$k]['id_o']." order by sort desc limit 1";
				$dyname=$this->db->fetchOne($sql);
				if($dyname){}
				else if($r['list'][$k]['like_name']) $dyname=$r['list'][$k]['like_name'];
				else if($r['list'][$k]['cname']) $dyname=$r['list'][$k]['cname'];
				else if($r['list'][$k]['ename']) $dyname=$r['list'][$k]['ename'];
				else if($r['list'][$k]['shuangtv_names'])
				{
					foreach($r['list'][$k]['shuangtv_names'] as $k2=>$i2)
					{
						if($k2) $dyname.=' / ';
						$dyname.=$i2['name'];
					}
				}
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
			
			//搜索演员、导演时
				if($r_yanyuan)
				{
					$r['list'][$k]['can_yan']=$r_yanyuan[$i['id']];
				}
				else if($r_daoyan)
				{
					$r['list'][$k]['dao_yan']=$r_daoyan[$i['id']];
				}
			///
		}

		if(!$r['list']) return false;
		if($a['no_count']) return $r['list'];
	
		//print_R($r);exit;
		return $r;
	}
	
	//取演员名称列表
	function get_yanyuan_list($a=array(),$time=1800,$clear_cache=false) 
	{
		$o=new q_cache; 
		$o->k='tq_movie_class_get_yanyuan_listv1';
		$o->a=$a;
		$o->clear_cache=$clear_cache;
		$o->time1=$time;
		$o->step1();
		if($o->then==1)
		{
			$o->info=$this->get_yanyuan_list_db($a);
		}
		$o->step2();
		if($o->info) return $o->info; else return null;
	}
	public function get_yanyuan_list_db($a=array()) 
	{
		if(!$a['search']) return false;
		$where=$this->db->quoteInto(" like ?",'%'.$a['search'].'%');
		$sql="select distinct yan_yuan from ( ";
		$sql.="select distinct a.zhuyan_name as yan_yuan from spider_douban_movie_zhuyan a join tq_video_main_index b on a.movie_id=b.douban_id where a.zhuyan_name ".$where." and b.state=1 union ";
		$sql.="select distinct a.zhuyan_name as yan_yuan from spider_mtime_movie_zhuyan a join tq_video_main_index b on a.movie_id=b.mtime_id where a.zhuyan_name ".$where." and b.state=1 ";
		$sql.=" ) as c order by yan_yuan limit 60";
		//echo $sql;exit;
		$r=$this->db->fetchAll($sql);
		//print_R($r);exit;
		return $r;
	}

	//取导演名称列表
	function get_daoyan_list($a=array(),$time=1800,$clear_cache=false) 
	{
		$o=new q_cache; 
		$o->k='tq_movie_class_get_daoyan_listv1';
		$o->a=$a;
		$o->clear_cache=$clear_cache;
		$o->time1=$time;
		$o->step1();
		if($o->then==1)
		{
			$o->info=$this->get_daoyan_list_db($a);
		}
		$o->step2();
		if($o->info) return $o->info; else return null;
	}
	public function get_daoyan_list_db($a=array()) 
	{
		if(!$a['search']) return false;
		$where=$this->db->quoteInto(" like ?",'%'.$a['search'].'%');
		$sql="select distinct dao_yan from ( ";
		$sql.="select distinct a.daoyan_name as dao_yan from spider_douban_movie_info a join tq_video_main_index b on a.movie_id=b.douban_id where a.daoyan_name ".$where." and b.state=1 union ";
		$sql.="select distinct a.daoyan_name as dao_yan from spider_mtime_movie_info a join tq_video_main_index b on a.movie_id=b.mtime_id where a.daoyan_name ".$where." and b.state=1 ";
		$sql.=" ) as c order by dao_yan limit 60";
		//echo $sql;exit;
		$r=$this->db->fetchAll($sql);
		//print_R($r);exit;
		return $r;
	}
	
	function get_movie_detail($a=array(),$time=1800,$clear_cache=false) 
	{
		$o=new q_cache; 
		$o->k='tq_movie_class_get_movie_detailv1';
		$o->a=$a;
		$o->clear_cache=$clear_cache;
		$o->time1=$time;
		$o->step1();
		if($o->then==1)
		{
			$o->info=$this->get_movie_detail_db($a);
		}
		$o->step2();
		if($o->info) return $o->info; else return null;
	}
	public function get_movie_detail_db($a=array()) 
	{
		require_once COMM_PATH .'/lib/string/str10_62.class.php';
		$o1=new Base62;
		if($a['is_full_id']) $id=intval($a['id']);
		else $id=intval($o1->base62_decode($a['id']));
		if(!$id) return false;
		
		$select = $this->db->select();
		$select->from('tq_video_main_index', '*');
		$select->where("id = ?",$id);
		if($a['state']) $select->where("state = ?",$a['state']);
		$sql = $select->__toString();//echo $sql;exit;
		$r=$this->db->fetchRow($sql);
		$r['id_62']=$a['id'];
		
		if($r['mtime_id'])
		{
			$select = $this->db->select();
			$select->from('spider_mtime_movie_info as b','');
			$select->joinLeft('spider_mtime_movie_shangyin as c','b.movie_id=c.movie_id','');
			$select->reset('columns');
			$columns_a[]="b.cname";
			$columns_a[]="b.ename";
			$columns_a[]="b.daoyan_id";
			$columns_a[]="b.daoyan_name";
			$columns_a[]="b.pianchang";
			$columns_a[]="b.pingfen";
			$columns_a[]="b.diqu_cname";
			$columns_a[]="b.diqu_ename";
			$columns_a[]="b.company_id";
			$columns_a[]="b.company_name";
			$columns_a[]="b.juqing";
			$columns_a[]="b.huaxu";
			$columns_a[]="b.guanwang";
			$columns_a[]="b.img_path";
			$columns_a[]="b.img_base";
			$columns_a[]="year(c.shangyin_time) as year";
			$columns_a[]="month(c.shangyin_time) as month";
			$columns_a[]="day(c.shangyin_time) as day";
			$columns_a[]="c.shangyin_diqu";
			$select->columns($columns_a);
			$select->where("b.movie_id=?",$r['mtime_id']);
			$select->where("b.state=?",1);
			$sql = $select->__toString();//echo $sql;exit;
			$r['mtime']=$this->db->fetchRow($sql);

			//$select=$this->db->select();$select->from('spider_mtime_movie_comment','*');$select->where("movie_id=?",$r['mtime_id']);$sql=$select->__toString();//echo $sql;exit;
			//$r['mtime']['comment']=$this->db->fetchAll($sql);

			$select=$this->db->select();$select->from('spider_mtime_movie_huojiang','*');$select->where("movie_id=?",$r['mtime_id']);$sql=$select->__toString();//echo $sql;exit;
			$r['mtime']['huojiang']=$this->db->fetchAll($sql);

			$select=$this->db->select();$select->from('spider_mtime_movie_name','*');
			$select->where("movie_id=?",$r['mtime_id']);
			$select->order(array('main desc','lang'));
			$sql=$select->__toString();//echo $sql;exit;
			$name_list=$this->db->fetchAll($sql);
			foreach($name_list as $k=>$i)
			{
				if($k)
				{
					$r['mtime']['name'][$i['id']]=$i;
				}
				else
				{
					$r['mtime']['name'][0]=$i;
				}
			}

			$select=$this->db->select();$select->from('spider_mtime_movie_trailer','*');$select->where("movie_id=?",$r['mtime_id']);$sql=$select->__toString();//echo $sql;exit;
			$r['mtime']['trailer']=$this->db->fetchAll($sql);

			$select=$this->db->select();$select->from('spider_mtime_movie_yuyan','*');$select->where("movie_id=?",$r['mtime_id']);$sql=$select->__toString();//echo $sql;exit;
			$r['mtime']['yuyan']=$this->db->fetchAll($sql);

			$select=$this->db->select();$select->from('spider_mtime_movie_zhuyan','*');$select->where("movie_id=?",$r['mtime_id']);$sql=$select->__toString();//echo $sql;exit;
			$r['mtime']['zhuyan']=$this->db->fetchAll($sql);

			//$select=$this->db->select();$select->from('spider_mtime_movie_zixun','*');$select->where("movie_id=?",$r['mtime_id']);$sql=$select->__toString();//echo $sql;exit;
			//$r['mtime']['zixun']=$this->db->fetchAll($sql);

			$sql="select url as mtime_yp_url,title,time,`desc` from spider_mtime_movie_comment where movie_id=".intval($r['mtime_id'])." order by huifushu desc limit 20";
			$yingpiyc_mtime=$this->db->fetchAll($sql);
		}
		
		//直播列表信息
		$select = $this->db->select();
		$select->from('tq_play_key_list','*');
		$select->where("tq_id = ?",$r['id']);
		$select->where("state = ?",1);
		$select->order(array('series desc','episode desc'));
		$sql = $select->__toString();//echo $sql;exit;
		
		$play_info_tmp=null;
		$play_info_tmp[]=$this->db->fetchAll($sql);
		if($play_info_tmp && $play_info_tmp[0]) $r['is_play_info_pub']=true; //注明该电影的播放资源已经整合到 tq_play_key_list 表了。
		///

		//链播列表信息
		$select = $this->db->select();
		$select->from('tq_play_link_list','*');
		$select->where("tq_id = ?",$r['id']);
		$select->where("state = ?",1);
		$select->order(array('series desc','episode desc'));
		$sql = $select->__toString();//echo $sql;exit;
		
		$play_link_info_tmp=null;
		$play_link_info_tmp=$this->db->fetchAll($sql);
		if($play_link_info_tmp) $r['is_play_link_info_pub']=true; //注明该电影的播放资源已经整合到 tq_play_link_list 表了。
		///

		//额外ext:预告片、花絮等播列表信息
		$select = $this->db->select();
		$select->from('tq_play_ext_list','*');
		$select->where("tq_id = ?",$r['id']);
		$select->where("state = ?",1);
		$select->order(array('series desc','episode desc'));
		$sql = $select->__toString();//echo $sql;exit;
		
		$play_ext_info_tmp=null;
		$play_ext_info_tmp=$this->db->fetchAll($sql);
		if($play_ext_info_tmp) $r['is_play_ext_info_pub']=true; //注明该电影的播放资源已经整合到 tq_play_ext_list 表了。
		///

		//双视
		if($r['shuangtv_id_1'])
		{
			$stv_in=null;
			$stv_in[]=$r['shuangtv_id_1'];
			if($r['shuangtv_id_2']) $stv_in[]=$r['shuangtv_id_2'];
			if($r['shuangtv_id_3']) $stv_in[]=$r['shuangtv_id_3'];
			$select = $this->db->select();
			$shuangtv_a[]='type';
			$shuangtv_a[]='class';
			$shuangtv_a[]='description';
			$shuangtv_a[]='img_path';
			$select->from('spider_shuangtv_movie_info',$shuangtv_a);
			$select->where("video_id in (?)",$stv_in);
			$sql = $select->__toString();//echo $sql;exit;
			$r['shuangtv']=$this->db->fetchAll($sql);

			if(!$play_info_tmp || !$play_info_tmp[0]) foreach($stv_in as $k=>$i)
			{
				$select = $this->db->select();
				$select->from('spider_shuangtv_movie_info_list','*');
				$select->where("video_id = ?",$i);
				$select->where("state = ?",1);
				$select->order(array("series","episode desc"));
				$sql = $select->__toString();//echo $sql;exit;
				$play_info_tmp[]=$this->db->fetchAll($sql);
			}

			//if($i['type'])	//非电影
			//{
				$select = $this->db->select();
				$select->from('spider_shuangtv_movie_name','name');
				$select->where("video_id in (?)",$stv_in);
				$sql = $select->__toString();//echo $sql;exit;
				$r['shuangtv_names']=$this->db->fetchAll($sql);
			//}
		}

		$r['play_info']=null;

		foreach($play_info_tmp as $k=>$i)
		{
			/*
			if($i) foreach($i as $i2)
			{
				$from=self::get_shuangtv_key_from($i2['video_key']);
				$r['play_info'][$k][$from][]=$i2;
			}
			if($r['play_info'][$k]['bdhd'])
			{
				unset($r['play_info'][$k]['qvod']);
				$r['play_from_type']='bdhd';
			}
			*/
			if($i) foreach($i as $k2=>$i2)
			{
				$from=self::get_shuangtv_key_from($i2['video_key']);
				if($from=='bdhd') $r['play_from_type']='bdhd';
				$i[$k2]['play_from']=$from;
			}
			if($i) $r['play_info'][$k]['v']=$i;
		}
		
		//print_R($r['play_info']);exit;

		if(!$r['type'])
		{
			$r['type']=0;
		}
		$t_type1=self::get_video_type_s();
		$r['video_type']=$t_type1[$r['type']]['video_type'];
		$r['video_type_uri']=$t_type1[$r['type']]['video_type_uri'];

		
		//豆瓣
		if($r['douban_id'])
		{
			$select = $this->db->select();
			$douban_a=null;
			$douban_a[]='daoyan_id';
			$douban_a[]='daoyan_name';
			$douban_a[]='pianchang';
			$douban_a[]='img_path';
			$douban_a[]='img_base';
			$douban_a[]='imdb';
			$douban_a[]='pingfen';
			$douban_a[]='votes';
			$douban_a[]='juqing';
			$douban_a[]='guanwang';
			$select->from('spider_douban_movie_info',$douban_a);
			$select->where("movie_id = ?",$r['douban_id']);
			$sql = $select->__toString();//echo $sql;exit;
			$r['douban']=$this->db->fetchRow($sql);

			if($r['douban']['imdb'])
			{
				if(!$r['imdb_id']) $r['imdb_id']=$r['douban']['imdb'];

				$select = $this->db->select();
				$select->from('spider_imdb_movie_info','pingfen');
				$select->where("movie_id = ?",$r['douban']['imdb']);
				$sql = $select->__toString();//echo $sql;exit;
				$r['imdb']['pingfen']=$this->db->fetchOne($sql);
			}
			
			$sql="select yp_id as douban_yp_id,title,yp_short as `desc`,unix_timestamp(time) as time from spider_douban_movie_yingping where movie_id=".intval($r['douban_id'])." order by good desc limit 20";
			$yingpiyc_douban=$this->db->fetchAll($sql);
		}
		///

		//mbaidu
		if($r['mbaidu_id'])
		{
			$select = $this->db->select();
			$mbaidu_a=null;
			$mbaidu_a[]='brief';
			$mbaidu_a[]='is_play';
			$mbaidu_a[]='pc_detail_url';
			$select->from('spider_mbaidu_movie_info',$mbaidu_a);
			$select->where("movie_id = ?",$r['mbaidu_id']);
			$sql = $select->__toString();//echo $sql;exit;
			$r['mbaidu']=$this->db->fetchRow($sql);

			if($r['mbaidu']['is_play'] && !$play_link_info_tmp)
			{
				$select = $this->db->select();
				$mbaidu_a=null;
				$mbaidu_a[]='url';
				$mbaidu_a[]='site_logo';
				$mbaidu_a[]='site_name';
				$select->from('spider_mbaidu_movie_sites_episode',$mbaidu_a);
				$select->where("movie_id = ?",$r['mbaidu_id']);
				$select->where("state = ?",1);
				$sql = $select->__toString();//echo $sql;exit;
				$t_play2=$this->db->fetchAll($sql);
				if($t_play2) foreach($t_play2 as $i)
				{
					$play_link_info_tmp[md5($i['url'])]=$i;
				}
			}

			if(!$play_ext_info_tmp)
			{
				//花絮等
				$select = $this->db->select();
				$mbaidu_a=null;
				$mbaidu_a[]='sort';
				$mbaidu_a[]='title';
				$mbaidu_a[]='url';
				$mbaidu_a[]='duration';
				$mbaidu_a[]='type';
				$select->from('spider_mbaidu_movie_ext_videos',$mbaidu_a);
				$select->where("movie_id = ?",$r['mbaidu_id']);
				$select->where("state = ?",1);
				$select->order('sort desc');
				$sql = $select->__toString();//echo $sql;exit;
				$t_play1=$this->db->fetchAll($sql);
				if($t_play1) foreach($t_play1 as $i)
				{
					
					$play_ext_info_tmp[md5($i['url'])]=$i;
				}
				///
			}

			//别名
				$select = $this->db->select();
				$mbaidu_a=null;
				$mbaidu_a[]='alias';
				$select->from('spider_mbaidu_movie_alias',$mbaidu_a);
				$select->where("movie_id = ?",$r['mbaidu_id']);
				$sql = $select->__toString();//echo $sql;exit;
				$r['mbaidu']['alias']=$this->db->fetchAll($sql);
			///
		}
		///

		if($play_link_info_tmp) 
		{
			foreach($play_link_info_tmp as $k3=>$i3)
			{
				if(!$i3['series']) $play_link_info_tmp[$k3]['series']=1;
				if(!$i3['episode']) $play_link_info_tmp[$k3]['episode']=1;
			}
			$r['play_link_info']=$play_link_info_tmp;
		}

		if($play_ext_info_tmp) 
		{
			foreach($play_ext_info_tmp as $k3=>$i3)
			{
				if(!$i3['series']) $play_ext_info_tmp[$k3]['series']=1;
				if(!$i3['episode']) $play_ext_info_tmp[$k3]['episode']=1;
			}
			$r['play_ext_info']=$play_ext_info_tmp;
		}

		if(!$yingpiyc_mtime) $yingpiyc_mtime=array();
		if(!$yingpiyc_douban) $yingpiyc_douban=array();
		$r['yingping']=array_merge($yingpiyc_mtime,$yingpiyc_douban);
		if($r['yingping'])
		{
			foreach($r['yingping'] as $k=>$i)
			{
				$i_t[$i['time']]=$i;
			}
			krsort($i_t);
			$r['yingping']=$i_t;
		}
		
		//详细介绍
			$select = $this->db->select();
			$select->from('tq_video_xxjs_html','*');
			$select->where("tq_id = ?",$r['id']);
			$select->order('right desc');
			$sql = $select->__toString();//echo $sql;exit;
			$r['xxjs']=$this->db->fetchAll($sql);
		///
		
		//取导演
			$select = $this->db->select();
			$select->from('tq_video_daoyan','name');
			$select->where("tq_id = ?",$r['id']);
			$sql = $select->__toString();//echo $sql;exit;
			$r['tq_daoyan']=$this->db->fetchAll($sql);
		///

		//取主演
			$select = $this->db->select();
			$select->from('tq_video_zhuyan','name as zhuyan_name');
			$select->where("tq_id = ?",$r['id']);
			$sql = $select->__toString();//echo $sql;exit;
			$r_tq_zy=$this->db->fetchAll($sql);
		///

		//统一主演
			$zhuyan_s=null;
			if($r_tq_zy)
			{
				foreach($r_tq_zy as $k=>$d)
				{
					$d['zhuyan_name']=htmlspecialchars_decode(stripslashes($d['zhuyan_name']), ENT_QUOTES);
					$zhuyan_s[$d['zhuyan_name']]=$d;
				}
			}
			if($r['mtime']['zhuyan'])
			{
				foreach($r['mtime']['zhuyan'] as $k=>$d)
				{
					$d['zhuyan_name']=htmlspecialchars_decode(stripslashes($d['zhuyan_name']), ENT_QUOTES);
					$zhuyan_s[$d['zhuyan_name']]=$d;
				}
			}
			if ($zhuyan_s) rsort($zhuyan_s);
			$r['tq_zhuyan']=$zhuyan_s;
		///

		//取年份、时长、评分
			$select = $this->db->select();
			$select->from('tq_video_info','*');
			$select->where("tq_id = ?",$r['id']);
			$sql = $select->__toString();//echo $sql;exit;
			$r_tq_info=$this->db->fetchRow($sql);
		///

		if($r_tq_info) $r['tq']=$r_tq_info;

		//统一年份
			if($r_tq_info['year']) $r['tq']['year']=$r_tq_info['year'];
			else if($r['mtime']['year']) $r['tq']['year']=$r['mtime']['year'];

			if($r_tq_info['month']) $r['tq']['month']=$r_tq_info['month'];
			else if($r['mtime']['month']) $r['tq']['month']=$r['mtime']['month'];

			if($r_tq_info['day']) $r['tq']['day']=$r_tq_info['day'];
			else if($r['mtime']['day']) $r['tq']['day']=$r['mtime']['day'];
		///
		
		//取地区
			$sql="select id,name,name_cn,name_en from tq_movie_diqu a join tq_movie_diqu_index b on a.id=b.diqu_id where b.tq_id=".$r['id'];
			$r_t_dq=$this->db->fetchRow($sql);
			 if($r_t_dq)
			{
				$r['tq']['diqu']=$r_t_dq;
			}
		///

		//取名
			$sql="select name,sort from tq_video_name where tq_id=".$r['id']." order by sort desc";
			$r_t_names=$this->db->fetchAll($sql);
			 if($r_t_names)
			{
				$r['tq']['name']=$r_t_names[0]['name'];
			}
		///

		//统一别名
			$ymin=null;
			if($r_t_names)
			{
				foreach($r_t_names as $k=>$d)
				{
					$tn1=htmlspecialchars_decode($d['name'], ENT_NOQUOTES);
					$ymin[$tn1]=$tn1;
				}
			}
			if($r['mtime']['name'])
			{
				foreach($r['mtime']['name'] as $k=>$d)
				{
					$tn1=htmlspecialchars_decode($d['name'], ENT_NOQUOTES);
					$ymin[$tn1]=$tn1;
				}
			}
			if($r['shuangtv_names'])
			{
				foreach($r['shuangtv_names'] as $k=>$d)
				{
					$tn1=htmlspecialchars_decode($d['name'], ENT_NOQUOTES);
					$ymin[$tn1]=$tn1;
				}
			}
			if($r['mbaidu']['alias'])
			{
				foreach($r['mbaidu']['alias'] as $k=>$d)
				{
					$tn1=htmlspecialchars_decode($d['alias'], ENT_NOQUOTES);
					$ymin[$tn1]=$tn1;
				}
			}
			rsort($ymin);
			$r['tq_alias']=$ymin;
		///

		//取分类
		$sql="select * from tq_movie_type a join tq_movie_type_index b on a.id = b.type_id where b.tq_id=".$r['id']." order by `right` desc";
		$r['tq']['type']=$this->db->fetchAll($sql);
		if(!$r['tq']['type'])
		{
			$sql="select type_cname from spider_mtime_movie_type where movie_id=".intval($r['mtime_id']);
			$t_types=$this->db->fetchAll($sql);
			//print_R($t_types);exit;
			if($t_types)
			{
				$t_mtps=$this->get_movie_types();
				$t_tp_act=false;
				foreach($t_types as $ttpk=>$ttpi)
				{
					foreach($t_mtps as $tmpk=>$tmpi)
					{
						if($tmpi['type']==$ttpi['type_cname'] || $tmpi['type_j']==$ttpi['type_cname'])
						{
							$sql="insert ignore into tq_movie_type_index(tq_id,type_id)values(".$r['id'].",".$tmpi['id'].")";
							$this->db->query($sql);
							$t_tp_act=true;
							break;
						}
					}
				}
				if($t_tp_act)
				{
					$sql="select * from tq_movie_type a join tq_movie_type_index b on a.id = b.type_id where b.tq_id=".$r['id']." order by `right` desc";
					$r['tq']['type']=$this->db->fetchAll($sql);
				}
			}
		} 
		///

		//更新评分
			if(time()-intval($r['pingfen_time'])>86400*10 || $_GET['__DEBUG'] ==1)	//10天更新
			{
				$u_pingfen=0;
				$u_i=0;
				if($r['tq']['pingfen']) {$u_i++;$u_pingfen+=$r['tq']['pingfen'];}
				if($r['mtime']['pingfen']) {$u_i++;$u_pingfen+=$r['mtime']['pingfen'];}
				if($r['douban']['pingfen']) {$u_i++;$u_pingfen+=$r['douban']['pingfen'];}
				if($r['imdb']['pingfen']) {$u_i++;$u_pingfen+=$r['imdb']['pingfen'];}
				if($u_pingfen && $u_pingfen!=$r['pingfen'])
				{
					$u_pingfen=number_format($u_pingfen/$u_i,1);
					$sql="update tq_video_main_index set pingfen=".$u_pingfen.",pingfen_time=".time()." where id=".$r['id'];	//die($sql);
					$this->db->query($sql);
					$r['pingfen']=$u_pingfen;
				}
			}
		///

		//print_R($r);exit;
		return $r;
	}
	
	//通过双视的video_key，获得资源的来源平台
	static function get_shuangtv_key_from($key)
	{
		if(strpos($key,'bdhd:')!== false || strpos($key,'ed2k:')!== false || strpos($key,'ftp:')!== false) return 'bdhd';
		else if(strpos($key,'qvod:')!== false) return 'qvod';
		else return 'youku';
	}

	public function update_movie($a=array()) 
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
		return $this->db->update('tq_video_main_index',$a,$where);
	}

	static function get_shuangtv_dsj_types($flip=null)
	{
		$r[1]="大陆电视剧";
		$r[2]="韩国电视剧";
		$r[3]="日本电视剧";
		$r[4]="台湾电视剧";
		$r[5]="美国电视剧";
		$r[6]="泰国电视剧";
		$r[7]="香港电视剧";
		if($flip) $r=array_flip($r);
		return $r;
	}
	static function get_shuangtv_dsj_jian_s($flip=null)
	{
		$r[1]="内地";
		$r[2]="韩剧";
		$r[3]="日剧";
		$r[4]="台剧";
		$r[5]="美剧";
		$r[6]="泰剧";
		$r[7]="TVB";
		if($flip) $r=array_flip($r);
		return $r;
	}
	
	static function get_video_type_s()
	{
		$r[0]['video_type']='电影';
		$r[0]['video_type_uri']='v-l';
		$r[1]['video_type']='电视剧';
		$r[1]['video_type_uri']='v-l-t-1';
		$r[2]['video_type']='综艺';
		$r[2]['video_type_uri']='v-l-t-2';
		$r[3]['video_type']='动漫';
		$r[3]['video_type_uri']='v-l-t-3';
		return $r;
	}

	public function get_video_count_db()
	{
		$sql='select count(*) from tq_video_main_index';
		$r=$this->db->fetchOne($sql);
		return $r;
	}
	public function get_video_count($time=300,$clear_cache=false)
	{
		$key = md5('tq_movie_class_get_video_countv1'.$time); 

		$cache = new temp_cache; 

		$info = $cache->get($key);

		if (!$info || $_GET['__DEBUG'] == 1 || $clear_cache){
			$info=$this->get_video_count_db();
			if($info) $cache->saveOrUpdate($key,$info,$time); 
		} 
		$cache->close();
		if($info) return $info; else return null;  
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
		$r[2]="聚合关于篮球的各种热帖";
		$r[3]="篮球比赛直播、录播资源";
		if($flip) $r=array_flip($r);
		return $r;
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
		
		//$sql="select count(distinct year,month,day) from (select * from tq_video_meirizuixin order by id desc limit 1000) as a where key1=".intval($a['key1']);
		//$n2=$this->db->fetchOne($sql);
		$sql="select distinct year,month,day from (select * from tq_video_meirizuixin order by id desc limit 1000) as a where key1=".intval($a['key1']);
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
			$sql="select * from tq_video_meirizuixin where year=".$d['year']." and month=".intval($d['month'])." and day=".intval($d['day'])." and key1=".intval($a['key1'])." order by sort desc,create_time desc";
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
		$select->from('tq_video_meirizuixin', '*');
		$select->where("key1 = ?",$a['key1']);
		$select->order($a['order']);
		$select->limit($limit[1],$limit[0]);
		$sql = $select->__toString();//echo $sql;exit;
		$r=$this->db->fetchAll($sql);
		//if($r['html']) $r['html']=stripslashes($r['html']);

		return $r;
	}
	
	//抓取总方法
	public function spider_video($a=array())
	{
		return true; //mitme 也改版了，暂时不抓了。2014/4/9
		if(!$a || !$a['type']) return false;
		ini_set("magic_quotes_runtime", 0);
		date_default_timezone_set("PRC");
		ignore_user_abort();
		set_time_limit(0);
		error_reporting(7); 
		ini_set('display_errors','on');
		require_once COMM_PATH .'/lib/proxy/proxy.adsl.class.php';
		require_once COMM_PATH . '/comm.functions.php';
		$robot_path=COMM_PATH.'/../robots/tqdianying';
		if($a['type']=='mtime' && $a['id'])
		{
			require_once $robot_path.'/spider_mtime_url_index_main/base_class.php';
			$o=new spider_for_spider_mtime_gai_lan;
			$x=null;
			$x['mtime_movie_url_id']=$a['id'];
			$r=$o->spider($x);
			if(!$r) die('spider_for_spider_mtime_gai_lan spider error!');

			$r=null;
			$o=new spider_for_spider_mtime_comment;
			$r=$o->spider($x);
			if(!$r) die('spider_for_spider_mtime_comment spider error!');
			
			$r=null;
			$o=new spider_for_spider_mtime_trailer;
			$r=$o->spider($x);
			if(!$r) die('spider_for_spider_mtime_trailer spider error!');
			
			$r=null;
			$o=new analyse_spider_mtime_html;
			$sql="select html from tqdianyiyc_spider_html.spider_mtime_html_gai_lan where movie_id=".intval($x['mtime_movie_url_id']);
			$x['html']=$this->db->fetchOne($sql);
			if($x['html']) $r=$o->analyse($x);
			if(!$r) die('analyse_spider_mtime_html spider error!');
			
			$r=null;
			$o=new imgs_deal_with_mtime;
			$sql="select img_path,img_base from spider_mtime_movie_info where movie_id=".intval($x['mtime_movie_url_id']);
			$r2=$this->db->fetchRow($sql);
			if($r2)
			{
				$x['img_path']=$r2['img_path'];
				$x['img_base']=$r2['img_base'];
				$r=$o->deal($x);
			}
			if(!$r) die('imgs_deal_with_mtime spider error!');
		}
		else if($a['type']=='shuangtv' && $a['id'])
		{
			/* 2014/1/9 双视倒闭 停抓
			require_once $robot_path.'/spider_shuangtv/base_class.php';
			$sql="delete from spider_shuangtv_url_index_main where movie_id=".$a['id'];$this->db->query($sql);
			$sql="replace into spider_shuangtv_url_index_main(movie_id,series)values(".$a['id'].",1)";$this->db->query($sql);
			$sql="replace into spider_shuangtv_url_index_main(movie_id,series)values(".$a['id'].",2)";$this->db->query($sql);
			$sql="replace into spider_shuangtv_url_index_main(movie_id,series)values(".$a['id'].",3)";$this->db->query($sql);
			$sql="replace into spider_shuangtv_url_index_main(movie_id,series)values(".$a['id'].",4)";$this->db->query($sql);
			$sql="replace into spider_shuangtv_url_index_main(movie_id,series)values(".$a['id'].",5)";$this->db->query($sql);
			$i=1; 
			while($i<1000) 
			{
				$r=null;
				$o=new spider_for_spider_shuangtv_url_index_main;
				$r=$o->get_1_process();
				if($r=='none_process') break;
				else if($r) $o->spider($r);
			}
			
			$o=new analyse_spider_shuangtv_html;
			$sql="select * from tqdianyiyc_spider_html.spider_shuangtv_html where movie_id=".$a['id'];
			$r2=$this->db->fetchAll($sql);
			$x=null;
			if($r2) foreach($r2 as $k=>$x)
			{
				$r=$o->analyse($x);
				if(!$r) die('analyse_spider_shuangtv_html run error!');
			}
			
			$i=1; 
			while($i<1000) 
			{
				$r=null;
				$o=new spider_for_spider_shuangtv_spider_img;
				$r=$o->get_1_process();
				if($r=='none_process') break;
				else $o->spider($r);
			}
			*/
		}
	}
	
}//end class