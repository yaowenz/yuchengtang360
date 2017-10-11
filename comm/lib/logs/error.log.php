<?
/**
 * 记录错误日志
 * created by jay 2011-2-16
 */
require_once dirname(__FILE__).'/simple.mq.php';
require_once dirname(__FILE__).'/../ip/ip.class.php';
require_once dirname(__FILE__).'/../db/db.class.php'; 
class error_logs{

	/*
	 * 记录错误日志
	 * @param $sn int 错误号
	 * @param $filename string 文件名
	 * @return void
	 */
	public static function write($sn,$filename,$print_sn = true){
		if($print_sn){
			echo '<!--'.$sn.'-->';
		}

		$key = 'error_log_msg_key'; //分类识别用的key
		$max = 10;  //阀值,达到阀值就会将所有数据以数组方式返回，否则返回空数组

		$ip = ip_class::get_ip();
		$dateline = time();
		$data = array();
		$data['sn'] = $sn;
		$data['filename'] = $filename;
		$data['ip'] = $ip;
		$data['dateline'] = $dateline;

		$smq = new simpleMQ($key,$max);
		$datalist = $smq->saveData($data);
		if(is_array($datalist) && !empty($datalist)){
			//print_r($datalist);
			$ar = cm_db_config::get_ini('ncg_single');
			$db = cm_db::get_db($ar);
			$table = 'error_log';
			$smq->saveto_db($datalist,$db,$table);
		}
	}

	/*
	 * 将错误日志的数据存入DB内
	 
	public static function saveto_db($datalist){
		$ar = cm_db_config::get_ini('ncg_single');
		$db = cm_db::get_db($ar);

		$table = 'error_log';

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
			//$ret = $db->insert($table,$d);
			//echo '<br/>------------------------<br/>';
		}
		if(substr($values,-1)==','){
			$values = substr($values,0,-1);
		}
		$sql = "INSERT INTO `$table` $field VALUES $values";
		//$sql = $db->quoteInto($sql,$param);
		//echo $sql;
		try{
			$db->query($sql);
			return true;
		}catch(Exception $e){
			self::alarm($error_msg);
			return false;
		}
	}
	*/

	public function __destruct(){}
}
?>