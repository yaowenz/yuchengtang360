<?php
/*
 * 文件操作类
 *
 */

require_once dirname(__FILE__).'/../string/string.class.php';
require_once dirname(__FILE__).'/../cache/cache.class.php';
class files{
	static $base_path; 
	static $base_url; 
	static $error;
	static $ok;

	/*
	 * 从缓存读取指定文件的内容
	 * @parameter string $dirname
	 * @return array
	 */
	static function read_file_from_cach($filesrc){
		$key = md5('read_file_'.$filesrc);
		$expire = 60*60;
		$cache = new temp_cache; 
		$str = $cache->get($key); 
		if (empty($str) || $_GET['__DEBUG'] == 1){ 
			$str = self::read_file($filesrc);
			if(!$str)return false; else $cache->saveOrUpdate($key,$str,$expire);
		} 
		$cache->close();
		if($str) return $str; else return false;
	}


	/*
	 * 获得指定文件的内容
	 * @parameter string $filesrc
	 * @return array
	 */
	static function read_file($filesrc){
		if(file_exists($filesrc)){ 
			$string = file_get_contents($filesrc);
			return $string;
		} else {
			echo "<!--$filesrc 不存在-->";
			return false;
		}
	}
	
	static function write_file($filename,$somecontent)
	{ 
		if (!$handle = fopen($filename, 'w')) {
			 self::$error="不能打开文件 $filename";
			 return false;
		}
		if (fwrite($handle, $somecontent) === FALSE) {
			self::$error= "不能写入到文件 $filename";
			return false;
		}
		self::$ok= "成功地将 $somecontent 写入到文件$filename";
		fclose($handle);
		return true;
	}
	/*****************
	 * 读取CSS文件内容
	 *****************/

	/*
	 * 从缓存读取指定CSS文件的内容
	 * @parameter string $dirname
	 * @return array
	 */
	static $css = '';
	static $css_tag = 'read_css_tag';
	static function read_css($file_name,$print_now = false){
		$key = md5('read_css_'.$file_name);
		$expire = 60*60;
		$cache = new temp_cache; 
		$file_content = $cache->get($key); 
		if (empty($file_content)){
			$filesrc = self::$base_path.'/css/'.$file_name; 
			$file_content = self::read_file($filesrc);
			if(!$file_content)return false; 
			if(self::$base_url) $file_content = str_replace("../files", self::$base_url, $file_content);
			$cache->saveByTag($key,$file_content,md5(self::$css_tag),$expire);
		}
		$cache->close();

		if($print_now){
			echo "<style type=\"text/css\">" . $file_content . "</style>";
		} else {
			$str = trim(self::$css);
			if($str == ''){
				$str = "<style type=\"text/css\">";
			}else{
				if(substr($str,-8,8) == '</style>'){
					$str = substr($str,0,-8);
				}
			}
			$str .= $file_content . "</style>";
			self::$css = $str;
		}
		//if($str) return $str; else return false;
	}

	static function remove_css_cach_all(){
		$cache = new temp_cache;
		$cache->removeTag(md5(self::css_tag));
		$cache->close();
	}

	static function remove_css_cach($file_name){
		$key = md5('read_css_'.$file_name);
		$cache = new temp_cache;
		$cache->delete($key);
		$cache->close();
	}

	/*****************
	 * 读取JS文件内容
	 *****************/
	/*
	 * 从缓存读取指定JS文件的内容
	 * @parameter string $dirname
	 * @return array
	 */
	static $js = '';
	static $js_tag = 'read_js_tag';
	static function read_js($file_name,$print_now = false){ 
		$key = md5('read_js_'.$file_name);
		$expire = 60*60;
		$cache = new temp_cache;  
		$file_content = $cache->get($key); 
		if (empty($file_content)){
			$filesrc = self::$base_path.'/js/'.$file_name;
			$file_content = self::read_file($filesrc);
			if(!$file_content)return false; 
			if(self::$base_url) $file_content = str_replace("../files", self::$base_url, $file_content); 
			$cache->saveByTag($key,$file_content,md5(self::$js_tag),$expire);
		}
		$cache->close();

		if($print_now){
			echo "<script type=\"text/javascript\" language=\"javascript\">" . $file_content . '</script>';
		} else {
			$str = trim(self::$js);
			if($str == ''){
				$str = "<script type=\"text/javascript\" language=\"javascript\">";
			}else{
				if(substr($str,-9,9) == '</script>'){
					$str = substr($str,0,-9);
				}
			}
			$str .= $file_content . "</script>";
			self::$js = $str;
		}
	}

	static function remove_js_cach_all(){
		$cache = new temp_cache;
		$cache->removeTag(md5(self::$js_tag));
		$cache->close();
	}

	static function remove_js_cach($file_name){
		$key = md5('read_js_'.$file_name);
		$cache = new temp_cache;
		$cache->delete($key);
		$cache->close();
	}

		//读取CSS文件内容
	/*
	static function read_css($file_name,$print_now = false){
		$filesrc = DOCUMENT_ROOT.'/css/'.$file_name;
		$str = trim(self::$css);

		if($str == ''){
			$str = "<style type=\"text/css\">";
		}else{
			if(substr($str,-8,8) == '</style>'){
				$str = substr($str,0,-8);
			}
		}
		$str .= self::read_file($filesrc) . "</style>";
		self::$css = $str;
	}
	*/
	
	/*
	//读取JS文件内容
	static function read_js($file_name,$print_now = false){
		$filesrc = DOCUMENT_ROOT.'/js/'.$file_name;
		$str = trim(self::$js);

		if($str == ''){
			$str = "<script type=\"text/javascript\" language=\"javascript\">";
		}else{
			if(substr($str,-9,9) == '</script>'){
				$str = substr($str,0,-9);
			}
		}
		$str .= self::read_file($filesrc) . "</script>";
		self::$js = $str;
	}
	*/

	static function unzip($dir,$file){
		if(!is_file($dir.'/'.$file)) return false;
		require_once dirname(__FILE__).'/pclzip.lib.php';
		$archive = new PclZip($dir.'/'.$file); 
		$rt=$archive->extract($dir); 
		return $rt;
	} 
	
	static function zip($dir,$file){
		require_once dirname(__FILE__).'/pclzip.lib.php';
		$archive = new PclZip($dir.'/'.$file.'.zip'); 
		$rt = $archive->create($dir,PCLZIP_OPT_REMOVE_ALL_PATH);
		//echo $rt.'</br>'.$dir;exit;
		if ($rt == 0) {
			die("Error : ".$archive->errorInfo(true));
		}
		return $rt;
	}

	//从 http 获取 文件url ，把文件(图片) 写入本地，并返回 path 
	static function get_local_path($url,$tail)
	{
		if(!$url) return false;
		$key = md5($url);
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10);
		$out = curl_exec($ch);
		if(!$out) return false;
		require_once dirname(__FILE__) . '/../../../config/comm.config.php';
		require_once dirname(__FILE__) .'/../spider/my_spider/tsina.login.php';
	
		$t_b_path = comm_config::$tmp_path;
		$tag='/get_local_path'; 
		$dir = my_spider::mkdir_by_date(time(),$t_b_path,$tag);  
		$dir=realpath($dir);
		if(!$dir) 
		{
			return false;
		}
		$fname=$dir.'/'.$key.'.'.$tail;  

		$r=self::write_file($fname,$out);  
		if(!$r){
			return false;
		}
		
		return $fname;
	}



}//end class
