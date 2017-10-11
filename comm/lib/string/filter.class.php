<?php
/**
	edit by zx 2010-8-6 from czdz
 */
require_once dirname(__FILE__).'/../db/db.class.php'; 
require_once dirname(__FILE__).'/../cache/cache.class.php'; 

define('cm_CENSOR_SUCCEED', 0);
define('cm_CENSOR_BANNED', 1);
define('cm_CENSOR_MODERATED', 2);
define('cm_CENSOR_REPLACED', 3);

class cm_censor {

	static $key_base = 'cm_COMM_CENSOR_CACHEv71';//缓存key全局设置

	var $table = 'common_word';
	var $censor_words = array();
	var $bbcodes_display;
	var $result;

	//从cache取过滤规则列表  
	static function get_censor_list(){ 
		$key = self::$key_base;
		$time = 3600;
		$cache = new temp_cache;
		$censor_list = $cache->get($key);
		
		if (empty($censor_list) || $_GET['__DEBUG'] == 1){
			$censor_list = self::get_censor_from_db();
			$cache->saveOrUpdate($key,$censor_list,$time);
		}
		$cache->close();
		if($censor_list) return $censor_list; else return false;
	}

	//过滤规则列表翻译   
	static function censor_translate($censor_list){ 
		$banned = $mod = array();
		$data = array('filter' => array(), 'banned' => '', 'mod' => '');
		foreach($censor_list as $censor){ 
			if(preg_match('/^\/(.+?)\/$/', $censor['find'], $a)) {
				switch($censor['replacement']) {
					case '{BANNED}':
						$data['banned'][] = $censor['find'];
						break;
					case '{MOD}':
						$data['mod'][] = $censor['find'];
						break;
					default:
						$data['filter']['find'][] = $censor['find'];
						$data['filter']['replace'][] = preg_replace("/\((\d+)\)/", "\\\\1", $censor['replacement']);
						break;
				}
			} else {
				$censor['find'] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($censor['find'], '/'));
				switch($censor['replacement']) {
					case '{BANNED}':
						$banned[] = $censor['find'];
						break;
					case '{MOD}':
						$mod[] = $censor['find'];
						break;
					default:
						$data['filter']['find'][] = '/'.$censor['find'].'/i';
						$data['filter']['replace'][] = $censor['replacement'];
						break;
				}
			}
						
		}
		if($banned) {
			$data['banned'] = '/('.implode('|', $banned).')/i';
		}
		if($mod) {
			$data['mod'] = '/('.implode('|', $mod).')/i';
		}

		return $data;
	}

	//从数据库取过滤规则列表
	static function get_censor_from_db(){  
		$db = cm_db::get_db(cm_db_config::get_ini('comm','slave'));  
		$select = $db->select();
		$select	->from('common_word_filter', '*'); 
		$sql = $select->__toString();    
		$censor_list = $db->fetchAll($sql);
		$censor_list = self::censor_translate($censor_list); 

		if($censor_list) return $censor_list; else return false;
	}

	//删过滤规则列表缓存
	static function clear_cache_censor(){  
			$cache = new temp_cache; 
			$cache->delete(self::$key_base);
			$cache->close();
	}  
 
	
	//构造函数
	function cm_censor() {
		$this->censor_words = array();
		$t=self::get_censor_list();
		if($t) $this->censor_words = $t;
		$this->bbcodes_display = '';
	}

	function & instance() {
		static $instance;
		if(!$instance) {
			$instance = new cm_censor();
		}
		return $instance;
	}

	function check(&$message, $modword = NULL) {
		$bbcodes = 'b|i|color|size|font|align|list|indent|email|hide|quote|code|free|table|tr|td|img|swf|attach|payto|float'.($this->bbcodes_display ? '|'.implode('|', array_keys($this->bbcodes_display)) : '');
		if(!empty($this->censor_words['banned'])) {
			if(preg_match($this->censor_words['banned'], @preg_replace(array("/\[($bbcodes)=?.*\]/iU", "/\[\/($bbcodes)\]/i"), '', $message))) {
				$this->result = cm_CENSOR_BANNED;
				return cm_CENSOR_BANNED;
			}
		}
		if($this->censor_words['mod']) {
			if($modword !== NULL) {
				$message = preg_replace($this->censor_words['mod'], $modword, $message);
			}
			if(preg_match($this->censor_words['mod'], @preg_replace(array("/\[($bbcodes)=?.*\]/iU", "/\[\/($bbcodes)\]/i"), '', $message))) {
				$this->result = cm_CENSOR_MODERATED;
				return cm_CENSOR_MODERATED;
			}
		}
		if(!empty($this->censor_words['filter'])) {
			$message = preg_replace($this->censor_words['filter']['find'], $this->censor_words['filter']['replace'], $message);
			$this->result = cm_CENSOR_REPLACED;
			return cm_CENSOR_REPLACED;
		}
		$this->result = cm_CENSOR_SUCCEED;
		return cm_CENSOR_SUCCEED;
	}

	function modbanned() {
		return $this->result == cm_CENSOR_BANNED;
	}

	function modmoderated() {
		return $this->result == cm_CENSOR_MODERATED;
	}

	function modreplaced() {
		return $this->result == cm_CENSOR_REPLACED;
	}

	function modsucceed() {
		return $this->result == cm_CENSOR_SUCCEED;
	}
}