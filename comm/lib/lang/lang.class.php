<?php
/*
	语言包
	by pkc & zx 2010-11-26
*/
class cm_lang{ 

	static public $lang_path;
	static public $error='';
	static $lang_key='language';
	static $cookie_time=2592000;//30天

	//判断客户端
	static function getClientLanguage(){
		$languageType = $_COOKIE[self::$lang_key];
		if(!isset ($languageType)){
			preg_match('/^([a-z\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
			$lang = $matches[1];
			switch ($lang) {
					case 'zh-cn' :
							$languageType = 'zh_CN';
							break;
					case 'zh-CN' :
							$languageType = 'zh_CN';
							break;
					case 'zh-hk' :
							$languageType = 'zh_HK';
							break;
					case 'zh-HK' :
							$languageType = 'zh_HK';
							break;
					case 'zh-tw' :
							$languageType = 'zh_HK';
							break;
					case 'zh-TW' :
							$languageType = 'zh_HK';
							break;
					case 'en-us' :
							$languageType = 'en';
							break;
					default:
							$languageType = 'zh_CN';
							break;
			}
		}
		self::setClientLanguage($languageType);
		return $languageType;
	}
	
	//设置客户度
	static function setClientLanguage($i)
	{
		if(!$i) return false;
		setcookie(self::$lang_key, $i, time()+self::$cookie_time, "/");
		return true;
	}
	
	//设置目录
	static function set_path($p)
	{
		if(!file_exists($p)){self::$error='!lang';return false;}
		self::$lang_path = $p;
		$l=self::getClientLanguage();
		$lf=$p.'/'.$l.'/'.$l.'.php';
		if(!file_exists($lf)){self::$error='!lang';return false;} 
		require_once($lf);
		return true; 
	}

}

function getLanguage($k)
{
	if(!cm_lang::$lang_path) return '!lang';
	return cm_lang_php::$o[$k]; 
}

?>