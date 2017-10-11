<?
/**
 * 简单队列入库
 * created by jay 2011-2-16
 */
require_once dirname(__FILE__).'/../cache/cache.class.php'; 
class simpleMQ{
	
	//阀值
	private $max=1;

	//识别KEY
	private $key;

	/*
	 * 初始化
	 * @parameter string 识别KEY
	 * @parameter int 阀值
	 */
	public function __construct($key,$max){
		$this->key = $key;
		$this->max = $max;
	}

	/*
	 * 将数据存入cachDB内，满足阀值后，将数据返回并清空cachDB
	 * @parameter string,array 要存储的数据
	 * @return array
	 */
	public function saveData($data) {
		if(empty($data)){
			return null;
		}
		if(is_array($data)){
			$str = serialize($data);
		}else{
			$str = $data;
		}
		$str = serialize($data);
		$dtime = gettimeofday();
		$strKey = md5('SimpleMQ_str'.$str.$dtime[usec]);
		$countKey = md5('SimpleMQ_count'.$this->key);
		$tag = md5('SimpleMQ_tag'.$this->key);

		$cachDB = new seria_cache();
		$count = $cachDB->get($countKey);
		if(!$count){
			$count = 0;
		}

		$count++;
		//入chachDB
		$cachDB->saveByTag($strKey,$str,$tag);
			
		$datalist = array();
		if(!($cachDB->get($strKey))){
			$datalist[] = unserialize($str);
			return $datalist;
		}

		$cachDB->saveOrUpdate($countKey,$count);

		//echo '<!--'.$this->max.':'.$count.'-->';
		//计数器
		$strList = array();
		if($count >= $this->max){
			//获取数组
			$strList = $cachDB->getByTag($tag);
			$cachDB->removeTag($tag);
			$cachDB->delete($countKey);
		}

		foreach($strList as $lst){
			$data_ary = unserialize($lst);
			if(!empty($data_ary)){
				$datalist[] = $data_ary;
			}
		}

		return $datalist;
	}
	
	/*
	 * 将错误日志的数据存入DB内
	 */
	public function saveto_db($datalist,$db,$table){
		$count = 0;
		foreach($datalist as $d){
			$count++;
			if($count == 1){
				$field = '(';
			}
			$values .= '(';
			foreach($d as $key=>$value){
				if($count == 1){
					$field .=  "`$key`,";
				}
				$values .= "'$value',";
			}

			if($count == 1){
				if(substr($field,-1)==','){
					$field = substr($field,0,-1);
				}
				$field .= ')';
			}
			
			if(substr($values,-1)==','){
				$values = substr($values,0,-1);
			}
			$values .= '),';
		}
		if(substr($values,-1)==','){
			$values = substr($values,0,-1);
		}
		$sql = "INSERT INTO `$table` $field VALUES $values"; //print_R($sql);exit; 
		try{
			$db->query($sql);
			return true;
		}catch(Exception $e){
			self::alarm($error_msg);
			return false;
		}
	}

	/*
	 * 出现错误的情况下报警
	 */
	public static function alarm($error_msg){
		
	}


	/*
	 * 将数据存入cachDB内，满足阀值后，将数据返回并清空cachDB
	 * @parameter string,array 要存储的数据
	 * @return array
	 */
	public function update_Data_bytime($data) {
		/*
		if(empty($data)){
			return null;
		}
		if(is_array($data)){
			$str = serialize($data);
		}else{
			$str = $data;
		}
		
		$strKey = md5('SimpleMQ_update_data'.$str);
		$timeKey = md5('SimpleMQ_update_time'.$this->key);
		$tag = md5('SimpleMQ_update_time_tag'.$this->key);

		$cachDB = new seria_cache();

		$count = $cachDB->get($countKey);
		if(!$count){
			$count = 0;
		}

		$count++;
		//入chachDB
		$cachDB->saveByTag($strKey,$str,$tag);
		$cachDB->saveOrUpdate($countKey,$count);

		//计数器
		$strList = array();
		if($count >= $this->max){
			//获取数组
			$strList = $cachDB->getByTag($tag);
			$cachDB->removeTag($tag);
			$cachDB->delete($countKey);
		}
		$datalist = array();
		foreach($strList as $lst){
			$datalist[] = unserialize($lst);
		}

		return $datalist;
		*/
	}

	/*
	 * 将二维数组转成字符串
	 * @parameter array 要转换的二维数组
	 * @return string
	
	public function array_to_string($datalist){
		$datalist = array();
		$str = '';
		foreach($datalist as $data){
			foreach($data as $key=>$value){
				$str .= $key.':'.$value.',';
			}
			if($substr($str,-1) == ','){
				$str = $substr($str,0,-1);
			}
			$str .= '|';
		}
		if($substr($str,-1) == '|'){
			$str = $substr($str,0,-1);
		}
		return $str;
	}
 */
	/*
	 * 将字符串转成二维数组
	 * @parameter string 要转换的二字符串
	 * @return array
	
	public function string_to_array($datastr){
		$datalist = array();
		$data_row = explode("|", $datastr);
		foreach($data_row as $dr){
			$data = array();
			$data_column = explode(",", $dr);
			foreach($data_column as $dc){
				$ary = explode(":", $dc);
				$data[$ary[0]] = $ary[1];
			}
			$datalist = $data;
		}
		return $datalist;
	}
 */
	public function __destruct(){}
}
?>